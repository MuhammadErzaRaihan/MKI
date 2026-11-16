<?php

namespace Tests\Unit;

use App\Http\Controllers\SpbeGatewayController;
use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class GatewayDetectionWhiteboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_whitebox_payload_inspector_branches(): void
    {
        $controller = new SpbeGatewayController();

        $this->assertSame('SQL_INJECTION', $controller->inspectPayload("admin' UNION SELECT password FROM users--")['type']);
        $this->assertSame('XSS_ATTACK', $controller->inspectPayload('<img src=x onerror=alert(1)>')['type']);
        $this->assertNull($controller->inspectPayload('6371012903910001 PasswordAsn2026'));
    }

    public function test_whitebox_office_cidr_boundary(): void
    {
        $this->assertTrue(EmployeeCredential::isOfficeIp('192.168.10.15'));
        $this->assertTrue(EmployeeCredential::isOfficeIp('10.1.2.3'));
        $this->assertFalse(EmployeeCredential::isOfficeIp('180.252.88.12'));
    }

    public function test_whitebox_tshark_ingestion_triggers_telegram_on_anomaly(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $tempPcap = tempnam(sys_get_temp_dir(), 'spbe_pcap_');
        file_put_contents($tempPcap, 'mock-pcap');

        Process::fake([
            'tshark *' => Process::result(
                output: "180.252.88.12|10.10.1.5|HTTP|80|/gateway/login?nip='or'1'='1\n"
            ),
        ]);

        $this->artisan('spbe:ingest-tshark', ['file' => $tempPcap])
            ->assertExitCode(0);

        @unlink($tempPcap);

        $this->assertDatabaseHas('security_audit_logs', [
            'source' => 'TSHARK_CAPTURE',
            'event_type' => 'PCAP_PAYLOAD_ANOMALY',
            'status' => 'BLOCKED',
        ]);

        Http::assertSent(fn ($request) => str_contains($request['text'] ?? '', 'PCAP_PAYLOAD_ANOMALY'));
    }

    public function test_whitebox_telegram_alert_skips_when_token_missing(): void
    {
        config(['services.telegram.bot_token' => null]);

        $log = new SecurityAuditLog([
            'source' => 'GATEWAY_LOGIN',
            'nip' => '6371012903910001',
            'ip_address' => '180.252.88.12',
            'event_type' => 'SQL_INJECTION',
            'severity' => 'CRITICAL',
            'status' => 'BLOCKED',
            'detection_latency_ms' => 1.2,
            'recommended_action' => 'Block IP',
        ]);

        $this->assertFalse(SecurityAuditLog::sendTelegramAlert($log));
    }
}