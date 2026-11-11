<div id="tab-settings" class="page-view space-y-6">
  
  <!-- Header Banner -->
  <div class="theme-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-2xl">
    <div>
      <h2 class="theme-heading text-sm font-bold uppercase">
        SETTINGS
      </h2>
      {{-- <p class="text-xs opacity-80 mt-1 font-mono">Kelola Tema Visual Dashboard dan Bahasa Pengantar Sistem</p> --}}
    </div>
    <span class="font-mono text-[9px] px-3 py-1.5 bg-black/60 border border-current">SYSTEM CONFIG v1.0</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- CARD 1: PENGATURAN TEMA VISUAL -->
    <div class="theme-card p-6 space-y-5 rounded-2xl">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-palette text-amber-300 text-sm"></i>
          <h3 class="theme-heading text-xs font-bold uppercase">TEMA STRUCTURAL & VISUAL</h3>
        </div>
        <span class="font-mono text-[10px] opacity-75">3 PRESETS</span>
      </div>

      <div class="space-y-3">
        
        <!-- Option: Executive Light Government -->
        <label onclick="window.setTheme('clean')" class="flex items-center justify-between p-4 border border-current rounded-xl cursor-pointer bg-black/20 hover:bg-black/40 transition-all">
          <div class="flex items-center gap-3">
            <input type="radio" name="theme_option" id="radio-theme-clean" value="clean" class="accent-sky-500">
            <div>
              <div class="font-bold text-xs font-sans">Executive Light Government</div>
              {{-- <div class="text-[10px] opacity-70 font-mono mt-0.5">Palet warna Soft Sky Blue & Cyan (Standard SPBE)</div> --}}
            </div>
          </div>
          <span class="w-4 h-4 rounded-full bg-[#0066D6] border border-white"></span>
        </label>

        <!-- Option: Modern Enterprise SOC -->
        <label onclick="window.setTheme('soc')" class="flex items-center justify-between p-4 border border-current rounded-xl cursor-pointer bg-black/20 hover:bg-black/40 transition-all">
          <div class="flex items-center gap-3">
            <input type="radio" name="theme_option" id="radio-theme-soc" value="soc" class="accent-cyan-400">
            <div>
              <div class="font-bold text-xs font-sans">Modern Enterprise SOC</div>
              {{-- <div class="text-[10px] opacity-70 font-mono mt-0.5">Mode Gelap Minimalis Cyber (Dark Slate & Neon Cyan)</div> --}}
            </div>
          </div>
          <span class="w-4 h-4 rounded-full bg-[#7AD7F0] border border-black"></span>
        </label>

        <!-- Option: Retro Arcade -->
        <label onclick="window.setTheme('retro')" class="flex items-center justify-between p-4 border border-current rounded-xl cursor-pointer bg-black/20 hover:bg-black/40 transition-all">
          <div class="flex items-center gap-3">
            <input type="radio" name="theme_option" id="radio-theme-retro" value="retro" class="accent-yellow-400">
            <div>
              <div class="font-bold text-xs font-sans">Neo-Brutalism</div>
              {{-- <div class="text-[10px] opacity-70 font-mono mt-0.5">Tampilan Kontras Tinggi, Border Tebal & Pixel Typography</div> --}}
            </div>
          </div>
          <span class="w-4 h-4 rounded-xs bg-[#FDF833] border border-black"></span>
        </label>

      </div>
    </div>

    <!-- CARD 2: PENGATURAN BAHASA -->
    <div class="theme-card p-6 space-y-5 rounded-2xl">
      <div class="flex items-center justify-between border-b border-current pb-3">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-language text-cyan-400 text-sm"></i>
          <h3 class="theme-heading text-xs font-bold uppercase">BAHASA (LANGUAGE)</h3>
        </div>
        <span class="font-mono text-[10px] opacity-75">LOCALE</span>
      </div>

      <div class="space-y-3">
        
        <!-- Option: Bahasa Indonesia -->
        <label onclick="window.setLanguage('id')" class="flex items-center justify-between p-4 border border-current rounded-xl cursor-pointer bg-black/20 hover:bg-black/40 transition-all">
          <div class="flex items-center gap-3">
            <input type="radio" name="language_option" id="radio-lang-id" value="id" checked class="accent-emerald-400">
            <div>
              <div class="font-bold text-xs font-sans">Bahasa Indonesia</div>
              <div class="text-[10px] opacity-70 font-mono mt-0.5">Bahasa baku operasional SPBE Diskominfo Kalsel</div>
            </div>
          </div>
          <span class="text-xs font-mono font-bold px-2 py-0.5 bg-rose-950 text-rose-300 border border-rose-800 rounded">ID</span>
        </label>

        <!-- Option: English -->
        <label onclick="window.setLanguage('en')" class="flex items-center justify-between p-4 border border-current rounded-xl cursor-pointer bg-black/20 hover:bg-black/40 transition-all">
          <div class="flex items-center gap-3">
            <input type="radio" name="language_option" id="radio-lang-en" value="en" class="accent-emerald-400">
            <div>
              <div class="font-bold text-xs font-sans">English (United States)</div>
              <div class="text-[10px] opacity-70 font-mono mt-0.5">Standard international technical security telemetry</div>
            </div>
          </div>
          <span class="text-xs font-mono font-bold px-2 py-0.5 bg-sky-950 text-sky-300 border border-sky-800 rounded">EN</span>
        </label>

      </div>
    </div>

  </div>

</div>