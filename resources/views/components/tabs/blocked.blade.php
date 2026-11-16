<div id="tab-blocked" class="page-view hidden space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 theme-card p-5 rounded-2xl">
        <div>
            <h2 class="theme-heading text-sm font-bold uppercase tracking-wider text-rose-400">
                <i class="fa-solid fa-shield-virus mr-2"></i>Blocked Threats &amp; Mitigated IPs
            </h2>
            <p class="text-xs opacity-75 mt-0.5">
                Daftar serangan aplikasi (SQLi/XSS), kredensial tidak sah, dan anomali paket yang diblokir otomatis.
            </p>
        </div>

        <button type="button" onclick="window.refreshDataLogs && window.refreshDataLogs()" class="px-3 py-2 rounded-lg theme-button-primary text-xs font-bold uppercase">
            <i class="fa-solid fa-rotate mr-1"></i>Sync Blocked List
        </button>
    </div>

    <div class="theme-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-current bg-black/40 text-[10px] font-bold uppercase tracking-wider opacity-80">
                        <th class="p-3">Waktu Blokir</th>
                        <th class="p-3">IP Penyerang</th>
                        <th class="p-3">Jenis Serangan</th>
                        <th class="p-3">Severity</th>
                        <th class="p-3">Tindakan Mitigasi</th>
                    </tr>
                </thead>
                <tbody id="blocked-table-body" class="divide-y divide-slate-800/80 text-xs">
                    <tr>
                        <td colspan="5" class="p-6 text-center opacity-70">Memuat daftar blokir...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.renderBlockedLogs = function (allRows) {
        const tbody = document.getElementById('blocked-table-body');
        if (!tbody) return;

        const blocked = (allRows || []).filter((r) => r.status === 'BLOCKED');
        tbody.innerHTML = '';

        if (blocked.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'p-6 text-center opacity-70';
            td.textContent = 'Belum ada serangan berstatus BLOCKED.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        blocked.forEach((row) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-black/30 transition-colors';

            const cells = [
                { text: row.created_at ? new Date(row.created_at).toLocaleString('id-ID') : '-', cls: 'p-3 font-mono text-[11px] opacity-75' },
                { text: row.ip_address || '-', cls: 'p-3 font-mono text-[11px] text-rose-400 font-bold' },
                { text: row.event_type || '-', cls: 'p-3 font-mono text-[11px]' },
                { text: row.severity || '-', cls: 'p-3 font-mono text-[11px] text-amber-300 font-bold' },
                { text: row.recommended_action || '-', cls: 'p-3 text-[11px] opacity-85' },
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