<?php

namespace Database\Seeders;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'nip' => '6371012903910001',
                'nama' => 'Andi Saputra, ST',
                'email' => 'andi.saputra@kalselprov.go.id',
                'password' => Hash::make('PasswordAsn2026'),
                'instansi' => 'Diskominfo (HRD)',
                'ttl' => 'Banjarmasin, 29 Mar 1991',
                'telp' => '0812-5551-0192',
                'status' => 'ON',
                'ip_detected' => '192.168.10.15',
                'is_office_ip' => true,
                'secret_key' => 'SK-KALSEL-849',
                'otp_code' => '654321',
            ],
            [
                'nip' => '6371021506880003',
                'nama' => 'Budi Santoso, S.Kom',
                'email' => 'budi.santoso@kalselprov.go.id',
                'password' => Hash::make('PasswordAsn2026'),
                'instansi' => 'BPKAD (Keuangan)',
                'ttl' => 'Banjarbaru, 15 Jun 1988',
                'telp' => '0813-9988-2104',
                'status' => 'ON',
                'ip_detected' => '10.10.10.10',
                'is_office_ip' => true,
                'secret_key' => 'SK-KALSEL-998',
                'otp_code' => '654321',
            ],
            [
                'nip' => '6371043009930005',
                'nama' => 'Citra Lestari, M.T',
                'email' => 'citra.lestari@kalselprov.go.id',
                'password' => Hash::make('PasswordAsn2026'),
                'instansi' => 'Dinas Pemasaran',
                'ttl' => 'Martapura, 30 Sep 1993',
                'telp' => '0852-7711-4039',
                'status' => 'OFF',
                'ip_detected' => '202.158.45.99',
                'is_office_ip' => false,
                'secret_key' => 'SK-KALSEL-304',
                'otp_code' => '654321',
            ],
        ];

        foreach ($records as $record) {
            EmployeeCredential::updateOrCreate(
                ['nip' => $record['nip']],
                $record
            );
        }

        if (SecurityAuditLog::count() === 0) {
            SecurityAuditLog::insert([
                [
                    'source' => 'GATEWAY_LOGIN',
                    'nip' => '6371021506880003',
                    'ip_address' => '10.10.10.10',
                    'destination_ip' => '10.10.1.5',
                    'protocol' => 'HTTP/POST',
                    'event_type' => 'OFFICE_IP_LOGIN',
                    'severity' => 'INFO',
                    'status' => 'ALLOWED',
                    'detection_latency_ms' => 1.05,
                    'payload_snippet' => 'Verified BPKAD (Keuangan) IP -> portal-asn.kalselprov.go.id',
                    'recommended_action' => 'Tidak ada tindakan; akses jaringan instansi sah',
                    'created_at' => now()->subHours(4),
                    'updated_at' => now()->subHours(4),
                ],
                [
                    'source' => 'GATEWAY_LOGIN',
                    'nip' => '6371021506880003',
                    'ip_address' => '10.10.99.5',
                    'destination_ip' => '10.10.1.5',
                    'protocol' => 'HTTP/POST',
                    'event_type' => 'SUSPICIOUS_EXTERNAL_IP',
                    'severity' => 'MEDIUM',
                    'status' => 'SUSPICIOUS',
                    'detection_latency_ms' => 2.15,
                    'payload_snippet' => 'IP 10.10.99.5 mismatch for BPKAD (Keuangan); OTP emailed: yes',
                    'recommended_action' => 'Wajibkan input OTP dari email ASN atau Secret Key pegawai',
                    'created_at' => now()->subHours(3),
                    'updated_at' => now()->subHours(3),
                ],
                [
                    'source' => 'GATEWAY_LOGIN',
                    'nip' => "' OR '1'='1' --",
                    'ip_address' => '103.47.132.44',
                    'destination_ip' => '10.10.1.5',
                    'protocol' => 'HTTP/POST',
                    'event_type' => 'SQL_INJECTION',
                    'severity' => 'CRITICAL',
                    'status' => 'BLOCKED',
                    'detection_latency_ms' => 0.62,
                    'payload_snippet' => "' OR '1'='1' --",
                    'recommended_action' => 'Blokir IP sumber pada WAF/Firewall dan audit parameter login',
                    'created_at' => now()->subHours(2),
                    'updated_at' => now()->subHours(2),
                ],
                [
                    'source' => 'WEB_WAF',
                    'nip' => 'HTTP-GUARD',
                    'ip_address' => '114.122.41.19',
                    'destination_ip' => '10.10.1.5',
                    'protocol' => 'HTTP/GET',
                    'event_type' => 'XSS_ATTACK',
                    'severity' => 'HIGH',
                    'status' => 'BLOCKED',
                    'detection_latency_ms' => 0.48,
                    'payload_snippet' => '/?search=<script>alert(document.cookie)</script>',
                    'recommended_action' => 'Sanitasi input dan blokir sesi pengirim',
                    'created_at' => now()->subHour(),
                    'updated_at' => now()->subHour(),
                ],
            ]);
        }
    }
}