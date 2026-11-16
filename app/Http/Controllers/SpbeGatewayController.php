<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        if (! $credential || $credential->status !== 'ON' || ! Hash::check($rawPassword, (string) $credential->password)) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: mb_substr(trim($rawNip), 0, 64),
                ip: $clientIp,
                eventType: 'INVALID_CREDENTIAL',
                severity: 'LOW',
                status: 'BLOCKED',
                payload: 'Failed login attempt',
                action: 'Verifikasi ulang NIP/Password; blokir IP jika gagal >= 3x'
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

        // Issue and email a 5-minute OTP when an external IP authenticates without a token yet.
        if ($verificationToken === '') {
            $otp = (string) random_int(100000, 999999);
            $credential->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            $emailDispatched = $this->dispatchOtpEmail($credential, $otp, $clientIp);

            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'SUSPICIOUS_EXTERNAL_IP',
                severity: 'MEDIUM',
                status: 'SUSPICIOUS',
                payload: "External IP challenge; OTP emailed: " . ($emailDispatched ? 'yes' : 'no'),
                action: 'Wajibkan input OTP dari email ASN atau Secret Key pegawai'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'SUSPICIOUS',
                'requires_mfa' => true,
                'otp_sent_to' => $credential->email,
                'latency_ms' => $log->detection_latency_ms,
                'message' => $emailDispatched
                    ? "IP ({$clientIp}) di luar kantor. Kode OTP telah dikirim ke {$credential->email} (berlaku 5 menit)."
                    : "IP ({$clientIp}) di luar kantor. Email OTP gagal dikirim; gunakan Secret Key pegawai.",
            ], 403);
        }

        $isSecretValid = hash_equals($credential->secret_key, $verificationToken);
        $isOtpValid = $credential->otp_expires_at !== null
            && now()->lte($credential->otp_expires_at)
            && hash_equals($credential->otp_code, $verificationToken);

        if (! $isSecretValid && ! $isOtpValid) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'INVALID_MFA_TOKEN',
                severity: 'HIGH',
                status: 'BLOCKED',
                payload: 'Invalid or expired OTP/Secret Key from external IP',
                action: 'Tolak akses luar kantor dan audit aktivitas NIP terkait'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'requires_mfa' => true,
                'latency_ms' => $log->detection_latency_ms,
                'message' => 'Kode OTP kedaluwarsa/salah atau Secret Key tidak cocok.',
            ], 403);
        }

        if ($isOtpValid) {
            // Prevent OTP replay after successful verification.
            $credential->update(['otp_expires_at' => null]);
        }

        $log = $this->recordLog(
            startedAt: $startedAt,
            nip: $credential->nip,
            ip: $clientIp,
            eventType: 'EXTERNAL_MFA_VERIFIED',
            severity: 'INFO',
            status: 'ALLOWED',
            payload: "MFA verified (" . ($isOtpValid ? 'EMAIL_OTP' : 'SECRET_KEY') . ") for {$targetPortal}",
            action: 'Sesi luar kantor sah melalui verifikasi MFA'
        );

        return response()->json([
            'authenticated' => true,
            'status' => 'ALLOWED',
            'redirect_portal' => $targetPortal,
            'latency_ms' => $log->detection_latency_ms,
            'message' => "Verifikasi MFA berhasil. Meneruskan ke {$targetPortal}.",
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

    public function sendTelegramMonitoringSummary(): JsonResponse
    {
        $totalBlocked = SecurityAuditLog::where('status', 'BLOCKED')->count();
        $totalSuspicious = SecurityAuditLog::where('status', 'SUSPICIOUS')->count();
        $avgLatency = round((float) SecurityAuditLog::avg('detection_latency_ms'), 3);

        $summaryLog = new SecurityAuditLog([
            'source' => 'MONITORING_REPORT',
            'nip' => 'ADMIN-SOC',
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'event_type' => 'SOC_STATUS_SUMMARY',
            'severity' => 'INFO',
            'status' => 'SUSPICIOUS',
            'detection_latency_ms' => $avgLatency,
            'payload_snippet' => "Blocked: {$totalBlocked} | Suspicious: {$totalSuspicious}",
            'recommended_action' => 'Monitoring rutin Gerbang SPBE Diskominfo Kalsel',
        ]);

        $sent = SecurityAuditLog::sendTelegramAlert($summaryLog);

        return response()->json([
            'sent' => $sent,
            'message' => $sent
                ? 'Ringkasan monitoring berhasil dikirim ke Telegram Admin.'
                : 'Gagal mengirim ke Telegram. Periksa TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID di .env.',
        ], $sent ? 200 : 422);
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

    private function dispatchOtpEmail(EmployeeCredential $credential, string $otp, string $ip): bool
    {
        if (empty($credential->email)) {
            return false;
        }

        try {
            $body = implode("\n", [
                "Yth. {$credential->nama} ({$credential->nip}),",
                "",
                "Terdeteksi upaya login Gerbang SPBE Diskominfo Kalsel dari IP luar kantor ({$ip}).",
                "Kode OTP verifikasi Anda: {$otp}",
                "Kode ini berlaku selama 5 menit. Jangan bagikan kepada siapa pun.",
            ]);

            Mail::raw($body, function ($message) use ($credential) {
                $message->to($credential->email)
                    ->subject('Kode OTP Verifikasi Gerbang SPBE Diskominfo Kalsel');
            });

            return true;
        } catch (\Throwable) {
            return false;
        }
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
    
    public function showLogin()
    {
        return view('gateway-login');
    }
}