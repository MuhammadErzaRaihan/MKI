let credentialsData = [];
let currentPhotoBase64 = null;

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export async function fetchCredentials(filterQuery = '') {
  const tbody = document.getElementById('credential-table-body');
  const badge = document.getElementById('cred-count-badge');
  if (!tbody) return;

  tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center opacity-70 font-sans">Memuat data kredensial pegawai...</td></tr>`;

  try {
    const url = filterQuery
      ? `/api/credentials?q=${encodeURIComponent(filterQuery)}`
      : '/api/credentials';

    const response = await fetch(url, {
      headers: { 'Accept': 'application/json' }
    });

    if (!response.ok) throw new Error('Gagal memuat data kredensial.');

    const payload = await response.json();
    credentialsData = payload.data || [];

    if (badge) badge.innerText = `${payload.total ?? credentialsData.length} RECORD FOUND`;
    renderCredentialsTable();
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-rose-400 font-sans">${error.message}</td></tr>`;
  }
}

export function renderCredentials(filterQuery = '') {
  fetchCredentials(filterQuery);
}

function renderCredentialsTable() {
  const tbody = document.getElementById('credential-table-body');
  if (!tbody) return;

  tbody.innerHTML = '';

  if (credentialsData.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center opacity-70 font-sans">Belum ada data pegawai tersimpan. Klik "Tambah Kredensial Pegawai" untuk menambahkan.</td></tr>`;
    return;
  }

  credentialsData.forEach((item) => {
    const isOfficeIP = Boolean(item.is_office_ip);

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-900/60 transition-colors';
    tr.innerHTML = `
      <td class="p-3">
        <div class="w-9 h-11 bg-slate-800 rounded-lg overflow-hidden border border-current shadow-sm flex items-center justify-center">
          <img src="${item.photo_url}" alt="${item.nama}" width="36" height="44" class="w-full h-full object-cover">
        </div>
      </td>
      <td class="p-3">
        <div class="font-bold font-sans text-sm">${item.nama}</div>
        <div class="text-[10px] opacity-75 font-mono">${item.nip}</div>
        <div class="text-[10px] text-sky-400 font-mono">${item.email || '-'}</div>
      </td>
      <td class="p-3 font-mono text-[11px]">
        <div>${item.ttl || '-'}</div>
        <div class="text-sky-400 font-bold mt-0.5"><i class="fa-solid fa-phone text-[9px] mr-1"></i>${item.telp || '-'}</div>
      </td>
      <td class="p-3 font-sans opacity-80">${item.instansi}</td>
      <td class="p-3 text-center">
        <span class="px-2 py-0.5 text-[8px] ${item.status === 'ON' ? 'bg-emerald-400 text-black' : 'bg-amber-400 text-black'} font-bold rounded">
          ${item.status}
        </span>
      </td>
      <td class="p-3">
        <div class="text-[11px] font-bold ${isOfficeIP ? 'text-emerald-400' : 'text-cyan-400'}">
          ${item.ip_label}
        </div>
        <div class="mt-1">
          <span class="px-2 py-0.5 bg-black/60 border border-current rounded text-[10px] font-bold text-amber-300">
            <i class="fa-solid fa-key text-[9px] mr-1"></i>${item.secret_key}
          </span>
        </div>
      </td>
      <td class="p-3 text-right">
        <div class="flex items-center justify-end gap-1.5">
          <button type="button" onclick="window.editCredential(${item.id})" class="p-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-lg transition-all hover:scale-105" title="Edit Pegawai">
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>
          <button type="button" onclick="window.deleteCredential(${item.id})" class="p-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg transition-all hover:scale-105" title="Hapus Pegawai">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

let searchDebounceTimer = null;
export function searchCredentials() {
  const input = document.getElementById('credential-search');
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    fetchCredentials(input ? input.value.trim() : '');
  }, 250);
}

export function openCredentialModal(credentialId = -1) {
  const modal = document.getElementById('cred-modal');
  const form = document.getElementById('form-credential');
  const title = document.getElementById('cred-modal-title');
  const preview = document.getElementById('photo-preview');
  const editInput = document.getElementById('cred-edit-index');

  if (!modal || !form) return;

  editInput.value = credentialId;
  currentPhotoBase64 = null;

  const existing = credentialsData.find((c) => c.id === credentialId);

  if (existing) {
    if (title) title.innerText = 'EDIT KREDENSIAL PEGAWAI (ID CARD)';
    document.getElementById('cred-nama').value = existing.nama;
    document.getElementById('cred-nip').value = existing.nip;
    document.getElementById('cred-email').value = existing.email || '';
    document.getElementById('cred-instansi').value = existing.instansi;
    document.getElementById('cred-ttl').value = existing.ttl || '';
    document.getElementById('cred-telp').value = existing.telp || '';
    document.getElementById('cred-secret').value = existing.secret_key;
    if (preview) preview.src = existing.photo_url;
  } else {
    if (title) title.innerText = 'SPBE DISKOMINFO - ID CARD PEGAWAI BARU';
    form.reset();
    if (preview) preview.src = 'https://placehold.co/120x150/0066D6/ffffff?text=FOTO+ID';
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

export function previewPhotoUpload(event) {
  const input = event.target || event.srcElement;
  if (input && input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById('photo-preview');
      if (preview) preview.src = e.target.result;
      currentPhotoBase64 = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

export async function saveCredentialForm(event) {
  if (event && event.preventDefault) event.preventDefault();

  const editId = parseInt(document.getElementById('cred-edit-index').value, 10);
  const isEdit = editId > 0;

  const payload = {
    nama: document.getElementById('cred-nama').value.trim(),
    nip: document.getElementById('cred-nip').value.trim(),
    email: document.getElementById('cred-email').value.trim(),
    instansi: document.getElementById('cred-instansi').value.trim(),
    ttl: document.getElementById('cred-ttl').value.trim(),
    telp: document.getElementById('cred-telp').value.trim(),
    secret_key: document.getElementById('cred-secret').value.trim(),
    photo_base64: currentPhotoBase64,
  };

  try {
    const response = await fetch(isEdit ? `/api/credentials/${editId}` : '/api/credentials', {
      method: isEdit ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (!response.ok) {
      const firstError = result.errors
        ? Object.values(result.errors).flat()[0]
        : (result.message || 'Validasi gagal.');
      throw new Error(firstError);
    }

    closeCredentialModal();
    await fetchCredentials();
    if (window.openModal) window.openModal(result.message);
  } catch (error) {
    if (window.openModal) {
      window.openModal(`<span class="text-rose-500 font-bold">${error.message}</span>`);
    } else {
      alert(error.message);
    }
  }

  return false;
}

export function editCredential(id) {
  openCredentialModal(id);
}

export async function deleteCredential(id) {
  const item = credentialsData.find((c) => c.id === id);
  if (!item) return;

  if (!confirm(`Apakah Anda yakin ingin menghapus kredensial ${item.nama}?`)) return;

  try {
    const response = await fetch(`/api/credentials/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });

    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'Gagal menghapus data.');

    await fetchCredentials();
    if (window.openModal) window.openModal(result.message);
  } catch (error) {
    alert(error.message);
  }
}