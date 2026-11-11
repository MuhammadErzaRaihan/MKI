<div id="tab-credential" class="page-view space-y-6">
  <div class="theme-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-2xl">
    <div>
      <h2 class="theme-heading text-sm font-bold uppercase">CREDENTIAL &amp; IP LOCATION RULES</h2>
      <p class="text-xs opacity-80 mt-1 font-mono">Audit &amp; Kelola Otentikasi Pegawai SPBE Diskominfo Kalsel</p>
    </div>
    <button onclick="window.openCredentialModal()" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2 shadow-md hover:scale-[1.03]">
      <i class="fa-solid fa-user-plus"></i>
      <span>TAMBAH KREDENSIAL PEGAWAI</span>
    </button>
  </div>

  <div class="p-4 bg-black/60 border-2 border-amber-300 flex items-start gap-4 rounded-2xl">
    <div class="w-10 h-10 bg-amber-300 text-black border border-black flex items-center justify-center font-bold text-lg flex-shrink-0 rounded-xl">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div class="text-xs leading-relaxed font-mono">
      <span class="font-bold text-amber-300 uppercase">RULE VERIFIKASI KEAMANAN:</span>
      <p class="mt-1">
        - Dalam <strong class="text-emerald-400 font-bold">Lingkup IP Kantor</strong> &rarr; Verifikasi <strong class="text-emerald-400 font-bold">Kode OTP</strong>.<br>
        - Terdeteksi di <strong class="text-cyan-400 font-bold">Luar Kantor (IP Tidak Sama)</strong> &rarr; Wajib menggunakan <strong class="text-cyan-400 font-bold">Secret Key Spesifik</strong>.
      </p>
    </div>
  </div>

  <div class="theme-card p-5 space-y-4 rounded-2xl">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-current pb-3">
      <h3 class="theme-heading text-xs font-bold uppercase">STATUS AUDIT KREDENSIAL PEGAWAI</h3>
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <div class="relative w-full sm:w-64">
          <input type="text" id="credential-search" oninput="window.searchCredentials()" placeholder="Cari NIP atau Nama..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-black/40 border border-current rounded-xl focus:outline-none focus:ring-1 focus:ring-sky-400">
          <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-xs opacity-60"></i>
        </div>
        <span id="cred-count-badge" class="text-xs font-mono opacity-80 whitespace-nowrap">0 RECORD FOUND</span>
      </div>
    </div>

    <div class="overflow-x-auto border border-current bg-black/40 rounded-xl">
      <table class="w-full text-left text-xs">
        <thead class="bg-black/80 font-mono text-[9px] uppercase border-b border-current">
          <tr>
            <th class="p-3">IDENTITAS</th>
            <th class="p-3">NAMA PEGAWAI &amp; NIP</th>
            <th class="p-3">TTL &amp; NO TELEPON</th>
            <th class="p-3">INSTANSI</th>
            <th class="p-3 text-center">STATUS</th>
            <th class="p-3">SECRET KEY / VERIFIKASI</th>
            <th class="p-3 text-right">AKSI (CRUD)</th>
          </tr>
        </thead>
        <tbody id="credential-table-body" class="divide-y divide-slate-800 font-mono">
        </tbody>
      </table>
    </div>
  </div>
</div>