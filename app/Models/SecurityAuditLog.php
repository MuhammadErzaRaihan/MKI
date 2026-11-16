<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityAuditLog extends Model
{
    protected $fillable = [
        'source',
        'nip',
        'ip_address',
        'destination_ip',
        'protocol',
        'event_type',
        'severity',
        'status',
        'detection_latency_ms',
        'payload_snippet',
        'recommended_action',
    ];

    protected static function booted(): void
    {
        static::created(function (SecurityAuditLog $log) {
            if ($log->shouldNotifyTelegram()) {
                self::sendTelegramAlert($log);
            }
        });
    }

    public function shouldNotifyTelegram(): bool
    {
        if (! in_array($this->status, ['BLOCKED', 'SUSPICIOUS'], true)) {
            return false;
        }

        // Alert on invalid credentials only when repeated from the same IP to prevent noise.
        if ($this->event_type === 'INVALID_CREDENTIAL') {
            return self::where('ip_address', $this->ip_address)
                ->where('event_type', 'INVALID_CREDENTIAL')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count() >= 3;
        }

        return true;
    }

    public static function sendTelegramAlert(SecurityAuditLog $log): bool
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId)) {
            return false;
        }

        $text = implode("\n", [
            "[SPBE ALERT - {$log->severity}] {$log->event_type}",
            "Status: {$log->status} | Sumber: {$log->source}",
            "NIP/Sensor: {$log->nip}",
            "IP Asal: {$log->ip_address}",
            "Latency Deteksi: {$log->detection_latency_ms} ms",
            "Detail: " . ($log->payload_snippet ?: '-'),
            "Tindakan: {$log->recommended_action}",
        ]);

        try {
            // Keep timeout short so slow Telegram API never stalls gateway latency.
            $response = Http::timeout(3)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram alert failed: ' . $e->getMessage());
            return false;
        }
    }
}