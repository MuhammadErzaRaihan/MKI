<div id="tab-overview" class="page-view active space-y-6">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 theme-card p-5">
    <div>
      <div class="flex items-center gap-3">
        <h2 class="theme-heading text-lg font-extrabold uppercase tracking-wide text-amber-300">
          INCIDENT #INC-2048
        </h2>
        <span class="text-[10px] font-bold px-2.5 py-1 bg-rose-600 text-white uppercase animate-pulse">CRITICAL ANOMALY</span>
      </div>
      <p class="text-xs opacity-80 mt-1 font-mono">Multi-Layer Threat Stream Analysis : Central SPBE Node</p>
    </div>

    <button onclick="window.openModal('Memuat laporan insiden #INC-2048 format PDF...')" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      <span>EXPORT REPORT</span>
    </button>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">AFFECTED ASSET</div>
        <div class="font-brutal font-black text-xl mt-1">Finance-DB-01</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl">
        <i class="fa-solid fa-server"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">MITRE TECHNIQUE</div>
        <div class="font-brutal font-black text-xl mt-1">T1078</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl">
        <i class="fa-solid fa-crosshairs"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">SOURCE IP</div>
        <div class="font-brutal font-black text-xl mt-1">203.145.12.89</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl">
        <i class="fa-solid fa-network-wired"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-70">CONFIDENCE</div>
        <div class="font-brutal font-black text-xl mt-1">92%</div>
      </div>
      <div class="w-10 h-10 bg-black/40 border border-current flex items-center justify-center font-bold text-lg rounded-xl">
        <i class="fa-solid fa-bullseye"></i>
      </div>
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
        <span class="font-mono text-sm font-bold text-amber-300">76</span>
      </div>
      <div class="w-full h-48 relative">
        <canvas id="chartLoginAttempts"></canvas>
      </div>
    </div>

    <div class="theme-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <h3 class="theme-heading text-xs font-bold uppercase">THREAT DETECTION</h3>
        <span class="font-mono text-xs font-bold text-emerald-400">+40%</span>
      </div>
      <div class="w-full h-48 relative">
        <canvas id="chartThreatDetection"></canvas>
      </div>
    </div>
  </div>
</div>