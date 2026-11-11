<aside id="sidebar-nav" class="hidden lg:block w-full lg:w-72 flex-shrink-0 theme-card p-4 self-start">
  
  <div class="text-[10px] font-mono font-bold uppercase tracking-widest px-2 mb-3 pb-2 border-b border-current opacity-75">
    STAGE SELECT PANELS
  </div>
  
  <nav class="space-y-2">
    <button onclick="window.switchTab('tab-overview')" id="nav-tab-overview" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-md theme-button-primary">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-border-all text-sm"></i>
        <span>MAIN DASHBOARD</span>
      </div>
      <i class="fa-solid fa-chevron-right text-xs"></i>
    </button>

    <button onclick="window.switchTab('tab-traffic')" id="nav-tab-traffic" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40 hover:-translate-y-0.5 hover:shadow-md">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-chart-line text-sm text-amber-300"></i>
        <span>NETWORK HUD</span>
      </div>
    </button>

    <button onclick="window.switchTab('tab-credential')" id="nav-tab-credential" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40 hover:-translate-y-0.5 hover:shadow-md">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-key text-sm text-emerald-400"></i>
        <span>CREDENTIAL KEY</span>
      </div>
    </button>

    <button onclick="window.switchTab('tab-datalog')" id="nav-tab-datalog" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40 hover:-translate-y-0.5 hover:shadow-md">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-terminal text-sm text-cyan-400"></i>
        <span>WAF STREAM</span>
      </div>
    </button>

    <button onclick="window.switchTab('tab-blocked')" id="nav-tab-blocked" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40 hover:-translate-y-0.5 hover:shadow-md">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-shield-cat text-sm text-rose-400"></i>
        <span>BLOCK MITIGATION</span>
      </div>
    </button>

    <!-- TAB DEDICATED SETTINGS & PREFERENCES -->
    <button onclick="window.switchTab('tab-settings')" id="nav-tab-settings" class="w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40 hover:-translate-y-0.5 hover:shadow-md">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-gear text-sm text-slate-300"></i>
        <span>SETTINGS</span>
      </div>
    </button>
  </nav>

</aside>