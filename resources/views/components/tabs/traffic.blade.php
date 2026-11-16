<div id="tab-traffic" class="page-view hidden space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 theme-card p-5 rounded-2xl">
        <div>
            <h2 class="theme-heading text-sm font-bold uppercase tracking-wider text-cyan-400">
                <i class="fa-solid fa-wave-square mr-2"></i>Network &amp; Gateway Traffic Monitor
            </h2>
            <p class="text-xs opacity-75 mt-0.5">
                Aliran paket jaringan hasil parsing Tshark PCAP dan permintaan autentikasi Gerbang SPBE.
            </p>
        </div>

        <button type="button" onclick="window.refreshDataLogs && window.refreshDataLogs()" class="px-3 py-2 rounded-lg theme-button-primary text-xs font-bold uppercase">
            <i class="fa-solid fa-rotate mr-1"></i>Sync Traffic
        </button>
    </div>

    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-current bg-black/40 text-[10px] font-bold uppercase tracking-wider opacity-80">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Layer / Sumber</th>
                        <th class="p-3">Source IP</th>
                        <th class="p-3">Destination IP</th>
                        <th class="p-3">Protokol</th>
                        <th class="p-3">Klasifikasi</th>
                        <th class="p-3 text-right">Latency</th>
                    </tr>
                </thead>
                <tbody id="traffic-table-body" class="divide-y divide-slate-800/80 text-xs">
                    <tr>
                        <td colspan="7" class="p-6 text-center opacity-70">Memuat telemetri trafik...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.renderTrafficLogs = function (allRows) {
        const tbody = document.getElementById('traffic-table-body');
        if (!tbody) return;

        const rows = allRows || [];
        tbody.innerHTML = '';

        if (rows.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'p-6 text-center opacity-70';
            td.textContent = 'Belum ada trafik terekam. Jalankan spbe:ingest-tshark atau uji login di /login.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-black/30 transition-colors';

            const cells = [
                { text: row.created_at ? new Date(row.created_at).toLocaleString('id-ID') : '-', cls: 'p-3 font-mono text-[11px] opacity-75 whitespace-nowrap' },
                { text: row.source || 'GATEWAY_LOGIN', cls: 'p-3 font-mono text-[11px] font-bold' },
                { text: row.ip_address || '-', cls: 'p-3 font-mono text-[11px] text-cyan-300' },
                { text: row.destination_ip || '10.10.1.5 (SPBE-GW)', cls: 'p-3 font-mono text-[11px] opacity-80' },
                { text: row.protocol || 'HTTP/POST', cls: 'p-3 font-mono text-[11px]' },
                { text: `${row.event_type} [${row.status}]`, cls: 'p-3 font-mono text-[11px] ' + (row.status === 'BLOCKED' ? 'text-rose-400 font-bold' : row.status === 'SUSPICIOUS' ? 'text-amber-300' : 'text-emerald-400') },
                { text: `${row.detection_latency_ms ?? 0} ms`, cls: 'p-3 text-right font-mono text-[11px] text-cyan-400 whitespace-nowrap' },
            ];

            cells.forEach((c) => {
                const td = document.createElement('td');
                td.className = c.cls;
                td.textContent = c.text;
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
    };
</script>