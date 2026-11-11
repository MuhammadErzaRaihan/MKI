export function switchTab(tabId) {
  document.querySelectorAll('.page-view').forEach(view => view.classList.remove('active'));
  const activeView = document.getElementById(tabId);
  if (activeView) activeView.classList.add('active');

  const navs = [
    { id: 'nav-tab-overview', tab: 'tab-overview' },
    { id: 'nav-tab-traffic', tab: 'tab-traffic' },
    { id: 'nav-tab-credential', tab: 'tab-credential' },
    { id: 'nav-tab-datalog', tab: 'tab-datalog' },
    { id: 'nav-tab-blocked', tab: 'tab-blocked' }
  ];

  navs.forEach(nav => {
    const el = document.getElementById(nav.id);
    if (el) {
      if (nav.tab === tabId) {
        el.className = "w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all theme-button-primary";
      } else {
        el.className = "w-full flex items-center justify-between px-3.5 py-3 border border-current font-bold text-xs uppercase rounded-xl transition-all bg-black/20 hover:bg-black/40";
      }
    }
  });

  if (tabId === 'tab-traffic' && window.initFullTrafficChart) {
    window.initFullTrafficChart();
  }
}

export function toggleMobileSidebar() {
  const sidebar = document.getElementById('sidebar-nav');
  if (sidebar) sidebar.classList.toggle('hidden');
}

export function openModal(msg) {
  const modal = document.getElementById('action-modal');
  const body = document.getElementById('modal-body');
  if (modal && body) {
    body.innerHTML = msg;
    modal.classList.remove('hidden');
  }
}

export function closeModal() {
  const modal = document.getElementById('action-modal');
  if (modal) modal.classList.add('hidden');
}