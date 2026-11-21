<?php

namespace Tests\Feature;

use App\Models\EmployeeCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SpbeGatewayLoginTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeCredential $asn;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $this->asn = EmployeeCredential::create([
            'nip' => '6371012903910001',
            'nama' => 'Andi Saputra, ST',
            'email' => 'andi.saputra@kalselprov.go.id',
            'password' => Hash::make('PasswordAsn2026'),
            'instansi' => 'Diskominfo Kalsel',
            'secret_key' => 'SK-KALSEL-849',
            'otp_code' => '654321',
            'status' => 'ON',
        ]);
    }

    public function test_external_ip_sends_otp_email_and_notifies_telegram_admin(): void
    {
        Mail::fake();

        $response = $this->postJson('/gateway/login', [
            'nip' => '6371012903910001',
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '180.252.88.12',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'SUSPICIOUS')
            ->assertJsonPath('requires_mfa', true)
            ->assertJsonPath('otp_sent_to', 'andi.saputra@kalselprov.go.id');

        $this->asn->refresh();
        $this->assertNotNull($this->asn->otp_expires_at);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org/bot'));
    }

    public function test_external_ip_login_succeeds_with_emailed_otp_and_prevents_replay(): void
    {
        Mail::fake();

        $this->postJson('/gateway/login', [
            'nip' => '6371012903910001',
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '180.252.88.12',
        ])->assertStatus(403);

        $issuedOtp = $this->asn->fresh()->otp_code;

        $this->postJson('/gateway/login', [
            'nip' => '6371012903910001',
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '180.252.88.12',
            'verification_token' => $issuedOtp,
        ])->assertStatus(200)
          ->assertJsonPath('status', 'ALLOWED');

        // Reusing the same OTP must fail once consumed.
        $this->postJson('/gateway/login', [
            'nip' => '6371012903910001',
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '180.252.88.12',
            'verification_token' => $issuedOtp,
        ])->assertStatus(403)
          ->assertJsonPath('status', 'BLOCKED');
    }

    public function test_sqli_attack_is_blocked_and_triggers_telegram_alert(): void
    {
        $this->postJson('/gateway/login', [
            'nip' => "' OR '1'='1' --",
            'password' => 'x',
            'simulated_ip' => '180.252.88.12',
        ])->assertStatus(403)
          ->assertJsonPath('event_type', 'SQL_INJECTION');

        Http::assertSent(fn ($request) => str_contains($request['text'] ?? '', 'SQL_INJECTION'));
    }

    public function test_gateway_login_view_and_datalogs_api_render_properly(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Login SPBE');

        $this->postJson('/gateway/login', [
            'nip' => "' OR '1'='1' --",
            'password' => 'x',
            'simulated_ip' => '180.252.88.12',
        ])->assertStatus(403);

        $this->getJson('/api/datalogs?source=GATEWAY_LOGIN')
            ->assertStatus(200)
            ->assertJsonFragment(['event_type' => 'SQL_INJECTION']);
    }

    public function test_instansi_ip_must_match_assigned_subnet_and_locks_out_after_three_failed_logins(): void
    {
        Mail::fake();

        $bpkad = EmployeeCredential::create([
            'nip' => '6371021506880003',
            'nama' => 'Budi Santoso, S.Kom',
            'email' => 'budi.santoso@kalselprov.go.id',
            'password' => Hash::make('PasswordAsn2026'),
            'instansi' => 'BPKAD (Keuangan)',
            'secret_key' => 'SK-KALSEL-998',
            'otp_code' => '654321',
            'status' => 'ON',
        ]);

        // IP sesuai instansi BPKAD (10.10.10.10) -> langsung ALLOWED
        $this->postJson('/gateway/login', [
            'nip' => $bpkad->nip,
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '10.10.10.10',
        ])->assertStatus(200)->assertJsonPath('status', 'ALLOWED');

        // IP berbeda (10.10.99.5) -> SUSPICIOUS & kirim OTP
        $this->postJson('/gateway/login', [
            'nip' => $bpkad->nip,
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '10.10.99.5',
        ])->assertStatus(403)->assertJsonPath('status', 'SUSPICIOUS');

        // Brute force 3x salah password -> percobaan ke-4 terkena BRUTE_FORCE_LOCKOUT (429)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/gateway/login', [
                'nip' => $bpkad->nip,
                'password' => 'SalahTerus',
                'simulated_ip' => '203.0.113.77',
            ])->assertStatus(401);
        }

        $this->postJson('/gateway/login', [
            'nip' => $bpkad->nip,
            'password' => 'PasswordAsn2026',
            'simulated_ip' => '203.0.113.77',
        ])->assertStatus(429)->assertJsonPath('event_type', 'BRUTE_FORCE_LOCKOUT');
    }

    public function test_internal_waf_middleware_blocks_xss_on_general_web_routes(): void
    {
        $this->get('/?search=' . urlencode('<script>alert(1)</script>'))
            ->assertStatus(403);

        $this->assertDatabaseHas('security_audit_logs', [
            'source' => 'WEB_WAF',
            'event_type' => 'XSS_ATTACK',
            'status' => 'BLOCKED',
        ]);
    }
}