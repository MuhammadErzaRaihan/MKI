<div id="tab-datalog" class="page-view space-y-6">
  
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

</div>