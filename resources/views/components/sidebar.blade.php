<aside id="sidebar-nav" class="hidden lg:block w-full lg:w-72 flex-shrink-0 theme-card p-4 self-start">
  
  <div class="text-[10px] font-mono font-bold uppercase tracking-widest px-2 mb-3 pb-2 border-b border-current opacity-75">
    STAGE SELECT PANELS
  </div>
  
  <nav class="space-y-2">
    <button onclick="window.switchTab('tab-overview')" id="nav-tab-overview" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all theme-button-primary">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-border-all text-sm"></i>
        <span>STAGE 1: INCIDENT</span>
      </div>
      <i class="fa-solid fa-chevron-right text-xs"></i>
    </button>

    <button onclick="window.switchTab('tab-traffic')" id="nav-tab-traffic" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-chart-line text-sm text-amber-300"></i>
        <span>NETWORK HUD</span>
      </div>
      <span class="text-[9px] px-1.5 py-0.5 bg-black/60 font-mono rounded">OLAP</span>
    </button>

    <button onclick="window.switchTab('tab-credential')" id="nav-tab-credential" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-key text-sm text-emerald-400"></i>
        <span>CREDENTIAL KEY</span>
      </div>
      <span class="text-[9px] px-1.5 py-0.5 bg-black/60 font-mono rounded">OTP</span>
    </button>

    <button onclick="window.switchTab('tab-datalog')" id="nav-tab-datalog" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-terminal text-sm text-cyan-400"></i>
        <span>WAF STREAM</span>
      </div>
      <span class="text-[9px] px-1.5 py-0.5 bg-cyan-950 text-cyan-300 font-mono font-bold border border-cyan-800 rounded">LIVE</span>
    </button>

    <button onclick="window.switchTab('tab-blocked')" id="nav-tab-blocked" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-shield-cat text-sm text-rose-400"></i>
        <span>BLOCK MITIGATION</span>
      </div>
      <span class="text-[9px] px-1.5 py-0.5 bg-rose-950 text-rose-300 font-mono font-bold border border-rose-800 rounded">CSIRT</span>
    </button>
  </nav>

  <div class="mt-6 pt-4 border-t border-current space-y-3">
    <div class="flex items-center justify-between px-1">
      <div class="font-mono text-[10px] uppercase tracking-wider font-bold text-amber-300">
        DESAIN &amp; STRUCTURAL THEMES
      </div>
      <i class="fa-solid fa-palette text-xs"></i>
    </div>

    <div class="bg-black/50 p-2.5 border border-current rounded-xl space-y-2">
      <button onclick="window.setTheme('clean')" id="btn-theme-clean" class="w-full flex items-center justify-between p-2.5 border border-sky-400 bg-white text-slate-900 text-xs font-mono font-bold hover:bg-sky-50 rounded-xl transition-all">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 bg-[#0066D6] rounded-full inline-block"></span>
          <span>Gov Light Standard</span>
        </div>
        <span class="text-[9px] bg-sky-100 text-[#0066D6] px-1.5 py-0.5 font-bold rounded-md">PALETTE BLUE</span>
      </button>

      <button onclick="window.setTheme('soc')" id="btn-theme-soc" class="w-full flex items-center justify-between p-2.5 border border-slate-700 bg-slate-900 text-slate-300 text-xs font-mono font-bold hover:bg-slate-800 rounded-xl transition-all">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 bg-cyan-300 rounded-full inline-block"></span>
          <span>Modern SOC</span>
        </div>
        <span class="text-[9px] bg-cyan-950 text-cyan-300 px-2 py-0.5 rounded-full border border-cyan-800">SLEEK</span>
      </button>

      <button onclick="window.setTheme('retro')" id="btn-theme-retro" class="w-full flex items-center justify-between p-2.5 border border-arcade-cyan bg-arcade-surface text-white text-xs font-mono font-bold hover:bg-slate-800 rounded-xl transition-all">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 bg-arcade-yellow border border-black inline-block rounded-xs"></span>
          <span>Retro Arcade</span>
        </div>
        <span class="font-pixel text-[8px] bg-arcade-cyan text-black px-1.5 py-0.5 font-bold rounded-xs">BRUTAL</span>
      </button>
    </div>
  </div>

  <div class="mt-4 pt-3 border-t border-current space-y-2">
    <div class="bg-black/40 p-3 border border-current font-mono text-xs space-y-1.5 rounded-xl">
      <div class="flex justify-between items-center opacity-80">
        <span>RULE ENGINE:</span>
        <span class="font-bold text-amber-300">WAF v2.4</span>
      </div>
      <div class="flex justify-between items-center opacity-80">
        <span>OLAP CUBE:</span>
        <span class="font-bold text-emerald-400">READY</span>
      </div>
    </div>
  </div>

</aside>