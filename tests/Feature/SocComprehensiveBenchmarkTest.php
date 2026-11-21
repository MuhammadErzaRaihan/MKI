<?php

namespace Tests\Feature;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SocComprehensiveBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    private static string $reportPath = '';
    private static array $sections = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$sections = [];
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (self::$reportPath === '') {
            self::$reportPath = storage_path('logs/report-soc-evaluation.html');
        }
    }

    public function test_1_three_iterations_soc_kpi_evaluation(): void
    {
        $scenariosPerIteration = [
            1 => [
                'label' => 'Iterasi 1 (Baseline Signatures)',
                'telegram_status' => 200,
                'normal' => [
                    ['type' => 'login', 'ip' => '10.10.10.10', 'nip' => '6371021506880003', 'pass' => 'PasswordAsn2026', 'desc' => 'Login ASN BPKAD IP resmi'],
                    ['type' => 'web', 'query' => 'monitoring jaringan kalsel', 'desc' => 'Pencarian dashboard normal'],
                ],
                'threats' => [
                    ['type' => 'login', 'ip' => '180.252.88.12', 'nip' => "' OR '1'='1' --", 'pass' => 'x', 'expect_log' => 'SQL_INJECTION', 'desc' => 'SQLi Tautology klasik'],
                    ['type' => 'login', 'ip' => '114.122.41.19', 'nip' => '<script>alert(1)</script>', 'pass' => 'x', 'expect_log' => 'XSS_ATTACK', 'desc' => 'XSS Script tag klasik'],
                    ['type' => 'login', 'ip' => '10.10.99.5', 'nip' => '6371021506880003', 'pass' => 'PasswordAsn2026', 'expect_log' => 'SUSPICIOUS_EXTERNAL_IP', 'desc' => 'IP luar subnet instansi'],
                    ['type' => 'bruteforce', 'ip' => '203.0.113.77', 'nip' => '6371021506880003', 'expect_log' => 'BRUTE_FORCE_LOCKOUT', 'desc' => 'Brute force login 4x'],
                    ['type' => 'web', 'query' => '<script>alert(document.cookie)</script>', 'expect_log' => 'XSS_ATTACK', 'desc' => 'WAF URL XSS'],
                ],
            ],
            2 => [
                'label' => 'Iterasi 2 (Obfuscated & Evasion Payloads)',
                'telegram_status' => 200,
                'normal' => [
                    ['type' => 'login', 'ip' => '10.10.10.25', 'nip' => '6371021506880003', 'pass' => 'PasswordAsn2026', 'desc' => 'Login ASN beda host dalam CIDR /24'],
                    ['type' => 'web', 'query' => 'select jadwal rapat koordinasi spbe', 'desc' => 'Benign keyword "select" dalam kalimat'],
                    ['type' => 'web', 'query' => 'update status layanan publik', 'desc' => 'Benign keyword "update" dalam kalimat'],
                ],
                'threats' => [
                    ['type' => 'login', 'ip' => '180.252.88.20', 'nip' => "1'/**/UnIoN/**/SeLeCt/**/1,2--", 'pass' => 'x', 'expect_log' => 'SQL_INJECTION', 'desc' => 'SQLi Mixed-case + Inline Comment'],
                    ['type' => 'login', 'ip' => '114.122.41.25', 'nip' => '<img src=x onerror=alert(1)>', 'pass' => 'x', 'expect_log' => 'XSS_ATTACK', 'desc' => 'XSS Event Handler onerror'],
                    ['type' => 'web', 'query' => '../../.env', 'expect_log' => null, 'desc' => 'Path Traversal ke file .env'],
                    ['type' => 'web', 'query' => '1 UNION ALL SELECT username,password FROM users', 'expect_log' => 'SQL_INJECTION', 'desc' => 'WAF URL SQLi UNION SELECT'],
                    ['type' => 'login', 'ip' => '10.10.99.9', 'nip' => '6371021506880003', 'pass' => 'PasswordAsn2026', 'expect_log' => 'SUSPICIOUS_EXTERNAL_IP', 'desc' => 'IP anomali luar kantor'],
                ],
            ],
            3 => [
                'label' => 'Iterasi 3 (Chaos: Telegram API Down + Mixed)',
                'telegram_status' => 500,
                'normal' => [
                    ['type' => 'login', 'ip' => '10.10.10.10', 'nip' => '6371021506880003', 'pass' => 'PasswordAsn2026', 'desc' => 'Login ASN valid saat Telegram gangguan'],
                    ['type' => 'web', 'query' => 'laporan audit keamanan informasi 2026', 'desc' => 'Pencarian log normal'],
                ],
                'threats' => [
                    ['type' => 'login', 'ip' => '180.252.88.33', 'nip' => "admin' OR 1=1#", 'pass' => 'x', 'expect_log' => 'SQL_INJECTION', 'desc' => 'SQLi Hash Comment saat Telegram 500'],
                    ['type' => 'login', 'ip' => '114.122.41.33', 'nip' => '<svg/onload=alert(1)>', 'pass' => 'x', 'expect_log' => 'XSS_ATTACK', 'desc' => 'XSS SVG Onload saat Telegram 500'],
                    ['type' => 'bruteforce', 'ip' => '203.0.113.88', 'nip' => '6371021506880003', 'expect_log' => 'BRUTE_FORCE_LOCKOUT', 'desc' => 'Brute force saat Telegram 500'],
                    ['type' => 'web', 'query' => '<iframe src="javascript:alert(1)">', 'expect_log' => 'XSS_ATTACK', 'desc' => 'WAF Iframe JS Injection'],
                ],
            ],
        ];

        $results = [];
        $findings = [];

        foreach ($scenariosPerIteration as $iter => $suite) {
            Cache::flush();
            Mail::fake();
            Http::fake([
                'https://api.telegram.org/*' => Http::response(['ok' => $suite['telegram_status'] === 200], $suite['telegram_status']),
            ]);

            $this->seed();

            $tp = $fn = $fp = $tn = $detectedThreats = $mitigatedThreats = 0;

            foreach ($suite['normal'] as $item) {
                if ($item['type'] === 'login') {
                    $res = $this->postJson('/gateway/login', [
                        'target_portal' => 'portal-asn.kalselprov.go.id',
                        'simulated_ip' => $item['ip'],
                        'nip' => $item['nip'],
                        'password' => $item['pass'],
                    ]);
                    $ok = $res->status() === 200 && ($res->json('status') ?? '') === 'ALLOWED';
                } else {
                    $res = $this->get('/?search=' . urlencode($item['query']));
                    $ok = $res->status() === 200;
                }

                if ($ok) {
                    $tn++;
                } else {
                    $fp++;
                    $findings[] = "[Iterasi {$iter} • FALSE POSITIVE] {$item['desc']} (HTTP {$res->status()})";
                }
            }

            foreach ($suite['threats'] as $threat) {
                $detected = false;
                if ($threat['type'] === 'login') {
                    $res = $this->postJson('/gateway/login', [
                        'target_portal' => 'portal-asn.kalselprov.go.id',
                        'simulated_ip' => $threat['ip'],
                        'nip' => $threat['nip'],
                        'password' => $threat['pass'],
                    ]);
                    $detected = in_array($res->status(), [403, 429], true) || ($res->json('status') ?? '') === 'SUSPICIOUS';
                } elseif ($threat['type'] === 'bruteforce') {
                    for ($attempt = 1; $attempt <= 4; $attempt++) {
                        $res = $this->postJson('/gateway/login', [
                            'target_portal' => 'portal-asn.kalselprov.go.id',
                            'simulated_ip' => $threat['ip'],
                            'nip' => $threat['nip'],
                            'password' => 'SalahPassword123',
                        ]);
                        if ($res->status() === 429 || ($res->json('event_type') ?? '') === 'BRUTE_FORCE_LOCKOUT') {
                            $detected = true;
                        }
                    }
                } elseif ($threat['type'] === 'web') {
                    $res = $this->get('/?search=' . urlencode($threat['query']));
                    $detected = $res->status() === 403;
                }

                if ($detected) {
                    $tp++;
                    $detectedThreats++;
                    $logged = $threat['expect_log']
                        ? SecurityAuditLog::where('event_type', $threat['expect_log'])->exists()
                        : SecurityAuditLog::where('status', 'BLOCKED')->exists();
                    if ($logged) {
                        $mitigatedThreats++;
                    } else {
                        $findings[] = "[Iterasi {$iter} • MITIGATION INCOMPLETE] {$threat['desc']}";
                    }
                } else {
                    $fn++;
                    $findings[] = "[Iterasi {$iter} • FALSE NEGATIVE] {$threat['desc']} lolos tanpa terdeteksi";
                }
            }

            $results[] = [
                'label' => $suite['label'],
                'tp' => $tp, 'fn' => $fn, 'fp' => $fp, 'tn' => $tn,
                'detected' => $detectedThreats,
                'mitigated' => $mitigatedThreats,
                'dr' => ($tp + $fn) > 0 ? round(($tp / ($tp + $fn)) * 100, 2) : 0.0,
                'fpr' => ($fp + $tn) > 0 ? round(($fp / ($fp + $tn)) * 100, 2) : 0.0,
                'msr' => $detectedThreats > 0 ? round(($mitigatedThreats / $detectedThreats) * 100, 2) : 0.0,
            ];

            SecurityAuditLog::query()->delete();
            EmployeeCredential::query()->delete();
            User::query()->delete();
        }

        $avgDr = round(array_sum(array_column($results, 'dr')) / count($results), 2);
        $avgFpr = round(array_sum(array_column($results, 'fpr')) / count($results), 2);
        $avgMsr = round(array_sum(array_column($results, 'msr')) / count($results), 2);

        self::$sections['kpi'] = [
            'rows' => $results,
            'avg_dr' => $avgDr,
            'avg_fpr' => $avgFpr,
            'avg_msr' => $avgMsr,
            'findings' => $findings,
        ];

        $this->assertGreaterThanOrEqual(80.0, $avgDr);
        $this->assertLessThanOrEqual(20.0, $avgFpr);
        $this->assertGreaterThanOrEqual(90.0, $avgMsr);
    }

    public function test_2_credential_crud_stress_and_integrity(): void
    {
        Mail::fake();
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->seed();

        $sample = EmployeeCredential::firstOrFail()->getAttributes();
        $initialCount = EmployeeCredential::count();

        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($r) => str_contains($r->getActionName(), 'CredentialController'));

        $indexUri = '/' . ltrim($routes->first(fn ($r) => str_ends_with($r->getActionName(), '@index'))?->uri() ?? 'api/credentials', '/');
        $storeUri = '/' . ltrim($routes->first(fn ($r) => str_ends_with($r->getActionName(), '@store'))?->uri() ?? 'api/credentials', '/');
        $updateRoute = $routes->first(fn ($r) => str_ends_with($r->getActionName(), '@update'));
        $destroyRoute = $routes->first(fn ($r) => str_ends_with($r->getActionName(), '@destroy'));

        $buildPayload = function (int $idx, string $status = 'ON') use ($sample): array {
            $payload = ['password' => 'PasswordAsn2026'];
            foreach ($sample as $col => $val) {
                if (in_array($col, ['id', 'created_at', 'updated_at', 'otp_code', 'otp_expires_at'], true)) {
                    continue;
                }
                $payload[$col] = match ($col) {
                    'nip' => '637199' . str_pad((string) $idx, 12, '0', STR_PAD_LEFT),
                    'nama', 'name' => "ASN Uji Stress {$idx}",
                    'email' => "asn.stress{$idx}@kalselprov.go.id",
                    'allowed_ip_cidr', 'ip_cidr', 'ip_address' => '10.10.20.0/24',
                    'secret_key' => "SK-STRESS-{$idx}",
                    'status' => $status,
                    default => is_string($val) ? $val : 'Diskominfo Kalsel',
                };
            }
            return $payload;
        };

        $batchSize = 50;
        $metrics = [];

        // 1. Batch Create
        $t0 = hrtime(true);
        $createOk = 0;
        for ($i = 1; $i <= $batchSize; $i++) {
            $res = $this->postJson($storeUri, $buildPayload($i, 'ON'));
            if (in_array($res->status(), [200, 201], true)) {
                $createOk++;
            }
        }
        $createdIds = EmployeeCredential::where('nip', 'like', '637199%')->pluck('id')->all();
        $metrics[] = ['phase' => '1. Batch Create (50x POST)', 'ops' => $batchSize, 'ok' => $createOk, 'ms' => (hrtime(true) - $t0) / 1_000_000];

        // 2. High-Freq Read
        $t0 = hrtime(true);
        $readOk = 0;
        for ($i = 1; $i <= $batchSize; $i++) {
            if ($this->getJson($indexUri)->status() === 200) {
                $readOk++;
            }
        }
        $metrics[] = ['phase' => '2. High-Freq Read (50x GET)', 'ops' => $batchSize, 'ok' => $readOk, 'ms' => (hrtime(true) - $t0) / 1_000_000];

        // 3. Rapid Update
        $t0 = hrtime(true);
        $updateOk = 0;
        $updateMethod = ($updateRoute && in_array('PATCH', $updateRoute->methods(), true) && ! in_array('PUT', $updateRoute->methods(), true))
            ? 'patchJson' : 'putJson';
        foreach ($createdIds as $idx => $id) {
            $uri = '/' . ltrim(preg_replace('/\{[^}]+\}/', (string) $id, $updateRoute?->uri() ?? "api/credentials/{$id}"), '/');
            if ($this->{$updateMethod}($uri, $buildPayload($idx + 1, 'OFF'))->status() === 200) {
                $updateOk++;
            }
        }
        $metrics[] = ['phase' => '3. Rapid Update Status (50x PUT)', 'ops' => count($createdIds), 'ok' => $updateOk, 'ms' => (hrtime(true) - $t0) / 1_000_000];

        // 4. Batch Delete
        $t0 = hrtime(true);
        $deleteOk = 0;
        foreach ($createdIds as $id) {
            $uri = '/' . ltrim(preg_replace('/\{[^}]+\}/', (string) $id, $destroyRoute?->uri() ?? "api/credentials/{$id}"), '/');
            if (in_array($this->deleteJson($uri)->status(), [200, 204], true)) {
                $deleteOk++;
            }
        }
        $metrics[] = ['phase' => '4. Batch Delete (50x DELETE)', 'ops' => count($createdIds), 'ok' => $deleteOk, 'ms' => (hrtime(true) - $t0) / 1_000_000];

        // 5. Duplicate & XSS Guard
        $dupPayload = $buildPayload(999, 'ON');
        $dupPayload['nip'] = EmployeeCredential::firstOrFail()->nip;
        $dupBlocked = $this->postJson($storeUri, $dupPayload)->status() === 422;

        $xssPayload = $buildPayload(998, 'ON');
        $xssPayload['nama'] = '<script>alert(1)</script>';
        $xssBlocked = $this->postJson($storeUri, $xssPayload)->status() === 403;

        $metrics[] = ['phase' => '5. Duplicate NIP & WAF XSS Guard', 'ops' => 2, 'ok' => ($dupBlocked ? 1 : 0) + ($xssBlocked ? 1 : 0), 'ms' => 0.0];

        $totalOps = array_sum(array_column($metrics, 'ops'));
        $totalOk = array_sum(array_column($metrics, 'ok'));
        $totalMs = round(array_sum(array_column($metrics, 'ms')), 2);

        self::$sections['crud'] = [
            'rows' => $metrics,
            'total_ops' => $totalOps,
            'avg_latency' => $totalOps > 0 ? round($totalMs / $totalOps, 2) : 0.0,
            'throughput' => $totalMs > 0 ? round($totalOps / ($totalMs / 1000), 2) : 0.0,
            'success_rate' => $totalOps > 0 ? round(($totalOk / $totalOps) * 100, 2) : 0.0,
        ];

        $this->assertEquals($initialCount, EmployeeCredential::count());
        $this->assertEquals($totalOps, $totalOk);
    }

    public function test_3_high_volume_database_soak_benchmark(): void
    {
        Mail::fake();
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->seed();

        // Buat 1 log dasar valid lalu gandakan menjadi 10.000 baris log audit
        $this->postJson('/gateway/login', [
            'target_portal' => 'portal-asn.kalselprov.go.id',
            'simulated_ip' => '180.252.88.12',
            'nip' => "' OR '1'='1' --",
            'password' => 'x',
        ]);

        $template = SecurityAuditLog::firstOrFail()->getAttributes();
        unset($template['id']);

        $volume = 10000;
        $tInsert = hrtime(true);
        $rows = array_fill(0, $volume, $template);
        // Chunk 50 baris per batch agar aman terhadap batas variabel SQLite (999)
        foreach (array_chunk($rows, 50) as $chunk) {
            SecurityAuditLog::insert($chunk);
        }
        $insertMs = round((hrtime(true) - $tInsert) / 1_000_000, 2);

        $soakMetrics = [];
        $soakMetrics[] = [
            'task' => "1. Bulk Seeding ({$volume} Audit Logs)",
            'status' => SecurityAuditLog::count() >= $volume ? 'OK' : 'FAIL',
            'ms' => $insertMs,
            'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        // Benchmark Render Dashboard Overview dengan 10.000+ log
        $tDash = hrtime(true);
        $resDash = $this->get('/');
        $soakMetrics[] = [
            'task' => '2. Load Main Dashboard & KPI Aggregation (GET /)',
            'status' => 'HTTP ' . $resDash->status(),
            'ms' => round((hrtime(true) - $tDash) / 1_000_000, 2),
            'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        // Benchmark API Datalogs dengan 10.000+ log
        $tApi = hrtime(true);
        $resApi = $this->getJson('/api/datalogs');
        $soakMetrics[] = [
            'task' => '3. Query WAF Stream API (GET /api/datalogs)',
            'status' => 'HTTP ' . $resApi->status(),
            'ms' => round((hrtime(true) - $tApi) / 1_000_000, 2),
            'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        // Benchmark Export CSV (deteksi route export otomatis bila tersedia)
        $exportRoute = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($r) => str_contains(strtolower($r->uri()), 'export') || str_contains(strtolower($r->uri()), 'csv'));
        $exportUri = $exportRoute ? '/' . ltrim($exportRoute->uri(), '/') : '/api/datalogs?export=csv';

        $tCsv = hrtime(true);
        $resCsv = $this->get($exportUri);
        $soakMetrics[] = [
            'task' => "4. Export Audit Report ({$exportUri})",
            'status' => 'HTTP ' . $resCsv->status(),
            'ms' => round((hrtime(true) - $tCsv) / 1_000_000, 2),
            'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        self::$sections['soak'] = [
            'volume' => SecurityAuditLog::count(),
            'rows' => $soakMetrics,
        ];

        $this->assertEquals(200, $resDash->status());
        $this->assertEquals(200, $resApi->status());
    }

    public function test_4_security_fuzzing_and_protocol_bypass(): void
    {
        Cache::flush();
        Mail::fake();
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->seed();

        $fuzzResults = [];

        // 1. JSON Array / Type Juggling (mencegah PHP TypeError HTTP 500)
        $resArray = $this->postJson('/gateway/login', [
            'target_portal' => 'portal-asn.kalselprov.go.id',
            'simulated_ip' => '10.10.10.10',
            'nip' => ['admin', "' OR 1=1--"],
            'password' => ['x'],
        ]);
        $arraySafe = $resArray->status() !== 500 && in_array($resArray->status(), [400, 403, 422], true);
        $fuzzResults[] = [
            'vector' => '1. JSON Array / PHP Type Juggling',
            'payload' => '{"nip": ["admin", "\' OR 1=1--"]}',
            'http' => $resArray->status(),
            'verdict' => $arraySafe ? 'PASS (Handled Safely)' : 'WARN (HTTP ' . $resArray->status() . ')',
        ];

        // 2. Double URL-Encoding XSS
        $resDoubleEnc = $this->get('/?search=%253Cscript%253Ealert(1)%253C%252Fscript%253E');
        $fuzzResults[] = [
            'vector' => '2. Double URL-Encoded XSS',
            'payload' => '%253Cscript%253Ealert(1)%253C%252Fscript%253E',
            'http' => $resDoubleEnc->status(),
            'verdict' => $resDoubleEnc->status() === 403 ? 'PASS (Blocked 403)' : 'BYPASS (HTTP ' . $resDoubleEnc->status() . ')',
        ];

        // 3. Null-Byte Injection
        $resNullByte = $this->get('/?search=admin%00.php');
        $fuzzResults[] = [
            'vector' => '3. Null-Byte Injection (%00)',
            'payload' => 'admin%00.php',
            'http' => $resNullByte->status(),
            'verdict' => $resNullByte->status() === 403 ? 'PASS (Blocked 403)' : 'BYPASS (HTTP ' . $resNullByte->status() . ')',
        ];

        // 4. OTP Single-Use Replay Attack
        $this->postJson('/gateway/login', [
            'target_portal' => 'portal-asn.kalselprov.go.id',
            'simulated_ip' => '10.10.99.5',
            'nip' => '6371021506880003',
            'password' => 'PasswordAsn2026',
        ]);
        $cred = EmployeeCredential::where('nip', '6371021506880003')->firstOrFail();
        $otpToken = $cred->otp_code ?: 'SK-KALSEL-998';

        $firstUse = $this->postJson('/gateway/login', [
            'target_portal' => 'portal-asn.kalselprov.go.id',
            'simulated_ip' => '10.10.99.5',
            'nip' => '6371021506880003',
            'password' => 'PasswordAsn2026',
            'verification_token' => $otpToken,
        ]);

        // Replay token yang sama kedua kalinya
        $replayUse = $this->postJson('/gateway/login', [
            'target_portal' => 'portal-asn.kalselprov.go.id',
            'simulated_ip' => '10.10.99.5',
            'nip' => '6371021506880003',
            'password' => 'PasswordAsn2026',
            'verification_token' => $otpToken,
        ]);

        $replayBlocked = $firstUse->status() === 200 && $replayUse->status() !== 200;
        $fuzzResults[] = [
            'vector' => '4. OTP Token Replay Attack',
            'payload' => "1st: HTTP {$firstUse->status()} -> 2nd Replay: HTTP {$replayUse->status()}",
            'http' => $replayUse->status(),
            'verdict' => $replayBlocked ? 'PASS (Replay Rejected)' : 'FAIL (Replay Allowed)',
        ];

        self::$sections['fuzzing'] = $fuzzResults;

        $this->assertNotEquals(500, $resArray->status());
        $this->assertTrue($replayBlocked);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$reportPath !== '' && ! empty(self::$sections)) {
            self::writeUnifiedHtmlReport();
        }
        parent::tearDownAfterClass();
    }

    private static function writeUnifiedHtmlReport(): void
    {
        $kpi = self::$sections['kpi'] ?? null;
        $crud = self::$sections['crud'] ?? null;
        $soak = self::$sections['soak'] ?? null;
        $fuzz = self::$sections['fuzzing'] ?? [];

        $kpiRows = '';
        if ($kpi) {
            foreach ($kpi['rows'] as $r) {
                $kpiRows .= sprintf(
                    '<tr><td style="text-align:left;">%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d/%d</td><td><strong>%.2f%%</strong></td><td><strong>%.2f%%</strong></td><td><strong>%.2f%%</strong></td></tr>',
                    $r['label'], $r['tp'], $r['fn'], $r['fp'], $r['tn'], $r['mitigated'], $r['detected'], $r['dr'], $r['fpr'], $r['msr']
                );
            }
        }

        $findingsHtml = ($kpi && ! empty($kpi['findings']))
            ? implode('', array_map(fn ($f) => '<li>' . htmlspecialchars($f, ENT_QUOTES, 'UTF-8') . '</li>', $kpi['findings']))
            : '<li>Tidak ada anomali false positive maupun false negative pada seluruh iterasi.</li>';

        $crudRows = '';
        if ($crud) {
            foreach ($crud['rows'] as $m) {
                $lat = ($m['ops'] > 0 && $m['ms'] > 0) ? sprintf('%.2f ms/op', $m['ms'] / $m['ops']) : '-';
                $crudRows .= sprintf(
                    '<tr><td style="text-align:left;">%s</td><td>%d</td><td>%d</td><td>%.2f ms</td><td>%s</td></tr>',
                    $m['phase'], $m['ops'], $m['ok'], $m['ms'], $lat
                );
            }
        }

        $soakRows = '';
        if ($soak) {
            foreach ($soak['rows'] as $s) {
                $soakRows .= sprintf(
                    '<tr><td style="text-align:left;">%s</td><td>%s</td><td><strong>%.2f ms</strong></td><td>%.2f MB</td></tr>',
                    $s['task'], $s['status'], $s['ms'], $s['mem_mb']
                );
            }
        }

        $fuzzRows = '';
        foreach ($fuzz as $fz) {
            $fuzzRows .= sprintf(
                '<tr><td style="text-align:left;">%s</td><td style="text-align:left;font-family:monospace;">%s</td><td>HTTP %d</td><td><strong>%s</strong></td></tr>',
                $fz['vector'],
                htmlspecialchars($fz['payload'], ENT_QUOTES, 'UTF-8'),
                $fz['http'],
                $fz['verdict']
            );
        }

        $timestamp = date('Y-m-d H:i:s');
        $avgDr = $kpi['avg_dr'] ?? 0;
        $avgFpr = $kpi['avg_fpr'] ?? 0;
        $avgMsr = $kpi['avg_msr'] ?? 0;
        $crudThroughput = $crud['throughput'] ?? 0;
        $crudLatency = $crud['avg_latency'] ?? 0;
        $soakVol = number_format($soak['volume'] ?? 0);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Unified Evaluation, Stress & Security Fuzzing Report — SOC SPBE</title>
<style>
  body { font-family: system-ui, sans-serif; margin: 28px; color: #0f172a; background: #f1f5f9; }
  .container { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 28px; max-width: 1080px; margin: auto; }
  h1 { font-size: 21px; margin: 0 0 6px; }
  h2 { font-size: 15px; margin: 28px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #e2e8f0; color: #1e293b; }
  .meta { font-size: 13px; color: #475569; margin-bottom: 20px; }
  .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 22px; }
  .kpi { border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px; background: #f8fafc; }
  .kpi span { display: block; font-size: 11px; color: #475569; margin-bottom: 4px; }
  .kpi strong { font-size: 19px; color: #0284c7; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
  th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: center; }
  th { background: #0f172a; color: #fff; }
  tr:nth-child(even) { background: #f8fafc; }
  ul { font-size: 13px; color: #334155; background: #fefce8; border: 1px solid #fde047; padding: 12px 28px; border-radius: 8px; }
</style>
</head>
<body>
<div class="container">
  <h1>Laporan Terpadu Automation Testing, Stress Benchmark &amp; Security Fuzzing — SOC SPBE</h1>
  <div class="meta">Waktu Eksekusi: {$timestamp} | Environment: Laragon (PHPUnit + SQLite In-Memory) | Mocking: Mail::fake() &amp; Http::fake()</div>

  <div class="kpi-grid">
    <div class="kpi"><span>Avg Detection Rate</span><strong>{$avgDr}%</strong></div>
    <div class="kpi"><span>Avg False Positive</span><strong>{$avgFpr}%</strong></div>
    <div class="kpi"><span>Avg Mitigation Rate</span><strong>{$avgMsr}%</strong></div>
    <div class="kpi"><span>CRUD Throughput</span><strong>{$crudThroughput} ops/s</strong></div>
    <div class="kpi"><span>DB Soak Volume</span><strong>{$soakVol} logs</strong></div>
  </div>

  <h2>1. Evaluasi KPI Keamanan (3 Iterasi Variatif)</h2>
  <table>
    <thead>
      <tr>
        <th>Skenario Iterasi</th><th>TP</th><th>FN</th><th>FP</th><th>TN</th><th>Mitigasi</th>
        <th>Detection Rate</th><th>False Positive Rate</th><th>Mitigation Success Rate</th>
      </tr>
    </thead>
    <tbody>
      {$kpiRows}
      <tr style="background:#e0f2fe;font-weight:bold;">
        <td colspan="6">RATA-RATA SKOR AKHIR (3 ITERASI)</td>
        <td>{$avgDr}%</td><td>{$avgFpr}%</td><td>{$avgMsr}%</td>
      </tr>
    </tbody>
  </table>
  <ul>{$findingsHtml}</ul>

  <h2>2. Stress Testing Manajemen Kredensial ASN (202 Operasi CRUD &amp; Guard)</h2>
  <table>
    <thead>
      <tr><th>Fase Pengujian</th><th>Total Operasi</th><th>Berhasil</th><th>Total Waktu</th><th>Rata-Rata Latensi (Avg: {$crudLatency} ms)</th></tr>
    </thead>
    <tbody>{$crudRows}</tbody>
  </table>

  <h2>3. High-Volume Database Soak Benchmark ({$soakVol} Baris Audit Log)</h2>
  <table>
    <thead>
      <tr><th>Operasi Query / Agregasi</th><th>Respons</th><th>Waktu Eksekusi</th><th>Peak Memory</th></tr>
    </thead>
    <tbody>{$soakRows}</tbody>
  </table>

  <h2>4. Security Fuzzing &amp; Protocol Bypass Test</h2>
  <table>
    <thead>
      <tr><th>Vektor Uji</th><th>Payload / Skenario</th><th>Status</th><th>Hasil Evaluasi</th></tr>
    </thead>
    <tbody>{$fuzzRows}</tbody>
  </table>
</div>
</body>
</html>
HTML;

        file_put_contents(self::$reportPath, $html);
    }
}