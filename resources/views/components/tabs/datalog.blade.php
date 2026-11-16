<div id="tab-datalog" class="page-view hidden space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 theme-card p-5 rounded-2xl">
        <div>
            <h2 class="theme-heading text-sm font-bold uppercase tracking-wider">
                <i class="fa-solid fa-database text-cyan-400 mr-2"></i>Security Audit &amp; Packet Data Log
            </h2>
            <p class="text-xs opacity-75 mt-0.5">
                Telemetri real-time dari Gerbang Login SPBE dan hasil inspeksi Tshark PCAP.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="datalog-source-filter" class="px-3 py-2 rounded-lg bg-black/60 border border-current text-xs font-mono focus:outline-none">
                <option value="">Semua Sumber</option>
                <option value="WEB_WAF">WEB_WAF</option>
                <option value="GATEWAY_LOGIN">GATEWAY_LOGIN</option>
                <option value="TSHARK_CAPTURE">TSHARK_CAPTURE</option>
            </select>

            <button type="button" id="datalog-refresh-btn" class="px-3 py-2 rounded-lg bg-black/60 border border-current text-xs font-bold uppercase hover:opacity-80">
                <i class="fa-solid fa-rotate mr-1"></i>Refresh
            </button>

            <button type="button" id="datalog-telegram-btn" class="px-3 py-2 rounded-lg theme-button-primary text-xs font-bold uppercase flex items-center gap-1.5">
                <i class="fa-brands fa-telegram"></i>Kirim Ringkasan Telegram
            </button>
        </div>
    </div>

    <div id="datalog-feedback" class="hidden px-4 py-2.5 rounded-lg text-xs font-mono border"></div>

    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-current bg-black/40 text-[10px] font-bold uppercase tracking-wider opacity-80">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Sumber</th>
                        <th class="p-3">NIP / Sensor</th>
                        <th class="p-3">IP Asal</th>
                        <th class="p-3">Event &amp; Severity</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Latency</th>
                        <th class="p-3">Payload / Detail</th>
                    </tr>
                </thead>
                <tbody id="datalog-table-body" class="divide-y divide-slate-800/80 text-xs">
                    <tr>
                        <td colspan="8" class="p-6 text-center opacity-70">Memuat log keamanan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        const tbody = document.getElementById('datalog-table-body');
        const filterSelect = document.getElementById('datalog-source-filter');
        const refreshBtn = document.getElementById('datalog-refresh-btn');
        const telegramBtn = document.getElementById('datalog-telegram-btn');
        const feedback = document.getElementById('datalog-feedback');

        if (!tbody) return;

        function showFeedback(message, isError = false) {
            feedback.textContent = message;
            feedback.className = 'px-4 py-2.5 rounded-lg text-xs font-mono border ' + (
                isError
                    ? 'bg-rose-500/10 border-rose-500/40 text-rose-300'
                    : 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300'
            );
        }

        function createCell(text, className = 'p-3') {
            const td = document.createElement('td');
            td.className = className;
            td.textContent = text ?? '-';
            return td;
        }

        async function loadDataLogs() {
            const source = filterSelect ? filterSelect.value : '';
            const url = source ? `/api/datalogs?source=${encodeURIComponent(source)}` : '/api/datalogs';

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Gagal memuat data log dari server.');

                const json = await res.json();
                const rows = json.data || [];

                tbody.innerHTML = '';

                if (rows.length === 0) {
                    const tr = document.createElement('tr');
                    const td = createCell('Belum ada log tercatat. Jalankan simulasi di Gerbang Login atau perintah spbe:ingest-tshark.', 'p-6 text-center opacity-70');
                    td.colSpan = 8;
                    tr.appendChild(td);
                    tbody.appendChild(tr);
                    return;
                }

                rows.forEach((row) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-black/30 transition-colors';

                    const timestamp = row.created_at ? new Date(row.created_at).toLocaleString('id-ID') : '-';
                    tr.appendChild(createCell(timestamp, 'p-3 font-mono text-[11px] opacity-75 whitespace-nowrap'));

                    const sourceTd = document.createElement('td');
                    sourceTd.className = 'p-3 font-mono text-[10px]';
                    const sourceBadge = document.createElement('span');
                    sourceBadge.className = 'px-2 py-0.5 rounded border ' + (
                        row.source === 'TSHARK_CAPTURE'
                            ? 'bg-purple-500/15 text-purple-300 border-purple-500/30'
                            : 'bg-sky-500/15 text-sky-300 border-sky-500/30'
                    );
                    sourceBadge.textContent = row.source || 'GATEWAY_LOGIN';
                    sourceTd.appendChild(sourceBadge);
                    tr.appendChild(sourceTd);

                    tr.appendChild(createCell(row.nip, 'p-3 font-mono text-[11px]'));
                    tr.appendChild(createCell(row.ip_address, 'p-3 font-mono text-[11px] text-cyan-300'));
                    tr.appendChild(createCell(`${row.event_type} (${row.severity})`, 'p-3 font-mono text-[11px]'));

                    const statusTd = document.createElement('td');
                    statusTd.className = 'p-3';
                    const statusBadge = document.createElement('span');
                    statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase ' + (
                        row.status === 'ALLOWED'
                            ? 'bg-emerald-400 text-slate-950'
                            : row.status === 'SUSPICIOUS'
                                ? 'bg-amber-400 text-slate-950'
                                : 'bg-rose-500 text-white'
                    );
                    statusBadge.textContent = row.status;
                    statusTd.appendChild(statusBadge);
                    tr.appendChild(statusTd);

                    tr.appendChild(createCell(`${row.detection_latency_ms ?? 0} ms`, 'p-3 text-right font-mono text-[11px] text-cyan-400 whitespace-nowrap'));
                    tr.appendChild(createCell(row.payload_snippet, 'p-3 font-mono text-[11px] max-w-xs truncate'));

                    tbody.appendChild(tr);
                });

                if (window.renderBlockedLogs) window.renderBlockedLogs(rows);
                if (window.renderTrafficLogs) window.renderTrafficLogs(rows);
            } catch (err) {
                tbody.innerHTML = '';
                const tr = document.createElement('tr');
                const td = createCell(err.message, 'p-6 text-center text-rose-400');
                td.colSpan = 8;
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        }

        if (filterSelect) filterSelect.addEventListener('change', loadDataLogs);
        if (refreshBtn) refreshBtn.addEventListener('click', loadDataLogs);

        if (telegramBtn) {
            telegramBtn.addEventListener('click', async () => {
                telegramBtn.disabled = true;
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const res = await fetch('/api/monitoring/telegram-summary', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });
                    const data = await res.json();
                    showFeedback(data.message || 'Permintaan selesai.', !res.ok);
                } catch {
                    showFeedback('Gagal menghubungi endpoint Telegram summary.', true);
                } finally {
                    telegramBtn.disabled = false;
                }
            });
        }

        window.refreshDataLogs = loadDataLogs;
        loadDataLogs();
    })();
</script>