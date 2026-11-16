<?php

namespace Tests\Feature;

use App\Models\EmployeeCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SpbeGatewayLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        EmployeeCredential::create([
            'nip' => '6371012903910001',
            'nama' => 'Andi Saputra, ST',
            'password' => Hash::make('PasswordAsn2026'),
            'instansi' => 'Diskominfo Kalsel',
            'secret_key' => 'SK-KALSEL-849',
            'otp_code' => '654321',
            'status' => 'ON',
        ]);
    }

    #[DataProvider('loginScenarioProvider')]
    public function test_spbe_gateway_login_scenarios(
        array $payload,
        int $expectedHttpStatus,
        string $expectedSecurityStatus,
        string $expectedEventType
    ): void {
        $response = $this->postJson('/gateway/login', $payload);

        $response->assertStatus($expectedHttpStatus)
            ->assertJsonPath('status', $expectedSecurityStatus);

        $this->assertDatabaseHas('security_audit_logs', [
            'source' => 'GATEWAY_LOGIN',
            'status' => $expectedSecurityStatus,
            'event_type' => $expectedEventType,
        ]);
    }

    public static function loginScenarioProvider(): array
    {
        return [
            'TC-01 Valid office IP passes without MFA' => [
                [
                    'nip' => '6371012903910001',
                    'password' => 'PasswordAsn2026',
                    'simulated_ip' => '192.168.10.15',
                ],
                200,
                'ALLOWED',
                'OFFICE_IP_LOGIN',
            ],
            'TC-02 External IP without OTP/Secret Key flagged suspicious' => [
                [
                    'nip' => '6371012903910001',
                    'password' => 'PasswordAsn2026',
                    'simulated_ip' => '180.252.88.12',
                ],
                403,
                'SUSPICIOUS',
                'SUSPICIOUS_EXTERNAL_IP',
            ],
            'TC-03 External IP with valid Secret Key passes' => [
                [
                    'nip' => '6371012903910001',
                    'password' => 'PasswordAsn2026',
                    'simulated_ip' => '180.252.88.12',
                    'verification_token' => 'SK-KALSEL-849',
                ],
                200,
                'ALLOWED',
                'EXTERNAL_MFA_VERIFIED',
            ],
            'TC-04 SQL Injection payload blocked immediately' => [
                [
                    'nip' => "' OR '1'='1' --",
                    'password' => 'anything',
                    'simulated_ip' => '180.252.88.12',
                ],
                403,
                'BLOCKED',
                'SQL_INJECTION',
            ],
            'TC-05 XSS script payload blocked immediately' => [
                [
                    'nip' => '<script>alert(document.cookie)</script>',
                    'password' => 'anything',
                    'simulated_ip' => '192.168.10.20',
                ],
                403,
                'BLOCKED',
                'XSS_ATTACK',
            ],
            'TC-06 Invalid credential rejected' => [
                [
                    'nip' => '6371012903910001',
                    'password' => 'WrongPassword',
                    'simulated_ip' => '192.168.10.15',
                ],
                401,
                'BLOCKED',
                'INVALID_CREDENTIAL',
            ],
        ];
    }
}