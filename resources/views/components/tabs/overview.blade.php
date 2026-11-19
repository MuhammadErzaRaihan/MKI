<div id="tab-overview" class="page-view active space-y-6">
  <!-- Header Status Insiden Live -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 theme-card p-5">
    <div class="flex flex-wrap items-center gap-3">
      <h2 class="theme-heading text-lg font-extrabold uppercase tracking-wide">
        SEC - ADMIN
      </h2>
      <span id="overview-threat-badge"
            class="text-[10px] font-bold px-2.5 py-1 rounded uppercase {{ ($stats['blocked_count'] ?? 0) > 0 ? 'bg-rose-600 text-white' : (($stats['suspicious_count'] ?? 0) > 0 ? 'bg-amber-500 text-slate-950' : 'bg-emerald-600 text-white') }}">
        {{ ($stats['blocked_count'] ?? 0) > 0 ? 'CRITICAL ANOMALY' : (($stats['suspicious_count'] ?? 0) > 0 ? 'SUSPICIOUS ACTIVITY' : 'SYSTEM NORMAL') }}
      </span>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('gateway.login.form') }}" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2">
        <i class="fa-solid fa-shield-halved"></i>
        <span>BUKA GERBANG LOGIN</span>
      </a>
      <button type="button" id="overview-export-btn" class="px-4 py-2.5 theme-button-primary text-xs uppercase font-bold flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>EXPORT CSV</span>
      </button>
    </div>
  </div>

  <!-- 4 Kartu KPI Live -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-75">SERANGAN DIBLOKIR</div>
        <div id="kpi-blocked-count" class="font-brutal font-black text-xl mt-1 text-rose-500">{{ $stats['blocked_count'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-65 mt-0.5">SQLi, XSS &amp; anomali paket</div>
      </div>
      <div class="w-10 h-10 bg-rose-500/10 border border-rose-500/40 flex items-center justify-center font-bold text-lg rounded-xl text-rose-500">
        <i class="fa-solid fa-ban"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-75">AKSES MENCURIGAKAN</div>
        <div id="kpi-suspicious-count" class="font-brutal font-black text-xl mt-1 text-amber-500">{{ $stats['suspicious_count'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-65 mt-0.5">IP luar kantor / MFA</div>
      </div>
      <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/40 flex items-center justify-center font-bold text-lg rounded-xl text-amber-500">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-75">AVG DETECTION LATENCY</div>
        <div id="kpi-avg-latency" class="font-brutal font-black text-xl mt-1 text-sky-500">{{ $stats['avg_latency_ms'] ?? 0 }} ms</div>
        <div id="kpi-total-events" class="text-[10px] font-mono opacity-65 mt-0.5">Dari {{ $stats['total_events'] ?? 0 }} total event</div>
      </div>
      <div class="w-10 h-10 bg-sky-500/10 border border-sky-500/40 flex items-center justify-center font-bold text-lg rounded-xl text-sky-500">
        <i class="fa-solid fa-gauge-high"></i>
      </div>
    </div>

    <div class="theme-card p-4 flex items-center justify-between rounded-2xl">
      <div>
        <div class="text-[10px] font-mono uppercase tracking-wider opacity-75">KREDENSIAL ASN AKTIF</div>
        <div class="font-brutal font-black text-xl mt-1 text-emerald-500">{{ $stats['active_asn'] ?? 0 }}</div>
        <div class="text-[10px] font-mono opacity-65 mt-0.5">Terdaftar pada Gerbang SPBE</div>
      </div>
      <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/40 flex items-center justify-center font-bold text-lg rounded-xl text-emerald-500">
        <i class="fa-solid fa-id-card-clip"></i>
      </div>
    </div>
  </div>

  <!-- Panel Hasil Inspeksi Gerbang & Telemetri -->
  <div class="theme-card p-5 rounded-2xl space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-current/20">
      <div>
        <h3 class="theme-heading text-xs font-bold uppercase tracking-wider">
          <i class="fa-solid fa-radar text-sky-500 mr-2"></i>Hasil Inspeksi
        </h3>
      </div>

      <button type="button" id="overview-telemetry-refresh" class="px-3 py-1.5 rounded-lg theme-button-primary text-xs font-bold uppercase self-start sm:self-auto cursor-pointer">
        <i class="fa-solid fa-rotate mr-1"></i>Refresh
      </button>
    </div>

    <div id="overview-latest-telemetry" class="p-4 rounded-xl border border-current/20 bg-slate-500/10 text-xs">
      Memuat hasil inspeksi terbaru...
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-current/20 text-[10px] font-bold uppercase tracking-wider opacity-75">
            <th class="py-2 pr-3">Waktu</th>
            <th class="py-2 pr-3">IP Asal</th>
            <th class="py-2 pr-3">Event</th>
            <th class="py-2 pr-3">Status</th>
            <th class="py-2 text-right">Latency</th>
          </tr>
        </thead>
        <tbody id="overview-telemetry-list" class="divide-y divide-slate-500/20"></tbody>
      </table>
    </div>
  </div>

  <!-- Stage Timeline Nodes (Dinamis dari 4 Event Terbaru) -->
  <div class="theme-card p-6 space-y-4 rounded-2xl">
    <div class="flex items-center justify-between border-b border-current/20 pb-3">
      <h3 class="theme-heading text-xs font-bold uppercase tracking-wide">STAGE TIMELINE NODES</h3>
      <span id="overview-timeline-count" class="font-mono text-xs opacity-75">MEMUAT EVENT...</span>
    </div>

    <div class="relative py-1 overflow-x-auto">
      <div id="overview-stage-timeline" class="grid grid-cols-1 md:grid-cols-4 gap-4 min-w-[750px]">
        <div class="bg-slate-500/10 border border-current/20 p-4 rounded-xl text-xs opacity-75">
          Memuat jejak aktivitas gerbang...
        </div>
      </div>
    </div>
  </div>

  <!-- Rekomendasi Mitigasi SOC (Dinamis dari Ancaman Nyata, Full-Width Tanpa Chart) -->
  <div class="theme-card p-5 space-y-4 rounded-2xl">
    <div class="flex items-center justify-between border-b border-current/20 pb-3">
      <div>
        <h3 class="theme-heading text-xs font-bold uppercase">REKOMENDASI MITIGASI</h3>
      </div>
      <span class="text-[9px] font-mono px-2 py-0.5 bg-slate-500/15 border border-current/30 rounded-md font-bold">LIVE RESPONSE</span>
    </div>

    <div id="overview-suggested-actions" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="p-4 bg-slate-500/10 border border-current/20 rounded-xl text-xs opacity-75">
        Menganalisis rekomendasi mitigasi dari audit log...
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const latestBox = document.getElementById('overview-latest-telemetry');
    const listBody = document.getElementById('overview-telemetry-list');
    const timelineGrid = document.getElementById('overview-stage-timeline');
    const timelineCount = document.getElementById('overview-timeline-count');
    const actionsGrid = document.getElementById('overview-suggested-actions');
    const refreshBtn = document.getElementById('overview-telemetry-refresh');
    const exportBtn = document.getElementById('overview-export-btn');

    if (!latestBox || !listBody) return;

    let currentLogs = [];

    function formatTimeWita(iso) {
      if (!iso) return '-';
      return new Date(iso).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });
    }

    function updateSummaryCards(rows) {
      const counts = { ALLOWED: 0, SUSPICIOUS: 0, BLOCKED: 0 };
      let totalLatency = 0;

      rows.forEach((r) => {
        if (counts[r.status] !== undefined) counts[r.status]++;
        totalLatency += Number(r.detection_latency_ms || 0);
      });

      const avgLatency = rows.length ? (totalLatency / rows.length).toFixed(2) : '0.00';

      const kpiBlocked = document.getElementById('kpi-blocked-count');
      const kpiSuspicious = document.getElementById('kpi-suspicious-count');
      const kpiLatency = document.getElementById('kpi-avg-latency');
      const kpiTotal = document.getElementById('kpi-total-events');
      const threatBadge = document.getElementById('overview-threat-badge');

      if (kpiBlocked) kpiBlocked.textContent = counts.BLOCKED;
      if (kpiSuspicious) kpiSuspicious.textContent = counts.SUSPICIOUS;
      if (kpiLatency) kpiLatency.textContent = `${avgLatency} ms`;
      if (kpiTotal) kpiTotal.textContent = `Dari ${rows.length} total event`;

      if (threatBadge) {
        if (counts.BLOCKED > 0) {
          threatBadge.className = 'text-[10px] font-bold px-2.5 py-1 rounded uppercase bg-rose-600 text-white';
          threatBadge.textContent = `CRITICAL ANOMALY (${counts.BLOCKED} BLOCKED)`;
        } else if (counts.SUSPICIOUS > 0) {
          threatBadge.className = 'text-[10px] font-bold px-2.5 py-1 rounded uppercase bg-amber-500 text-slate-950';
          threatBadge.textContent = `SUSPICIOUS ACTIVITY (${counts.SUSPICIOUS})`;
        } else {
          threatBadge.className = 'text-[10px] font-bold px-2.5 py-1 rounded uppercase bg-emerald-600 text-white';
          threatBadge.textContent = 'SYSTEM NORMAL';
        }
      }
    }

    function renderTelemetry(rows) {
      if (rows.length === 0) {
        latestBox.textContent = 'Belum ada aktivitas inspeksi tercatat.';
        listBody.innerHTML = '';
        return;
      }

      const top = rows[0];
      const badgeClass = top.status === 'ALLOWED'
        ? 'bg-emerald-500 text-slate-950'
        : top.status === 'SUSPICIOUS'
          ? 'bg-amber-400 text-slate-950'
          : 'bg-rose-600 text-white';

      latestBox.innerHTML = '';

      const header = document.createElement('div');
      header.className = 'flex flex-wrap items-center justify-between gap-2 mb-2';

      const badge = document.createElement('span');
      badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider ' + badgeClass;
      badge.textContent = `${top.status} • ${top.event_type}`;

      const meta = document.createElement('span');
      meta.className = 'font-mono text-[11px] text-sky-500 font-semibold';
      meta.textContent = `Latency: ${top.detection_latency_ms ?? 0} ms | IP: ${top.ip_address} (${top.source})`;

      header.append(badge, meta);

      const detail = document.createElement('p');
      detail.className = 'font-mono text-[11px] opacity-90 break-all';
      detail.textContent = `Payload/Detail: ${top.payload_snippet || '-'}`;

      const action = document.createElement('p');
      action.className = 'text-[11px] opacity-80 mt-1';
      action.textContent = `Mitigasi: ${top.recommended_action || '-'}`;

      latestBox.append(header, detail, action);

      listBody.innerHTML = '';
      rows.slice(0, 5).forEach((row) => {
        const tr = document.createElement('tr');
        const statusColor = row.status === 'BLOCKED'
          ? 'text-rose-500'
          : row.status === 'SUSPICIOUS'
            ? 'text-amber-500'
            : 'text-emerald-500';

        const cells = [
          { text: formatTimeWita(row.created_at), cls: 'py-2 pr-3 font-mono text-[11px] opacity-80' },
          { text: row.ip_address || '-', cls: 'py-2 pr-3 font-mono text-[11px] text-sky-500 font-semibold' },
          { text: row.event_type || '-', cls: 'py-2 pr-3 font-mono text-[11px]' },
          { text: row.status || '-', cls: `py-2 pr-3 font-mono text-[10px] font-bold ${statusColor}` },
          { text: `${row.detection_latency_ms ?? 0} ms`, cls: 'py-2 text-right font-mono text-[11px] text-sky-500 font-semibold' },
        ];

        cells.forEach((c) => {
          const td = document.createElement('td');
          td.className = c.cls;
          td.textContent = c.text;
          tr.appendChild(td);
        });

        listBody.appendChild(tr);
      });
    }

    function renderStageTimeline(rows) {
      if (!timelineGrid) return;

      const latestFour = rows.slice(0, 4);
      if (timelineCount) {
        timelineCount.textContent = `${latestFour.length} LATEST EVENTS RECORDED`;
      }

      if (latestFour.length === 0) {
        timelineGrid.innerHTML = '<div class="p-4 text-xs opacity-75">Belum ada event terekam.</div>';
        return;
      }

      timelineGrid.innerHTML = '';
      latestFour.forEach((ev) => {
        const style = ev.status === 'BLOCKED'
          ? { title: 'text-rose-500', pill: 'bg-rose-600 text-white' }
          : ev.status === 'SUSPICIOUS'
            ? { title: 'text-amber-500', pill: 'bg-amber-400 text-slate-950' }
            : { title: 'text-emerald-500', pill: 'bg-emerald-500 text-slate-950' };

        const card = document.createElement('div');
        card.className = 'bg-slate-500/10 border border-current/25 p-4 space-y-1.5 rounded-xl';

        const title = document.createElement('span');
        title.className = `block text-[10px] font-mono font-bold ${style.title}`;
        title.textContent = ev.event_type || 'EVENT';

        const sub = document.createElement('p');
        sub.className = 'text-[11px] opacity-85 font-mono';
        sub.textContent = `IP ${ev.ip_address || '-'} (${ev.source || '-'})`;

        const pill = document.createElement('div');
        pill.className = `mt-2 text-[9px] font-mono ${style.pill} px-2 py-0.5 inline-block font-bold rounded-md`;
        pill.textContent = `${ev.status} • ${formatTimeWita(ev.created_at)} WITA`;

        card.append(title, sub, pill);
        timelineGrid.appendChild(card);
      });
    }

    function renderSuggestedActions(rows) {
      if (!actionsGrid) return;

      const actionable = rows.filter((r) => r.status === 'BLOCKED' || r.status === 'SUSPICIOUS').slice(0, 4);

      if (actionable.length === 0) {
        actionsGrid.innerHTML = '<div class="p-4 bg-slate-500/10 border border-current/20 rounded-xl text-xs opacity-75">Tidak ada ancaman aktif yang membutuhkan tindakan mitigasi.</div>';
        return;
      }

      actionsGrid.innerHTML = '';
      actionable.forEach((item) => {
        const card = document.createElement('div');
        card.className = 'p-4 bg-slate-500/10 border border-current/25 flex flex-col justify-between gap-3 rounded-xl';

        const info = document.createElement('div');
        info.className = 'space-y-1';

        const topLine = document.createElement('div');
        topLine.className = 'flex items-center justify-between gap-2';

        const heading = document.createElement('h4');
        heading.className = 'font-bold text-xs';
        heading.textContent = `${item.event_type} — IP ${item.ip_address}`;

        const badge = document.createElement('span');
        badge.className = item.status === 'BLOCKED'
          ? 'text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-rose-600 text-white'
          : 'text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-amber-400 text-slate-950';
        badge.textContent = item.status;

        topLine.append(heading, badge);

        const desc = document.createElement('p');
        desc.className = 'text-[11px] opacity-80';
        desc.textContent = item.recommended_action || 'Isolasi IP sumber dan audit log akses.';

        const timeMeta = document.createElement('p');
        timeMeta.className = 'text-[10px] opacity-65 font-mono';
        timeMeta.textContent = `Sumber: ${item.source} • Pukul ${formatTimeWita(item.created_at)} WITA`;

        info.append(topLine, desc, timeMeta);

        const btnRow = document.createElement('div');
        btnRow.className = 'flex items-center gap-2 pt-1';

        const rejectBtn = document.createElement('button');
        rejectBtn.type = 'button';
        rejectBtn.className = 'flex-1 py-1.5 bg-slate-500/20 hover:bg-slate-500/30 font-mono text-[10px] font-bold uppercase border border-current/30 rounded-lg cursor-pointer';
        rejectBtn.textContent = 'ABAIKAN';
        rejectBtn.addEventListener('click', () => {
          card.classList.add('opacity-50');
          if (window.openModal) {
            window.openModal(`Rekomendasi mitigasi untuk IP ${item.ip_address} ditandai diabaikan.`);
          }
        });

        const approveBtn = document.createElement('button');
        approveBtn.type = 'button';
        approveBtn.className = 'flex-1 py-1.5 theme-button-primary text-[10px] uppercase font-bold rounded-lg cursor-pointer';
        approveBtn.textContent = 'TERAPKAN MITIGASI';
        approveBtn.addEventListener('click', () => {
          approveBtn.textContent = 'MITIGASI AKTIF';
          approveBtn.disabled = true;
          if (window.openModal) {
            window.openModal(`Mitigasi diterapkan untuk IP ${item.ip_address} (${item.event_type}).`);
          }
        });

        btnRow.append(rejectBtn, approveBtn);
        card.append(info, btnRow);
        actionsGrid.appendChild(card);
      });
    }

    async function loadOverviewData() {
      try {
        const res = await fetch('/api/datalogs?per_page=50', { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('Gagal mengambil data telemetri.');

        const json = await res.json();
        currentLogs = json.data || [];

        updateSummaryCards(currentLogs);
        renderTelemetry(currentLogs);
        renderStageTimeline(currentLogs);
        renderSuggestedActions(currentLogs);
      } catch (err) {
        latestBox.textContent = err.message;
      }
    }

    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        if (!currentLogs.length) {
          if (window.openModal) window.openModal('Belum ada data log untuk diekspor.');
          return;
        }
        const headers = ['id', 'created_at', 'source', 'ip_address', 'event_type', 'status', 'detection_latency_ms', 'recommended_action'];
        const csvLines = [
          headers.join(','),
          ...currentLogs.map((row) =>
            headers.map((h) => `"${String(row[h] ?? '').replace(/"/g, '""')}"`).join(',')
          ),
        ];
        const blob = new Blob([csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `soc-audit-report-${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
      });
    }

    if (refreshBtn) refreshBtn.addEventListener('click', loadOverviewData);
    loadOverviewData();
  })();
</script>