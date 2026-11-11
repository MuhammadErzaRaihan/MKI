<div id="tab-blocked" class="page-view space-y-6">
  <div class="theme-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-2xl">
    <div>
      <h2 class="theme-heading text-sm font-bold uppercase">BLOCKED STATUS &amp; MITIGASI PENCEGAHAN</h2>
      <p class="text-xs opacity-80 mt-1 font-mono">Manajemen Isolasi IP Terdeteksi Anomalous (Unblock / Blockfull)</p>
    </div>
    <span class="font-mono text-[9px] px-3 py-1.5 bg-black/60 border border-current">CSIRT NODE ACTIVE</span>
  </div>

  <div class="theme-card p-5 space-y-4 rounded-2xl">
    <div class="overflow-x-auto border border-current bg-black/40 rounded-xl">
      <table class="w-full text-left text-xs">
        <thead class="bg-black/80 font-mono text-[9px] uppercase border-b border-current">
          <tr>
            <th class="p-3">ALAMAT IP</th>
            <th class="p-3">ALASAN PEMBLOKIRAN</th>
            <th class="p-3">DEPARTEMEN / INSTANSI</th>
            <th class="p-3">STAMP WAKTU</th>
            <th class="p-3 text-center">RISK LEVEL</th>
            <th class="p-3 text-right">AKSI MITIGASI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800 font-mono text-slate-200">
          <tr class="hover:bg-slate-900/60 transition-colors">
            <td class="p-3 font-bold text-rose-400">203.145.12.89</td>
            <td class="p-3 font-sans font-semibold">SQL Injection Regex Match (' OR '1'='1)</td>
            <td class="p-3 font-sans opacity-80">External ISP Node</td>
            <td class="p-3 opacity-75">2026-11-07 14:27:32</td>
            <td class="p-3 text-center font-sans">
              <span class="px-2.5 py-0.5 rounded text-[9px] font-bold bg-rose-950 text-rose-300 border border-rose-800">CRITICAL</span>
            </td>
            <td class="p-3 text-right font-sans">
              <div class="flex items-center justify-end gap-2">
                <button onclick="window.openModal('Unblock IP 203.145.12.89')" class="px-3 py-1 rounded-lg bg-emerald-950 text-emerald-300 border border-emerald-800 hover:bg-emerald-900 font-bold transition-all text-xs">Unblock</button>
                <button onclick="window.openModal('Blockfull IP 203.145.12.89')" class="px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-700 font-bold transition-all text-xs shadow-sm">Blockfull</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>