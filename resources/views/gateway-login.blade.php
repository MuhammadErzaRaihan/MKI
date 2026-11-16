<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gerbang Login ASN - SPBE Diskominfo Kalsel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-sky-600/20 border border-sky-500/40 flex items-center justify-center text-sky-400">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold tracking-wide uppercase">Gerbang Autentikasi SPBE</h1>
                        <p class="text-xs text-slate-400">Diskominfo Provinsi Kalimantan Selatan</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="text-xs text-sky-400 hover:text-sky-300 font-mono">
                    <i class="fa-solid fa-chart-line mr-1"></i>SOC Dashboard
                </a>
            </div>

            <form id="spbe-gateway-form" class="space-y-4">
                <div>
                    <label for="gw-portal" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        Aplikasi Tujuan Instansi
                    </label>
                    <select id="gw-portal" name="target_portal" class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono focus:outline-none focus:border-sky-500">
                        <option value="portal-asn.kalselprov.go.id">portal-asn.kalselprov.go.id (Portal Utama ASN)</option>
                        <option value="e-kinerja.kalselprov.go.id">e-kinerja.kalselprov.go.id (E-Kinerja Pemprov)</option>
                        <option value="simpeg.kalselprov.go.id">simpeg.kalselprov.go.id (Sistem Kepegawaian)</option>
                    </select>
                </div>

                <div>
                    <label for="gw-ip" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        Simulasi IP Klien (Lingkup Pengujian Lab)
                    </label>
                    <select id="gw-ip" name="simulated_ip" class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono focus:outline-none focus:border-sky-500">
                        <option value="192.168.10.15">192.168.10.15 — IP Kantor Diskominfo</option>
                        <option value="10.10.10.10">10.10.10.10 — IP Resmi Instansi BPKAD</option>
                        <option value="10.10.99.5">10.10.99.5 — IP Instansi Lain (Mismatch / Wajib OTP)</option>
                        <option value="180.252.88.12">180.252.88.12 — IP Publik Luar Kantor (Wajib OTP)</option>
                    </select>
                </div>

                <div>
                    <label for="gw-nip" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        NIP Pegawai ASN
                    </label>
                    <!-- Leave unconstrained so SQLi and XSS test payloads reach the backend inspector. -->
                    <input
                        type="text"
                        id="gw-nip"
                        name="nip"
                        placeholder="Masukkan 18 digit NIP atau payload uji"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono focus:outline-none focus:border-sky-500"
                    >
                </div>

                <div>
                    <label for="gw-password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        Kata Sandi
                    </label>
                    <input
                        type="password"
                        id="gw-password"
                        name="password"
                        placeholder="Masukkan kata sandi ASN"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono focus:outline-none focus:border-sky-500"
                    >
                </div>

                <div id="mfa-field-wrapper" class="hidden p-3 rounded-lg bg-amber-500/10 border border-amber-500/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="gw-token" class="text-[11px] font-bold uppercase tracking-wider text-amber-300">
                            <i class="fa-solid fa-key mr-1"></i>Verifikasi OTP Email / Secret Key
                        </label>
                        <span id="mfa-target-email" class="text-[10px] font-mono text-amber-200"></span>
                    </div>
                    <input
                        type="text"
                        id="gw-token"
                        name="verification_token"
                        placeholder="Masukkan 6 digit OTP email atau SK-KALSEL-XXX"
                        class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-amber-500/50 text-xs font-mono focus:outline-none focus:border-amber-400"
                    >
                </div>

                <div class="pt-1 flex items-center gap-2">
                    <button
                        type="submit"
                        id="gw-submit-btn"
                        class="flex-1 py-2.5 px-4 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                    >
                        <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Verifikasi &amp; Masuk Portal
                    </button>
                    <button
                        type="button"
                        id="gw-reset-btn"
                        class="py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-lg transition-colors"
                        title="Reset Form"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-800">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Preset Skenario Uji Terkontrol (UTS)
                </p>
                <div class="flex flex-wrap gap-1.5 text-[11px]">
                    <button type="button" data-preset="office" class="preset-btn px-2.5 py-1 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/25">
                        1. IP Kantor Valid
                    </button>
                    <button type="button" data-preset="external" class="preset-btn px-2.5 py-1 rounded bg-amber-500/15 text-amber-300 border border-amber-500/30 hover:bg-amber-500/25">
                        2. IP Luar Kantor (OTP)
                    </button>
                    <button type="button" data-preset="sqli" class="preset-btn px-2.5 py-1 rounded bg-rose-500/15 text-rose-300 border border-rose-500/30 hover:bg-rose-500/25">
                        3. SQL Injection
                    </button>
                    <button type="button" data-preset="xss" class="preset-btn px-2.5 py-1 rounded bg-rose-500/15 text-rose-300 border border-rose-500/30 hover:bg-rose-500/25">
                        4. XSS Attack
                    </button>
                    <button type="button" data-preset="invalid" class="preset-btn px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700">
                        5. Kredensial Salah
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-3 mb-4">
                    Hasil Inspeksi Gerbang &amp; Telemetri
                </h2>

                <div id="verdict-empty" class="text-xs text-slate-400 py-8 text-center">
                    Kirim permintaan login untuk melihat status keputusan akses, latency deteksi, dan aksi mitigasi.
                </div>

                <div id="verdict-card" class="hidden space-y-4">
                    <div class="flex items-center justify-between">
                        <span id="verdict-badge" class="px-2.5 py-1 rounded text-xs font-bold uppercase"></span>
                        <span id="verdict-latency" class="text-xs font-mono text-sky-400"></span>
                    </div>

                    <p id="verdict-message" class="text-xs leading-relaxed text-slate-200 bg-slate-950 p-3 rounded-lg border border-slate-800"></p>

                    <dl class="grid grid-cols-2 gap-2 text-[11px] font-mono bg-slate-950 p-3 rounded-lg border border-slate-800">
                        <dt class="text-slate-400">Event Type</dt>
                        <dd id="verdict-event" class="text-right font-bold text-slate-200">-</dd>
                        <dt class="text-slate-400">HTTP Code</dt>
                        <dd id="verdict-http" class="text-right font-bold text-slate-200">-</dd>
                        <dt class="text-slate-400">Portal Tujuan</dt>
                        <dd id="verdict-portal" class="text-right text-slate-300 truncate">-</dd>
                    </dl>

                    <a
                        id="verdict-enter-btn"
                        href="{{ route('dashboard') }}"
                        class="hidden w-full text-center py-2.5 px-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                    >
                        <i class="fa-solid fa-door-open mr-1.5"></i>Lanjut ke Dashboard SPBE
                    </a>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800 text-[11px] text-slate-400 space-y-1">
                <div class="font-bold text-slate-300">Aturan Kebijakan Akses:</div>
                <p>1. Payload melewati filter regex SQLi/XSS sebelum query autentikasi.</p>
                <p>2. IP di luar CIDR instansi ditandai <span class="text-amber-300 font-mono">SUSPICIOUS</span> dan memicu OTP email serta alert Telegram SOC.</p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('spbe-gateway-form');
        const mfaWrapper = document.getElementById('mfa-field-wrapper');
        const mfaTargetEmail = document.getElementById('mfa-target-email');
        const tokenInput = document.getElementById('gw-token');

        const presets = {
            office: { nip: '6371021506880003', password: 'PasswordAsn2026', ip: '10.10.10.10' },
            external: { nip: '6371021506880003', password: 'PasswordAsn2026', ip: '10.10.99.5' },
            sqli: { nip: "' OR '1'='1' --", password: 'x', ip: '180.252.88.12' },
            xss: { nip: '<script>alert(document.cookie)<\/script>', password: 'x', ip: '192.168.10.15' },
            invalid: { nip: '6371012903910001', password: 'SalahPassword123', ip: '180.252.88.12' },
        };

        document.querySelectorAll('.preset-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const p = presets[btn.dataset.preset];
                if (!p) return;
                document.getElementById('gw-nip').value = p.nip;
                document.getElementById('gw-password').value = p.password;
                document.getElementById('gw-ip').value = p.ip;
                tokenInput.value = '';
                mfaWrapper.classList.add('hidden');
            });
        });

        document.getElementById('gw-reset-btn').addEventListener('click', () => {
            form.reset();
            mfaWrapper.classList.add('hidden');
            document.getElementById('verdict-card').classList.add('hidden');
            document.getElementById('verdict-empty').classList.remove('hidden');
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('gw-submit-btn');
            submitBtn.disabled = true;

            const payload = {
                target_portal: document.getElementById('gw-portal').value,
                simulated_ip: document.getElementById('gw-ip').value,
                nip: document.getElementById('gw-nip').value,
                password: document.getElementById('gw-password').value,
                verification_token: tokenInput.value.trim(),
            };

            try {
                const res = await fetch('{{ route('gateway.login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                renderVerdict(res.status, data, payload.target_portal);
            } finally {
                submitBtn.disabled = false;
            }
        });

        function renderVerdict(httpStatus, data, portal) {
            document.getElementById('verdict-empty').classList.add('hidden');
            document.getElementById('verdict-card').classList.remove('hidden');

            const badge = document.getElementById('verdict-badge');
            const status = data.status || 'BLOCKED';
            badge.textContent = status;
            badge.className = 'px-2.5 py-1 rounded text-xs font-bold uppercase ' + (
                status === 'ALLOWED'
                    ? 'bg-emerald-400 text-slate-950'
                    : status === 'SUSPICIOUS'
                        ? 'bg-amber-400 text-slate-950'
                        : 'bg-rose-500 text-white'
            );

            document.getElementById('verdict-latency').textContent = `${data.latency_ms ?? 0} ms`;
            document.getElementById('verdict-message').textContent = data.message || '-';
            document.getElementById('verdict-event').textContent = data.event_type || (status === 'SUSPICIOUS' ? 'SUSPICIOUS_EXTERNAL_IP' : 'OFFICE_IP_LOGIN');
            document.getElementById('verdict-http').textContent = httpStatus;
            document.getElementById('verdict-portal').textContent = data.redirect_portal || portal;

            if (data.requires_mfa) {
                mfaWrapper.classList.remove('hidden');
                mfaTargetEmail.textContent = data.otp_sent_to ? `Terkirim ke: ${data.otp_sent_to}` : 'Gunakan Secret Key';
                tokenInput.focus();
            } else if (status === 'ALLOWED') {
                mfaWrapper.classList.add('hidden');
            }

            const enterBtn = document.getElementById('verdict-enter-btn');
            if (data.authenticated) {
                enterBtn.classList.remove('hidden');
            } else {
                enterBtn.classList.add('hidden');
            }
        }
    </script>
</body>
</html>