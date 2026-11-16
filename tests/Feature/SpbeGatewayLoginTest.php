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
            ->assertSee('Gerbang Autentikasi SPBE');

        $this->postJson('/gateway/login', [
            'nip' => "' OR '1'='1' --",
            'password' => 'x',
            'simulated_ip' => '180.252.88.12',
        ])->assertStatus(403);

        $this->getJson('/api/datalogs?source=GATEWAY_LOGIN')
            ->assertStatus(200)
            ->assertJsonFragment(['event_type' => 'SQL_INJECTION']);
    }
}