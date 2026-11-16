{{-- <div id="tab-datalog" class="page-view space-y-6">
  
  <div class="theme-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <h2 class="theme-heading text-sm font-bold uppercase">
        DATA LOG STREAM (IIS / WAF)
      </h2>
      <p class="text-xs opacity-80 mt-1 font-mono">Ingestion Stream Endpoint /api/events</p>
    </div>
    <span class="font-mono text-[9px] px-3 py-1.5 bg-black/60 border border-current">samplelog.log</span>
  </div>

  <div class="theme-card p-5 space-y-3">
    <div class="flex items-center justify-between border-b border-current pb-3 text-xs font-mono">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 bg-cyan-400 inline-block"></span>
        <span class="w-3 h-3 bg-amber-300 inline-block"></span>
        <span class="w-3 h-3 bg-emerald-400 inline-block"></span>
        <span class="font-bold ml-2">Terminal View : JSON Raw Log Decoder</span>
      </div>
      <span class="opacity-70">Auto-Refresh Active</span>
    </div>

    <!-- TERMINAL LOG BOX DENGAN DUAL-MODE CONTRAST -->
    <div class="terminal-box bg-black/90 border border-current p-4 font-mono text-xs leading-relaxed h-[420px] overflow-y-auto space-y-2 rounded-xl shadow-inner">
      <div class="log-header text-amber-300 font-bold">#Software: Microsoft Internet Information Services 10.0 (SPBE Firewall)</div>
      <div class="log-header text-amber-300 font-bold">#Version: 2.0</div>
      <div class="log-header text-amber-300 font-bold">#Date: 2026-11-07 14:27:01</div>
      <div class="log-header text-amber-300 border-b border-current pb-2 mb-2 font-bold">#Fields: date time s-sitename s-computername s-ip cs-method cs-uri-stem cs-uri-query s-port cs-username c-ip</div>
      
      <div class="log-text">2026-11-07 14:27:01 SPBE_FIREWALL 222.222.222.222 GET /api/events 80 220.181.7.113 HTTP/1.1 200</div>
      <div class="log-text">2026-11-07 14:27:12 SPBE_FIREWALL 180.252.88.12 POST /login/auth 80 98.88.35.133 HTTP/1.1 200</div>
      
      <div class="log-danger text-rose-400 font-bold bg-rose-950/80 p-1.5 border border-rose-800 rounded">2026-11-07 14:27:32 SPBE_WAF 203.145.12.89 POST /api/v1/users?id=1' OR '1'='1 80 403 [SQLi Attack Pattern Match]</div>
      
      <div class="log-text">2026-11-07 14:27:39 SPBE_FIREWALL 192.168.10.15 GET /robots.txt 80 95.55.207.95 HTTP/1.1 200</div>
      <div class="log-text">2026-11-07 14:27:43 SPBE_FIREWALL 202.158.45.99 GET /dashboard/spbe 80 80.217.23.11 HTTP/1.1 200</div>
      
      <div class="log-warning text-amber-300 font-bold bg-amber-950/80 p-1.5 border border-amber-800 rounded">2026-11-07 14:28:05 SPBE_WAF 103.22.180.55 POST /api/events payload="SYN_FLOOD_BURST" 80 429 [Threshold Exceeded]</div>
      
      <div class="log-text">2026-11-07 14:28:15 SPBE_FIREWALL 192.168.10.15 GET /screen.css 80 98.88.35.133 HTTP/1.1 304</div>
      <div class="log-text">2026-11-07 14:29:02 SPBE_FIREWALL 192.168.10.22 GET /api/v1/health 80 192.168.10.22 HTTP/1.1 200</div>
    </div>
  </div>

</div> --}}


<div class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h3 class="text-sm font-bold uppercase tracking-wider">SPBE Unified Security &amp; Network Data Log</h3>
      <p class="text-xs opacity-75">Menampilkan log Gerbang Login ASN dan hasil tangkapan paket Wireshark (tshark)</p>
    </div>
    <div class="flex items-center gap-2 text-xs">
      <select id="log-source-filter" onchange="loadSecurityLogs(this.value)" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700">
        <option value="">Semua Sumber Log</option>
        <option value="GATEWAY_LOGIN">Gateway Login</option>
        <option value="TSHARK_CAPTURE">Wireshark / Tshark</option>
      </select>
      <button type="button" onclick="loadSecurityLogs()" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-lg">
        Refresh Log
      </button>
    </div>
  </div>

  <div class="overflow-x-auto border border-slate-800 rounded-xl">
    <table class="w-full text-left text-xs">
      <thead class="bg-slate-900/90 uppercase text-[10px]">
        <tr>
          <th class="p-3">Waktu</th>
          <th class="p-3">Sumber</th>
          <th class="p-3">IP Asal / Tujuan</th>
          <th class="p-3">Event &amp; Protokol</th>
          <th class="p-3">Severity</th>
          <th class="p-3">Status</th>
          <th class="p-3">Latency</th>
          <th class="p-3">Rekomendasi Tindakan</th>
        </tr>
      </thead>
      <tbody id="unified-datalog-body" class="divide-y divide-slate-800"></tbody>
    </table>
  </div>
</div>

<script>
async function loadSecurityLogs(source = '') {
  const tbody = document.getElementById('unified-datalog-body');
  if (!tbody) return;

  const url = source ? `/api/datalogs?source=${encodeURIComponent(source)}` : '/api/datalogs';
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  const json = await res.json();

  tbody.innerHTML = (json.data || []).map(row => `
    <tr class="hover:bg-slate-900/50">
      <td class="p-3 font-mono">${new Date(row.created_at).toLocaleTimeString('id-ID')}</td>
      <td class="p-3 font-bold">${row.source}</td>
      <td class="p-3 font-mono">${row.ip_address}${row.destination_ip ? ' -> ' + row.destination_ip : ''}</td>
      <td class="p-3">
        <div class="font-bold">${row.event_type} <span class="opacity-70">(${row.protocol})</span></div>
        <div class="text-[10px] opacity-75 font-mono">${row.payload_snippet || '-'}</div>
      </td>
      <td class="p-3 font-bold">${row.severity}</td>
      <td class="p-3 font-bold">${row.status}</td>
      <td class="p-3 font-mono">${row.detection_latency_ms} ms</td>
      <td class="p-3">${row.recommended_action}</td>
    </tr>
  `).join('') || '<tr><td colspan="8" class="p-4 text-center opacity-70">Belum ada log tercatat.</td></tr>';
}
document.addEventListener('DOMContentLoaded', () => loadSecurityLogs());
</script>