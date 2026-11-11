<header class="theme-header sticky top-0 z-40 transition-colors">
  <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
    
    <div class="flex items-center gap-3">
      <button id="btn-mobile-menu" onclick="window.toggleMobileSidebar()" class="lg:hidden p-2 text-arcade-cyan hover:text-white hover:scale-110 focus:outline-none transition-transform" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars text-xl"></i>
      </button>
      
      <div id="header-logo-icon" class="w-11 h-11 bg-arcade-yellow text-black border-2 border-black flex items-center justify-center font-bold text-xl rounded-xl shadow">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      
      <div>
        <div class="flex items-center gap-2">
          <h1 id="header-title" class="theme-heading text-xs sm:text-sm font-extrabold tracking-wider uppercase">Monitor Sistem pemerintahan berbasis elektronik</h1>
          <span id="header-badge" class="theme-badge-highlight text-[9px] px-2 py-0.5 uppercase font-bold">DISKOMINFO</span>
        </div>
      </div>
    </div>

    <!-- PERSONA 4 WEATHER, DATE & TIME WIDGET -->
    <div class="hidden md:flex items-center gap-3">
    
    <!-- Kapsul Widget dengan Class Proteksi hud-capsule -->
    <div class="hud-capsule flex items-center gap-2.5 bg-black/90 text-white px-4 py-1.5 rounded-full border border-slate-700 shadow-md font-mono">
        <!-- Time formatted in AM/PM -->
        <span id="hud-clock-time" class="font-extrabold text-sm tracking-wider text-amber-300">10:30 AM</span>
        <span class="opacity-30 text-xs">|</span>
        <!-- Date MM/DD -->
        <span id="hud-clock-date" class="font-bold text-xs text-slate-200">11/09</span>
        <!-- Day of the Week Badge -->
        <span id="hud-clock-day" class="font-extrabold text-[10px] text-sky-400 bg-sky-950/90 px-2 py-0.5 rounded-md border border-sky-700 uppercase tracking-wider">MON</span>
    </div>

    <div class="flex items-center gap-2.5 bg-black/40 px-3.5 py-1 border border-current rounded-full">
        <div id="hud-weather-icon" class="w-7 h-7 rounded-full bg-amber-400 text-black flex items-center justify-center text-xs font-bold shadow-sm">
        <i class="fa-solid fa-sun"></i>
        </div>
        <div class="flex flex-col justify-center">
        <span id="hud-time-period" class="font-brutal font-extrabold text-xs leading-none tracking-wide text-amber-300">Evening</span>
        <span id="hud-weather-desc" class="text-[9px] font-mono opacity-80 leading-tight mt-0.5">Cerah (Banjarmasin)</span>
        </div>
    </div>

    </div>

    <!-- USER PROFILE BADGE -->
    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2.5">
        <div class="text-right hidden sm:block">
          <div class="font-bold text-[11px] uppercase tracking-wider">Jams Walter</div>
          <div class="text-[10px] font-mono opacity-70">@jamswalter2345</div>
        </div>
        <div class="w-10 h-10 bg-amber-400 text-black border-2 border-black flex items-center justify-center font-bold text-base rounded-full shadow hover:scale-105 transition-transform">
          <i class="fa-solid fa-user-ninja"></i>
        </div>
      </div>
    </div>

  </div>
</header>