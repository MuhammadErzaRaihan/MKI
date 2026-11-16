<div id="tab-overview" class="page-view active space-y-6">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 theme-card p-5">
    <div>
      <div class="flex items-center gap-3">
        <h2 class="theme-heading text-lg font-extrabold uppercase tracking-wide text-amber-300">
          INCIDENT #INC-2048
        </h2>
        <span class="text-[10px] font-bold px-2.5 py-1 bg-rose-600 text-white uppercase animate-pulse">CRITICAL ANOMALY</span>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('gateway.login.form') }}" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2">
        <i class="fa-solid fa-shield-halved"></i>
        <span>BUKA GERBANG LOGIN</span>
      </a>
      <button onclick="window.openModal('Memuat laporan insiden #INC-2048 format PDF...')" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>EXPORT REPORT</span>
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">SERANGAN DIBLOKIR</div>
        <div class="font-brutal font-black text-xl mt-1 text-rose-400">{{ $stats['blocked_count'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-60 mt-0.5">SQLi, XSS &amp; anomali paket</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl text-rose-400">
        <i class="fa-solid fa-ban"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">AKSES MENCURIGAKAN</div>
        <div class="font-brutal font-black text-xl mt-1 text-amber-300">{{ $stats['suspicious_count'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-60 mt-0.5">IP luar kantor / MFA</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl text-amber-300">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">AVG DETECTION LATENCY</div>
        <div class="font-brutal font-black text-xl mt-1 text-cyan-400">{{ $stats['avg_latency_ms'] ?? 0 }} ms</div>
        <div class="text-[10px] font-mono opacity-60 mt-0.5">Dari {{ $stats['total_events'] ?? 0 }} total event</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl text-cyan-400">
        <i class="fa-solid fa-gauge-high"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">KREDENSIAL ASN AKTIF</div>
        <div class="font-brutal font-black text-xl mt-1 text-emerald-400">{{ $stats['active_asn'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-60 mt-0.5">Terdaftar pada Gerbang SPBE</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl text-emerald-400">
        <i class="fa-solid fa-id-card-clip"></i>
      </div>
    </div>
  </div>

  <!-- Panel Hasil Inspeksi Gerbang & Telemetri -->
  <div class="theme-card p-5 rounded-2xl space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-current">
      <div>
        <h3 class="theme-heading text-xs font-bold uppercase tracking-wider">
          <i class="fa-solid fa-radar text-cyan-400 mr-2"></i>Hasil Inspeksi Gerbang &amp; Telemetri
        </h3>
        <p class="text-xs opacity-75 mt-0.5">
          Keputusan akses real-time, detection latency, dan aksi mitigasi dari Gerbang SPBE &amp; Web WAF.
        </p>
      </div>

      <button type="button" id="overview-telemetry-refresh" class="px-3 py-1.5 rounded-lg theme-button-primary text-xs font-bold uppercase self-start sm:self-auto">
        <i class="fa-solid fa-rotate mr-1"></i>Refresh Telemetri
      </button>
    </div>

    <div id="overview-latest-telemetry" class="p-4 rounded-xl border border-current bg-black/30 text-xs">
      Memuat hasil inspeksi terbaru...
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-current text-[10px] font-bold uppercase tracking-wider opacity-75">
            <th class="py-2 pr-3">Waktu</th>
            <th class="py-2 pr-3">IP Asal</th>
            <th class="py-2 pr-3">Event</th>
            <th class="py-2 pr-3">Status</th>
            <th class="py-2 text-right">Latency</th>
          </tr>
        </thead>
        <tbody id="overview-telemetry-list" class="divide-y divide-slate-800/60"></tbody>
      </table>
    </div>
  </div>

  <div class="theme-card p-6 space-y-6 rounded-2xl">
    <div class="flex items-center justify-between border-b border-current pb-3">
      <h3 class="theme-heading text-xs font-bold uppercase tracking-wide">STAGE TIMELINE NODES</h3>
      <span class="font-mono text-xs opacity-75">4 EVENTS RECORDED</span>
    </div>

    <div class="relative py-2 overflow-x-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 min-w-[750px]">
        <div class="bg-black/50 border border-current p-4 space-y-1 rounded-xl">
          <span class="text-[10px] font-mono font-bold text-cyan-400">Suspicious login attempt</span>
          <p class="text-[11px] opacity-80 font-mono">IP 203.145.12.89</p>
          <div class="mt-2 text-[9px] font-mono bg-cyan-950 text-cyan-300 border border-cyan-700 px-2 py-0.5 inline-block font-bold rounded-md">TIME: 14:27 WITA</div>
        </div>

        <div class="bg-black/50 border border-current p-4 space-y-1 rounded-xl">
          <span class="text-[10px] font-mono font-bold text-amber-300">Malware signature triggered</span>
          <p class="text-[11px] opacity-80 font-mono">Finance-DB-01</p>
          <div class="mt-2 text-[9px] font-mono bg-amber-950 text-amber-300 border border-amber-700 px-2 py-0.5 inline-block font-bold rounded-md">TIME: 14:27 WITA</div>
        </div>

        <div class="bg-black/50 border border-current p-4 space-y-1 rounded-xl">
          <span class="text-[10px] font-mono font-bold text-emerald-400">Lateral movement attempt</span>
          <p class="text-[11px] opacity-80 font-mono">Target Node /api/v1</p>
          <div class="mt-2 text-[9px] font-mono bg-emerald-950 text-emerald-300 border border-emerald-700 px-2 py-0.5 inline-block font-bold rounded-md">TIME: 14:28 WITA</div>
        </div>

        <div class="bg-black/50 border border-current p-4 space-y-1 rounded-xl">
          <span class="text-[10px] font-mono font-bold text-rose-400">Escalated to Critical</span>
          <p class="text-[11px] opacity-80 font-mono">Telegram Bot Triggered</p>
          <div class="mt-2 text-[9px] font-mono bg-rose-950 text-rose-300 border border-rose-700 px-2 py-0.5 inline-block font-bold rounded-md">TIME: 14:29 WITA</div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="theme-card p-5 space-y-4 rounded-2xl">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <h3 class="theme-heading text-xs font-bold uppercase">AI SUGGESTED ACTIONS</h3>
        <span class="text-[9px] font-mono px-2 py-0.5 bg-black/60 border border-current rounded-md">AUTO-BOT</span>
      </div>

      <div class="space-y-3">
        <div class="p-3 bg-black/40 border border-current flex flex-col gap-2 rounded-xl">
          <div>
            <h4 class="font-bold text-xs">Quarantine Finance-DB-01 Server</h4>
            <p class="text-[10px] opacity-70 font-mono">25 Sep 2026</p>
          </div>
          <div class="flex items-center gap-2 pt-1">
            <button onclick="window.openModal('Tindakan Quarantine Server ditolak.')" class="flex-1 py-1 bg-black/60 font-mono text-[9px] uppercase border border-current rounded-lg">REJECT</button>
            <button onclick="window.openModal('Tindakan Quarantine Server disetujui.')" class="flex-1 py-1 theme-button-primary text-[9px] uppercase font-bold rounded-lg">APPROVE</button>
          </div>
        </div>

        <div class="p-3 bg-black/40 border border-current flex flex-col gap-2 rounded-xl">
          <div>
            <h4 class="font-bold text-xs">Block IP 203.145.12.89 on Firewall</h4>
            <p class="text-[10px] opacity-70 font-mono">23 Sep 2026</p>
          </div>
          <div class="flex items-center gap-2 pt-1">
            <button onclick="window.openModal('Tindakan Block IP ditolak.')" class="flex-1 py-1 bg-black/60 font-mono text-[9px] uppercase border border-current rounded-lg">REJECT</button>
            <button onclick="window.openModal('Tindakan Block IP disetujui.')" class="flex-1 py-1 theme-button-primary text-[9px] uppercase font-bold rounded-lg">APPROVE</button>
          </div>
        </div>
      </div>
    </div>

    <div class="theme-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <h3 class="theme-heading text-xs font-bold uppercase">LOGIN ATTEMPTS</h3>
        <span class="font-mono text-sm font-bold text-amber-300">{{ $stats['total_events'] ?? 0 }}</span>
      </div>
      <div class="w-full h-48 relative">
        <canvas id="chartLoginAttempts"></canvas>
      </div>
    </div>

    <div class="theme-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <h3 class="theme-heading text-xs font-bold uppercase">THREAT DETECTION</h3>
        <span class="font-mono text-xs font-bold text-emerald-400">{{ $stats['blocked_count'] ?? 0 }} BLOCKED</span>
      </div>
      <div class="w-full h-48 relative">
        <canvas id="chartThreatDetection"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const latestBox = document.getElementById('overview-latest-telemetry');
    const listBody = document.getElementById('overview-telemetry-list');
    const refreshBtn = document.getElementById('overview-telemetry-refresh');

    if (!latestBox || !listBody) return;

    async function loadOverviewTelemetry() {
      try {
        const res = await fetch('/api/datalogs', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Gagal mengambil data telemetri.');

        const json = await res.json();
        const rows = json.data || [];

        if (rows.length === 0) {
          latestBox.textContent = 'Belum ada aktivitas inspeksi tercatat.';
          listBody.innerHTML = '';
          return;
        }

        const top = rows[0];
        const badgeClass = top.status === 'ALLOWED'
          ? 'bg-emerald-400 text-slate-950'
          : top.status === 'SUSPICIOUS'
            ? 'bg-amber-400 text-slate-950'
            : 'bg-rose-500 text-white';

        latestBox.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'flex flex-wrap items-center justify-between gap-2 mb-2';

        const badge = document.createElement('span');
        badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider ' + badgeClass;
        badge.textContent = `${top.status} • ${top.event_type}`;

        const meta = document.createElement('span');
        meta.className = 'font-mono text-[11px] text-cyan-400 font-semibold';
        meta.textContent = `Latency: ${top.detection_latency_ms ?? 0} ms | IP: ${top.ip_address} (${top.source})`;

        header.append(badge, meta);

        const detail = document.createElement('p');
        detail.className = 'font-mono text-[11px] opacity-90 break-all';
        detail.textContent = `Payload/Detail: ${top.payload_snippet || '-'}`;

        const action = document.createElement('p');
        action.className = 'text-[11px] opacity-80 mt-1';
        action.textContent = `Mitigasi: ${top.recommended_action || '-'}`;

        latestBox.append(header, detail, action);

        listBody.innerHTML = '';
        rows.slice(0, 5).forEach((row) => {
          const tr = document.createElement('tr');

          const cells = [
            { text: row.created_at ? new Date(row.created_at).toLocaleTimeString('id-ID') : '-', cls: 'py-2 pr-3 font-mono text-[11px] opacity-75' },
            { text: row.ip_address || '-', cls: 'py-2 pr-3 font-mono text-[11px] text-cyan-300' },
            { text: row.event_type || '-', cls: 'py-2 pr-3 font-mono text-[11px]' },
            { text: row.status || '-', cls: 'py-2 pr-3 font-mono text-[10px] font-bold ' + (row.status === 'BLOCKED' ? 'text-rose-400' : row.status === 'SUSPICIOUS' ? 'text-amber-300' : 'text-emerald-400') },
            { text: `${row.detection_latency_ms ?? 0} ms`, cls: 'py-2 text-right font-mono text-[11px] text-cyan-400' },
          ];

          cells.forEach((c) => {
            const td = document.createElement('td');
            td.className = c.cls;
            td.textContent = c.text;
            tr.appendChild(td);
          });

          listBody.appendChild(tr);
        });
      } catch (err) {
        latestBox.textContent = err.message;
      }
    }

    if (refreshBtn) refreshBtn.addEventListener('click', loadOverviewTelemetry);
    loadOverviewTelemetry();
  })();
</script>