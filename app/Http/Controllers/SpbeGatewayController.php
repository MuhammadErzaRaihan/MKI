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

        $clientIp = $request->input('simulated_ip')
            ?: ($request->header('X-Simulated-IP') ?: ($request->ip() ?? '127.0.0.1'));

        // 1. Inspeksi SQL Injection & XSS sebelum menyentuh query autentikasi
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

        // 2. Blokir sementara jika IP sudah gagal login >= 3x dalam 5 menit (Brute Force)
        $recentFailedLogins = SecurityAuditLog::where('ip_address', $clientIp)
            ->where('event_type', 'INVALID_CREDENTIAL')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentFailedLogins >= 3) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: mb_substr(trim($rawNip), 0, 64),
                ip: $clientIp,
                eventType: 'BRUTE_FORCE_LOCKOUT',
                severity: 'HIGH',
                status: 'BLOCKED',
                payload: "Locked out after {$recentFailedLogins} failed attempts",
                action: 'Kunci akses IP selama 5 menit dan laporkan ke SOC'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => 'BRUTE_FORCE_LOCKOUT',
                'latency_ms' => $log->detection_latency_ms,
                'message' => "IP {$clientIp} diblokir sementara (5 menit) akibat percobaan login gagal berulang kali.",
            ], 429);
        }

        // 3. Validasi Kredensial ASN
        $credential = EmployeeCredential::where('nip', trim($rawNip))->first();

        if (! $credential || $credential->status !== 'ON' || ! Hash::check($rawPassword, (string) $credential->password)) {
            $attemptNo = $recentFailedLogins + 1;
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: mb_substr(trim($rawNip), 0, 64),
                ip: $clientIp,
                eventType: 'INVALID_CREDENTIAL',
                severity: 'LOW',
                status: 'BLOCKED',
                payload: "Failed login attempt ({$attemptNo}/3)",
                action: 'Verifikasi ulang NIP/Password; kunci IP jika gagal >= 3x'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => 'INVALID_CREDENTIAL',
                'latency_ms' => $log->detection_latency_ms,
                'message' => "NIP atau kata sandi ASN tidak valid (percobaan {$attemptNo}/3).",
            ], 401);
        }

        // 4. Verifikasi IP Resmi Instansi ASN (misal BPKAD wajib 10.10.10.10)
        if ($credential->isAllowedInstansiIp($clientIp)) {
            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'OFFICE_IP_LOGIN',
                severity: 'INFO',
                status: 'ALLOWED',
                payload: "Verified {$credential->instansi} IP -> {$targetPortal}",
                action: 'Tidak ada tindakan; akses jaringan instansi sah'
            );

            return response()->json([
                'authenticated' => true,
                'status' => 'ALLOWED',
                'event_type' => 'OFFICE_IP_LOGIN',
                'redirect_portal' => $targetPortal,
                'latency_ms' => $log->detection_latency_ms,
                'message' => "IP Instansi ({$credential->instansi}) terverifikasi. Meneruskan {$credential->nama} ke {$targetPortal}.",
            ]);
        }

        // 5. IP tidak sesuai subnet instansi / luar kantor -> Tantangan OTP Email + Alert Telegram
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
                payload: "IP {$clientIp} mismatch for {$credential->instansi}; OTP emailed: " . ($emailDispatched ? 'yes' : 'no'),
                action: 'Wajibkan input OTP dari email ASN atau Secret Key pegawai'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'SUSPICIOUS',
                'event_type' => 'SUSPICIOUS_EXTERNAL_IP',
                'requires_mfa' => true,
                'otp_sent_to' => $credential->email,
                'latency_ms' => $log->detection_latency_ms,
                'message' => $emailDispatched
                    ? "IP ({$clientIp}) tidak sesuai jaringan {$credential->instansi}. Kode OTP dikirim ke {$credential->email} (berlaku 5 menit)."
                    : "IP ({$clientIp}) tidak sesuai jaringan {$credential->instansi}. Gunakan Secret Key pegawai.",
            ], 403);
        }

        // 6. Deteksi Anomali MFA (Brute force token OTP >= 3x, kedaluwarsa, atau replay)
        $failedMfaCount = SecurityAuditLog::where('nip', $credential->nip)
            ->where('event_type', 'INVALID_MFA_TOKEN')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($failedMfaCount >= 3) {
            $credential->update(['otp_expires_at' => null]);

            $log = $this->recordLog(
                startedAt: $startedAt,
                nip: $credential->nip,
                ip: $clientIp,
                eventType: 'MFA_BRUTE_FORCE',
                severity: 'CRITICAL',
                status: 'BLOCKED',
                payload: 'OTP invalidated after >= 3 invalid MFA attempts',
                action: 'Cabut OTP aktif dan kunci sesi verifikasi MFA'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => 'MFA_BRUTE_FORCE',
                'latency_ms' => $log->detection_latency_ms,
                'message' => 'Percobaan OTP salah berulang kali. Sesi OTP dicabut otomatis.',
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
                payload: 'Invalid, expired, or replayed OTP/Secret Key',
                action: 'Tolak akses luar instansi dan audit aktivitas NIP terkait'
            );

            return response()->json([
                'authenticated' => false,
                'status' => 'BLOCKED',
                'event_type' => 'INVALID_MFA_TOKEN',
                'requires_mfa' => true,
                'latency_ms' => $log->detection_latency_ms,
                'message' => 'Kode OTP salah/kedaluwarsa/sudah terpakai atau Secret Key tidak cocok.',
            ], 403);
        }

        if ($isOtpValid) {
            // Hanguskan OTP setelah dipakai agar tidak bisa di-replay
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
            action: 'Sesi luar jaringan instansi sah melalui verifikasi MFA'
        );

        return response()->json([
            'authenticated' => true,
            'status' => 'ALLOWED',
            'event_type' => 'EXTERNAL_MFA_VERIFIED',
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