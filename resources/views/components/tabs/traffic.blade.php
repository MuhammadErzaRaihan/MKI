<div id="tab-traffic" class="page-view space-y-6">
  <div class="theme-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <h2 class="theme-heading text-sm font-bold uppercase">NETWORK TELEMETRY HUD</h2>
      <p class="text-xs opacity-80 mt-1">Pemantauan Akses Perangkat Pengguna SPBE 2021 - 2026</p>
    </div>
    <span class="font-mono text-[9px] px-3 py-1.5 bg-black/60 border border-current">RANGE: 2021 - 2026</span>
  </div>

  <div class="theme-card p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4 text-xs border-b border-current pb-3">
      <div class="flex items-center gap-6 font-mono text-[10px]">
        <div class="flex items-center gap-2">
          <span class="w-3.5 h-3.5 bg-cyan-400 inline-block border border-black"></span>
          <span>Mobile Traffic</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3.5 h-3.5 bg-amber-300 inline-block border border-black"></span>
          <span>Desktop Traffic</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3.5 h-3.5 bg-emerald-400 inline-block border border-black"></span>
          <span>Tablet Traffic</span>
        </div>
      </div>
      <span class="opacity-70 font-mono text-xs">OLAP Data Cube Synced</span>
    </div>

    <div class="w-full h-[400px] relative">
      <canvas id="chartTrafficFull"></canvas>
    </div>
  </div>
</div>