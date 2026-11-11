<div id="cred-modal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white text-slate-900 border-2 border-sky-400 max-w-2xl w-full rounded-2xl shadow-2xl overflow-hidden relative my-6">
    
    <!-- Header Modal -->
    <div class="bg-[#0066D6] text-white px-6 py-3 flex items-center justify-between border-b-2 border-sky-300">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-address-card text-xl text-sky-200"></i>
        <div>
          <h3 id="cred-modal-title" class="font-bold text-sm tracking-wider uppercase">SPBE DISKOMINFO - ID CARD PEGAWAI</h3>
          <p class="text-[10px] text-sky-100 font-mono">Identity &amp; Credential Authentication System</p>
        </div>
      </div>
      <button type="button" onclick="window.closeCredentialModal()" class="text-white hover:text-rose-200 text-lg transition-all p-1">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Form Body -->
    <form id="form-credential" onsubmit="window.saveCredentialForm(event)" class="p-6 space-y-5">
      <input type="hidden" id="cred-edit-index" value="-1">

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Upload Foto Identitas -->
        <div class="md:col-span-4 flex flex-col items-center justify-center bg-slate-50 border-2 border-dashed border-sky-300 p-4 rounded-2xl text-center relative group">
          <div class="w-32 h-40 bg-slate-200 rounded-xl overflow-hidden border border-slate-300 shadow-inner flex items-center justify-center relative mb-3">
            <img id="photo-preview" src="https://placehold.co/120x150/0066D6/ffffff?text=FOTO+ID" alt="Foto Identitas" class="w-full h-full object-cover">
          </div>

          <label for="cred-photo-input" class="px-3 py-1.5 bg-sky-100 text-[#0066D6] hover:bg-[#0066D6] hover:text-white font-bold text-xs rounded-xl cursor-pointer transition-all border border-sky-300 flex items-center gap-2">
            <i class="fa-solid fa-camera"></i>
            <span>Upload Foto</span>
          </label>
          <input type="file" id="cred-photo-input" accept="image/*" onchange="window.previewPhotoUpload(event)" class="hidden">
          <span class="text-[9px] text-slate-400 mt-2 font-mono">PNG / JPG (Maks 2MB)</span>
        </div>

        <!-- Input Fields -->
        <div class="md:col-span-8 space-y-3.5 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap &amp; Gelar</label>
            <input type="text" id="cred-nama" required placeholder="Contoh: Andi Saputra, ST" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 text-slate-900 font-medium">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">NIP (Nomor Induk Pegawai)</label>
              <input type="text" id="cred-nip" required placeholder="16 Digit NIP" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 text-slate-900 font-mono">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Instansi / SKPD</label>
              <input type="text" id="cred-instansi" required placeholder="Dinas / SKPD" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 text-slate-900">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tempat &amp; Tanggal Lahir</label>
              <input type="text" id="cred-ttl" required placeholder="Banjarmasin, 29 Mar 1991" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 text-slate-900">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor Telepon (WhatsApp)</label>
              <input type="text" id="cred-telp" required placeholder="0812-XXXX-XXXX" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 text-slate-900 font-mono">
            </div>
          </div>

          <!-- Secret Key Generator -->
          <div class="pt-1">
            <label class="block font-bold text-slate-700 mb-1">Secret Key Otentikasi (Luar Kantor)</label>
            <div class="flex items-center gap-2">
              <input type="text" id="cred-secret" readonly required placeholder="SK-KALSEL-XXXX" class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-xl focus:outline-none text-slate-900 font-mono font-bold text-sky-700">
              <button type="button" onclick="window.generateSecretKey()" class="px-3 py-2 bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold text-xs rounded-xl whitespace-nowrap flex items-center gap-1.5 shadow transition-all hover:scale-105">
                <i class="fa-solid fa-key"></i>
                <span>Generate Key</span>
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- Controls -->
      <div class="border-t border-slate-200 pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-1 opacity-60 font-mono text-slate-700 text-xs tracking-widest">
          <span>||| | |||| | ||| |||| |</span>
          <span class="text-[9px] font-bold">SPBE-AUTH-ID</span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
          <button type="button" onclick="window.closeCredentialModal()" class="flex-1 sm:flex-none px-4 py-2 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-xl font-bold text-xs transition-all">
            Batal
          </button>
          <button type="submit" class="flex-1 sm:flex-none px-6 py-2 bg-[#0066D6] text-white hover:bg-sky-600 rounded-xl font-bold text-xs shadow-md transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Simpan Kredensial</span>
          </button>
        </div>
      </div>

    </form>
  </div>
</div>