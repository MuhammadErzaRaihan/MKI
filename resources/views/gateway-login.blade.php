<!DOCTYPE html>
<html lang="id" class="dark" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Autentikasi SPBE — Diskominfo Kalsel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-dark min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 sm:p-6 antialiased" style="background-color:#020617;color:#f1f5f9;">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-7 shadow-2xl space-y-5" style="background-color:#0f172a;border-color:#1e293b;">
        <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500/15 border border-sky-500/40 flex items-center justify-center text-sky-400 shrink-0">
                    <i class="fa-solid fa-shield-halved text-base"></i>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-white tracking-wide">
                        Login SPBE
                    </h1>
                </div>
            </div>

            {{-- <a href="{{ url('/') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300 flex items-center gap-1.5 shrink-0 pt-1">
                <i class="fa-solid fa-chart-line"></i>
                <span>Admin dash</span>
            </a> --}}
        </div>

        <!-- Banner Status Ringkas (Detail telemetri lengkap tampil di Dashboard Admin) -->
        <div id="gw-alert" class="hidden p-3.5 rounded-xl border text-xs space-y-1"></div>

        <form id="gw-login-form" class="space-y-4">
            <div>
                <label for="gw-portal" class="block text-[11px] font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    Portal Tujuan SPBE
                </label>
                <select id="gw-portal" name="target_portal" class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono text-slate-100 focus:outline-none focus:border-sky-400" style="background-color:#020617;color:#f1f5f9;">
                    <option value="portal-asn.kalselprov.go.id">portal-asn.kalselprov.go.id (Portal Utama ASN)</option>
                    <option value="e-kinerja.kalselprov.go.id">e-kinerja.kalselprov.go.id (Sistem E-Kinerja)</option>
                    <option value="sipd-aset.kalselprov.go.id">sipd-aset.kalselprov.go.id (Portal Keuangan &amp; Aset)</option>
                </select>
            </div>

            <div>
                <label for="gw-ip" class="block text-[11px] font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    Simulasi IP Pengirim
                </label>
                <select id="gw-ip" name="simulated_ip" class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono text-slate-100 focus:outline-none focus:border-sky-400" style="background-color:#020617;color:#f1f5f9;">
                    <option value="10.10.10.10">10.10.10.10 — IP Resmi Instansi BPKAD</option>
                    <option value="192.168.10.15">192.168.10.15 — IP Kantor Diskominfo</option>
                    <option value="10.10.99.5">10.10.99.5 — IP Instansi Lain (Mismatch / Wajib OTP)</option>
                    <option value="180.252.88.12">180.252.88.12 — IP Publik Luar Kantor (Wajib OTP)</option>
                    <option value="203.0.113.77">203.0.113.77 — IP Uji Brute Force (Lockout 3x)</option>
                </select>
            </div>

            <div>
                <label for="gw-nip" class="block text-[11px] font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    NIP / Username
                </label>
                <input id="gw-nip" name="nip" type="text" autocomplete="off"
                       placeholder="Masukkan 18 digit NIP atau payload uji"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono text-white placeholder:text-slate-400 focus:outline-none focus:border-sky-400"
                       style="background-color:#020617;color:#ffffff;">
            </div>

            <div>
                <label for="gw-password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    Kata Sandi
                </label>
                <input id="gw-password" name="password" type="password" autocomplete="off"
                       placeholder="Masukkan kata sandi ASN"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-xs font-mono text-white placeholder:text-slate-400 focus:outline-none focus:border-sky-400"
                       style="background-color:#020617;color:#ffffff;">
            </div>

            <div id="gw-mfa-box" class="hidden p-3.5 rounded-xl bg-amber-500/10 border border-amber-400/40 space-y-2">
                <label for="gw-token" class="block text-[11px] font-bold uppercase tracking-wider text-amber-300">
                    <i class="fa-solid fa-key mr-1"></i>Kode OTP Email ASN / Secret Key
                </label>
                <input id="gw-token" name="verification_token" type="text" autocomplete="off"
                       placeholder="Masukkan 6 digit OTP atau Secret Key (mis. SK-KALSEL-998)"
                       class="w-full px-3.5 py-2 rounded-lg bg-slate-950 border border-amber-400/50 text-xs font-mono text-white placeholder:text-slate-400 focus:outline-none focus:border-amber-300"
                       style="background-color:#020617;color:#ffffff;">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" id="gw-submit-btn"
                        class="flex-1 py-2.5 px-4 rounded-lg bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs uppercase tracking-wider transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Verifikasi &amp; Masuk Portal</span>
                </button>

                <button type="button" id="gw-reset-btn" title="Reset Form"
                        class="px-3.5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-100 text-xs transition-colors cursor-pointer">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>
        </form>

        <div class="pt-4 border-t border-slate-800 space-y-2.5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-200">
                Preset Skenario Uji Terkontrol
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-preset="office" class="px-2.5 py-1.5 rounded-md bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-400/40 text-emerald-300 text-[11px] font-semibold cursor-pointer">
                    1. IP Kantor Valid
                </button>
                <button type="button" data-preset="external" class="px-2.5 py-1.5 rounded-md bg-amber-500/15 hover:bg-amber-500/25 border border-amber-400/40 text-amber-300 text-[11px] font-semibold cursor-pointer">
                    2. IP Luar Kantor (OTP)
                </button>
                <button type="button" data-preset="sqli" class="px-2.5 py-1.5 rounded-md bg-rose-500/15 hover:bg-rose-500/25 border border-rose-400/40 text-rose-300 text-[11px] font-semibold cursor-pointer">
                    3. SQL Injection
                </button>
                <button type="button" data-preset="xss" class="px-2.5 py-1.5 rounded-md bg-pink-500/15 hover:bg-pink-500/25 border border-pink-400/40 text-pink-300 text-[11px] font-semibold cursor-pointer">
                    4. XSS Attack
                </button>
                <button type="button" data-preset="invalid" class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-100 text-[11px] font-semibold cursor-pointer">
                    5. Kredensial Salah
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('gw-login-form');
            const ipSelect = document.getElementById('gw-ip');
            const nipInput = document.getElementById('gw-nip');
            const passInput = document.getElementById('gw-password');
            const mfaBox = document.getElementById('gw-mfa-box');
            const tokenInput = document.getElementById('gw-token');
            const alertBox = document.getElementById('gw-alert');
            const resetBtn = document.getElementById('gw-reset-btn');
            const submitBtn = document.getElementById('gw-submit-btn');

            const presets = {
                office: { nip: '6371021506880003', password: 'PasswordAsn2026', ip: '10.10.10.10' },
                external: { nip: '6371021506880003', password: 'PasswordAsn2026', ip: '10.10.99.5' },
                sqli: { nip: "' OR '1'='1' --", password: 'x', ip: '180.252.88.12' },
                xss: { nip: '<script>alert(document.cookie)<\/script>', password: 'x', ip: '192.168.10.15' },
                invalid: { nip: '6371012903910001', password: 'SalahPassword123', ip: '203.0.113.77' },
            };

            document.querySelectorAll('[data-preset]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const p = presets[btn.dataset.preset];
                    if (!p) return;
                    ipSelect.value = p.ip;
                    nipInput.value = p.nip;
                    passInput.value = p.password;
                    tokenInput.value = '';
                    mfaBox.classList.add('hidden');
                    alertBox.classList.add('hidden');
                });
            });

            resetBtn.addEventListener('click', () => {
                form.reset();
                tokenInput.value = '';
                mfaBox.classList.add('hidden');
                alertBox.classList.add('hidden');
            });

            function showAlert(httpStatus, data) {
                const status = data.status || 'BLOCKED';
                const styles = status === 'ALLOWED'
                    ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200'
                    : status === 'SUSPICIOUS'
                        ? 'border-amber-400/40 bg-amber-500/10 text-amber-200'
                        : 'border-rose-400/40 bg-rose-500/10 text-rose-200';

                if (data.requires_mfa) {
                    mfaBox.classList.remove('hidden');
                    tokenInput.focus();
                } else if (status === 'ALLOWED') {
                    mfaBox.classList.add('hidden');
                    tokenInput.value = '';
                }

                alertBox.className = 'p-3.5 rounded-xl border text-xs space-y-1 ' + styles;
                alertBox.innerHTML = '';

                const title = document.createElement('p');
                title.className = 'font-bold font-mono';
                title.textContent = `[${status} • HTTP ${httpStatus}] ${data.event_type || ''}`;

                const msg = document.createElement('p');
                msg.textContent = data.message || '-';

                alertBox.append(title, msg);
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitBtn.disabled = true;

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const res = await fetch('/gateway/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            target_portal: document.getElementById('gw-portal').value,
                            simulated_ip: ipSelect.value,
                            nip: nipInput.value,
                            password: passInput.value,
                            verification_token: tokenInput.value,
                        }),
                    });

                    const data = await res.json();
                    showAlert(res.status, data);
                } catch {
                    showAlert(500, {
                        status: 'BLOCKED',
                        event_type: 'NETWORK_ERROR',
                        message: 'Gagal terhubung ke endpoint /gateway/login.',
                    });
                } finally {
                    submitBtn.disabled = false;
                }
            });
        })();
    </script>
</body>
</html>