<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SpbeGatewayController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $startedAt = hrtime(true);

        $rawNip = (string) $request->input('nip', '');
        $rawPassword = (string) $request->input('password', '');
        $verificationToken = trim((string) $request->input('verification_token', ''));
        $targetPortal = (string) $request->input('target_portal', 'portal-asn.kalselprov.go.id');

        // Allow simulated_ip header/input so controlled UTS lab tests can emulate external IPs locally.
        $clientIp = $request->input('simulated_ip')
            ?: ($request->header('X-Simulated-IP') ?: ($request->ip() ?? '127.0.0.1'));

        $threat = $this->inspectPayload($rawNip . ' ' . $rawPassword);
        if ($threat !== null) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: mb_substr($rawNip, 0, 64),
                ip: $clientIp,
                eventType: $threat['type'],
                severity: $threat['severity'],
                status: 'BLOCKED',
                payload: mb_substr($rawNip, 0, 120),
                action: $threat['action']
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => $threat['type'],
                'latency_ms' => $log->detection_latency_ms,
                'message' => "Serangan {$threat['type']} terdeteksi dan diblokir oleh Gerbang SPBE.",
            ], 403);
        }

        $credential = EmployeeCredential::where('nip', trim($rawNip))->first();

        if (! $credential || $credential->status !== 'ON' || ! Hash::check($rawPassword, $credential->password)) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: mb_substr(trim($rawNip), 0, 64),
                ip: $clientIp,
                eventType: 'INVALID_CREDENTIAL',
                severity: 'LOW',
                status: 'BLOCKED',
                payload: 'Failed login attempt',
                action: 'Verifikasi ulang NIP/Password; blokir IP jika gagal > 5x'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => 'INVALID_CREDENTIAL',
                'latency_ms' => $log->detection_latency_ms,
                'message' => 'NIP atau kata sandi ASN tidak valid.',
            ], 401);
        }

        if (EmployeeCredential::isOfficeIp($clientIp)) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'OFFICE_IP_LOGIN',
                severity: 'INFO',
                status: 'ALLOWED',
                payload: "Access granted to {$targetPortal}",
                action: 'Tidak ada tindakan; akses jaringan internal sah'
            );

            return response()->json([
                'authenticated' => true,
                'status' => 'ALLOWED',
                'redirect_portal' => $targetPortal,
                'latency_ms' => $log->detection_latency_ms,
                'message' => "IP Kantor terverifikasi. Meneruskan {$credential->nama} ke {$targetPortal}.",
            ]);
        }

        // External IPs are flagged suspicious and require either OTP or employee Secret Key.
        $tokenValid = $verificationToken !== '' && (
            hash_equals($credential->secret_key, $verificationToken) ||
            hash_equals($credential->otp_code, $verificationToken)
        );

        if (! $tokenValid) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'SUSPICIOUS_EXTERNAL_IP',
                severity: 'MEDIUM',
                status: 'SUSPICIOUS',
                payload: 'Login outside office CIDR without valid OTP/Secret Key',
                action: 'Wajibkan input OTP atau Secret Key pegawai sebelum menerbitkan sesi'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'SUSPICIOUS',
                'requires_mfa' => true,
                'latency_ms' => $log->detection_latency_ms,
                'message' => "IP ({$clientIp}) berada di luar jaringan instansi. Masukkan OTP atau Secret Key.",
            ], 403);
        }

        $log = $this->recordLog(
            startedAt: $startedAt,
            nip: $credential->nip,
            ip: $clientIp,
            eventType: 'EXTERNAL_MFA_VERIFIED',
            severity: 'INFO',
            status: 'ALLOWED',
            payload: "MFA verified for {$targetPortal}",
            action: 'Sesi luar kantor sah melalui verifikasi Secret Key/OTP'
        );

        return response()->json([
            'authenticated' => true,
            'status' => 'ALLOWED',
            'redirect_portal' => $targetPortal,
            'latency_ms' => $log->detection_latency_ms,
            'message' => "Verifikasi OTP/Secret Key berhasil. Meneruskan ke {$targetPortal}.",
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $source = $request->query('source');

        $logs = SecurityAuditLog::query()
            ->when($source, fn ($q) => $q->where('source', $source))
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['data' => $logs]);
    }

    public function inspectPayload(string $input): ?array
    {
        $sqliPattern = '/(\b(union\s+select|select\s+.*\s+from|insert\s+into|drop\s+table|or\s+1\s*=\s*1)\b|--|;\s*drop|\'\s*or\s*\')/i';
        if (preg_match($sqliPattern, $input)) {
            return [
                'type' => 'SQL_INJECTION',
                'severity' => 'CRITICAL',
                'action' => 'Blokir IP sumber pada WAF/Firewall dan audit parameter login',
            ];
        }

        $xssPattern = '/(<\s*script\b|javascript\s*:|on(error|load|click|mouseover)\s*=|<\s*img\b[^>]*onerror)/i';
        if (preg_match($xssPattern, $input)) {
            return [
                'type' => 'XSS_ATTACK',
                'severity' => 'HIGH',
                'action' => 'Sanitasi input dan blokir sesi pengirim',
            ];
        }

        return null;
    }

    private function recordLog(
        int $startedAt,
        ?string $nip,
        string $ip,
        string $eventType,
        string $severity,
        string $status,
        string $payload,
        string $action
    ): SecurityAuditLog {
        $latencyMs = round((hrtime(true) - $startedAt) / 1_000_000, 3);

        return SecurityAuditLog::create([
            'source' => 'GATEWAY_LOGIN',
            'nip' => $nip ?: 'UNKNOWN',
            'ip_address' => $ip,
            'protocol' => 'HTTP/POST',
            'event_type' => $eventType,
            'severity' => $severity,
            'status' => $status,
            'detection_latency_ms' => $latencyMs,
            'payload_snippet' => $payload,
            'recommended_action' => $action,
        ]);
    }
}