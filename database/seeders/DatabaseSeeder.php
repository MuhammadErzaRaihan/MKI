<?php

namespace Database\Seeders;

use App\Models\EmployeeCredential;
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
                'password' => Hash::make('PasswordAsn2026'),
                'instansi' => 'BPKAD (Keuangan)',
                'ttl' => 'Banjarbaru, 15 Jun 1988',
                'telp' => '0813-9988-2104',
                'status' => 'ON',
                'ip_detected' => '180.252.88.12',
                'is_office_ip' => false,
                'secret_key' => 'SK-KALSEL-998',
                'otp_code' => '654321',
            ],
            [
                'nip' => '6371043009930005',
                'nama' => 'Citra Lestari, M.T',
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
    }
}