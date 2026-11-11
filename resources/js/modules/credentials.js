let credentialsData = [
  {
    nip: '6371012903910001',
    nama: 'Andi Saputra, ST',
    instansi: 'Diskominfo (HRD)',
    ttl: 'Banjarmasin, 29 Mar 1991',
    telp: '0812-5551-0192',
    status: 'ON',
    ipDetected: '192.168.10.15 (IP Kantor)',
    secretKey: 'SK-KALSEL-849',
    photo: 'https://placehold.co/120x150/0066D6/ffffff?text=Andi'
  },
  {
    nip: '6371021506880003',
    nama: 'Budi Santoso, S.Kom',
    instansi: 'BPKAD (Keuangan)',
    ttl: 'Banjarbaru, 15 Jun 1988',
    telp: '0813-9988-2104',
    status: 'ON',
    ipDetected: '180.252.88.12 (Luar Kantor)',
    secretKey: 'SK-KALSEL-998',
    photo: 'https://placehold.co/120x150/0284C7/ffffff?text=Budi'
  }
];

let currentPhotoBase64 = null;

export function renderCredentials(filterQuery = '') {
  const tbody = document.getElementById('credential-table-body');
  const badge = document.getElementById('cred-count-badge');
  if (!tbody) return;

  tbody.innerHTML = '';

  const filtered = credentialsData.filter(item => 
    item.nama.toLowerCase().includes(filterQuery.toLowerCase()) || 
    item.nip.includes(filterQuery) ||
    item.instansi.toLowerCase().includes(filterQuery.toLowerCase())
  );

  if (badge) badge.innerText = `${filtered.length} RECORD FOUND`;

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-slate-400 font-sans">Tidak ada data pegawai ditemukan.</td></tr>`;
    return;
  }

  filtered.forEach((item) => {
    const originalIndex = credentialsData.indexOf(item);
    const isOfficeIP = item.ipDetected.includes('IP Kantor');

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-900/60 transition-colors';
    tr.innerHTML = `
      <td class="p-3">
        <div class="w-9 h-11 bg-slate-800 rounded-lg overflow-hidden border border-current shadow-sm flex items-center justify-center">
          <img src="${item.photo}" alt="${item.nama}" class="w-full h-full object-cover">
        </div>
      </td>
      <td class="p-3">
        <div class="font-bold font-sans text-sm text-slate-100">${item.nama}</div>
        <div class="text-[10px] opacity-75 font-mono text-slate-200">${item.nip}</div>
      </td>
      <td class="p-3 font-mono text-[11px]">
        <div>${item.ttl}</div>
        <div class="text-sky-400 font-bold mt-0.5"><i class="fa-solid fa-phone text-[9px] mr-1"></i>${item.telp}</div>
      </td>
      <td class="p-3 font-sans opacity-80">${item.instansi}</td>
      <td class="p-3 text-center">
        <span class="px-2 py-0.5 text-[8px] ${item.status === 'ON' ? 'bg-emerald-400 text-black' : 'bg-amber-400 text-black'} font-bold rounded">
          ${item.status}
        </span>
      </td>
      <td class="p-3">
        <div class="text-[11px] font-bold ${isOfficeIP ? 'text-emerald-400' : 'text-cyan-400'}">
          ${item.ipDetected}
        </div>
        <div class="mt-1">
          <span class="px-2 py-0.5 bg-black/60 border border-current rounded text-[10px] font-bold text-amber-300">
            <i class="fa-solid fa-key text-[9px] mr-1"></i>${item.secretKey}
          </span>
        </div>
      </td>
      <td class="p-3 text-right">
        <div class="flex items-center justify-end gap-1.5">
          <button onclick="window.editCredential(${originalIndex})" class="p-1.5 bg-sky-600 text-white rounded-lg" title="Edit Pegawai">
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>
          <button onclick="window.deleteCredential(${originalIndex})" class="p-1.5 bg-rose-600 text-white rounded-lg" title="Hapus Pegawai">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

export function searchCredentials() {
  const q = document.getElementById('credential-search')?.value || '';
  renderCredentials(q);
}

export function openCredentialModal(editIndex = -1) {
  const modal = document.getElementById('cred-modal');
  const form = document.getElementById('form-credential');
  const title = document.getElementById('cred-modal-title');
  const preview = document.getElementById('photo-preview');
  const editInput = document.getElementById('cred-edit-index');

  if (!modal || !form) return;

  editInput.value = editIndex;

  if (editIndex >= 0 && credentialsData[editIndex]) {
    const item = credentialsData[editIndex];
    title.innerText = "EDIT KREDENSIAL PEGAWAI (ID CARD)";
    document.getElementById('cred-nama').value = item.nama;
    document.getElementById('cred-nip').value = item.nip;
    document.getElementById('cred-instansi').value = item.instansi;
    document.getElementById('cred-ttl').value = item.ttl;
    document.getElementById('cred-telp').value = item.telp;
    document.getElementById('cred-secret').value = item.secretKey;
    preview.src = item.photo;
    currentPhotoBase64 = item.photo;
  } else {
    title.innerText = "SPBE DISKOMINFO - ID CARD PEGAWAI BARU";
    form.reset();
    preview.src = "https://placehold.co/120x150/0066D6/ffffff?text=FOTO+ID";
    currentPhotoBase64 = "https://placehold.co/120x150/0066D6/ffffff?text=FOTO+ID";
    generateSecretKey();
  }

  modal.classList.remove('hidden');
}

export function closeCredentialModal() {
  const modal = document.getElementById('cred-modal');
  if (modal) modal.classList.add('hidden');
}

export function generateSecretKey() {
  const randomNum = Math.floor(100 + Math.random() * 900);
  const secretInput = document.getElementById('cred-secret');
  if (secretInput) secretInput.value = `SK-KALSEL-${randomNum}`;
}

export function editCredential(index) {
  openCredentialModal(index);
}

export function deleteCredential(index) {
  const item = credentialsData[index];
  if (!item) return;

  if (confirm(`Apakah Anda yakin ingin menghapus kredensial ${item.nama}?`)) {
    credentialsData.splice(index, 1);
    renderCredentials();
  }
}