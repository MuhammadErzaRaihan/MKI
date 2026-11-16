<?php

namespace App\Console\Commands;

use App\Models\SecurityAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class IngestTsharkCapture extends Command
{
    protected $signature = 'spbe:ingest-tshark {file : Path to .pcap or .pcapng file}';
    protected $description = 'Parse Wireshark/tshark packet capture into security_audit_logs';

    public function handle(): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->error("File capture tidak ditemukan: {$file}");
            return self::FAILURE;
        }

        $startedAt = hrtime(true);

        // Extract compact tab-separated packet fields to avoid loading full JSON trees in memory.
        $result = Process::run([
            'tshark',
            '-r', $file,
            '-Y', 'ip',
            '-T', 'fields',
            '-E', 'separator=|',
            '-e', 'ip.src',
            '-e', 'ip.dst',
            '-e', '_ws.col.Protocol',
            '-e', 'tcp.dstport',
            '-e', 'http.request.uri',
        ]);

        if ($result->failed()) {
            $this->error('Gagal menjalankan tshark: ' . $result->errorOutput());
            return self::FAILURE;
        }

        $lines = array_filter(explode("\n", trim($result->output())));
        $latencyMs = round((hrtime(true) - $startedAt) / 1_000_000, 3);
        $inserted = 0;

        foreach ($lines as $line) {
            [$srcIp, $dstIp, $protocol, $dstPort, $uri] = array_pad(explode('|', $line), 5, '');
            if ($srcIp === '') {
                continue;
            }

            $isSuspiciousPort = in_array($dstPort, ['22', '23', '3306', '3389'], true);
            $hasAttackUri = preg_match('/(\.\.\/|<script|union\+select|\'or\')/i', $uri) === 1;

            $severity = $hasAttackUri ? 'CRITICAL' : ($isSuspiciousPort ? 'MEDIUM' : 'INFO');
            $status = $hasAttackUri ? 'BLOCKED' : ($isSuspiciousPort ? 'SUSPICIOUS' : 'ALLOWED');
            $eventType = $hasAttackUri
                ? 'PCAP_PAYLOAD_ANOMALY'
                : ($isSuspiciousPort ? "SENSITIVE_PORT_{$dstPort}" : 'NETWORK_PACKET');

            SecurityAuditLog::create([
                'source' => 'TSHARK_CAPTURE',
                'nip' => 'NET-SENSOR',
                'ip_address' => $srcIp,
                'destination_ip' => $dstIp ?: null,
                'protocol' => $protocol ?: 'TCP',
                'event_type' => $eventType,
                'severity' => $severity,
                'status' => $status,
                'detection_latency_ms' => $latencyMs,
                'payload_snippet' => $uri !== '' ? "URI: {$uri}" : "Dst Port: {$dstPort}",
                'recommended_action' => $status === 'ALLOWED'
                    ? 'Trafik jaringan normal'
                    : 'Periksa sumber paket pada Wireshark dan batasi akses port/endpoint',
            ]);

            $inserted++;
        }

        $this->info("Berhasil mengimpor {$inserted} paket jaringan dari tshark dalam {$latencyMs} ms.");
        return self::SUCCESS;
    }
}