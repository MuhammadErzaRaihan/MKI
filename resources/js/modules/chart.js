let cachedLogs = [];

function getThemeColors() {
    const isLight =
        document.body.classList.contains('theme-clean') ||
        document.body.classList.contains('theme-corporate') ||
        !document.body.classList.contains('theme-dark');

    return {
        text: isLight ? '#0f172a' : '#f1f5f9',
        muted: isLight ? '#475569' : '#94a3b8',
        grid: isLight ? '#e2e8f0' : '#1e293b',
        axis: isLight ? '#cbd5e1' : '#334155',
        ringBg: isLight ? '#e2e8f0' : '#334155',
    };
}

function drawRoundedBar(ctx, x, y, width, height, radius) {
    const r = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x, y + height);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.lineTo(x + width - r, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + r);
    ctx.lineTo(x + width, y + height);
    ctx.closePath();
    ctx.fill();
}

function renderBarChart(canvas, series) {
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();
    const w = rect.width || 320;
    const h = rect.height || 192;

    canvas.width = w * dpr;
    canvas.height = h * dpr;

    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const colors = getThemeColors();
    const rawMax = Math.max(...series.map((s) => s.value), 1);
    const maxVal = Math.ceil(rawMax * 1.25); // Ruang 25% di atas agar angka tidak terpotong

    const padLeft = 28;
    const padRight = 12;
    const padTop = 24;
    const padBottom = 30;
    const chartW = w - padLeft - padRight;
    const chartH = h - padTop - padBottom;

    // Gambar 3 garis bantu horizontal + label skala Y
    ctx.font = '10px monospace';
    ctx.textAlign = 'right';
    for (let step = 0; step <= 2; step++) {
        const ratio = step / 2;
        const y = padTop + chartH - ratio * chartH;
        const tickVal = Math.round(ratio * maxVal);

        ctx.strokeStyle = step === 0 ? colors.axis : colors.grid;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padLeft, y);
        ctx.lineTo(w - padRight, y);
        ctx.stroke();

        ctx.fillStyle = colors.muted;
        ctx.fillText(String(tickVal), padLeft - 6, y + 3);
    }

    const slotW = chartW / series.length;
    const barW = Math.min(42, slotW * 0.48);

    series.forEach((item, i) => {
        const barH = item.value > 0 ? Math.max((item.value / maxVal) * chartH, 6) : 2;
        const x = padLeft + i * slotW + (slotW - barW) / 2;
        const y = padTop + (chartH - barH);

        ctx.fillStyle = item.color;
        drawRoundedBar(ctx, x, y, barW, barH, 6);

        // Angka nilai di atas bar
        ctx.fillStyle = colors.text;
        ctx.font = 'bold 12px monospace';
        ctx.textAlign = 'center';
        ctx.fillText(String(item.value), x + barW / 2, y - 7);

        // Label kategori di bawah sumbu X
        ctx.fillStyle = colors.muted;
        ctx.font = 'bold 10px monospace';
        ctx.fillText(item.label, x + barW / 2, h - 10);
    });
}

function renderDonutChart(canvas, groups) {
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();
    const w = rect.width || 320;
    const h = rect.height || 192;

    canvas.width = w * dpr;
    canvas.height = h * dpr;

    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const colors = getThemeColors();
    const entries = Object.entries(groups);
    const total = entries.reduce((sum, [, v]) => sum + v, 0);
    const palette = ['#e11d48', '#d97706', '#0284c7', '#7c3aed', '#ea580c'];

    const r = Math.min(h * 0.34, 52);
    const cx = r + 20;
    const cy = h / 2;
    const lineWidth = 15;

    if (total === 0) {
        ctx.strokeStyle = colors.ringBg;
        ctx.lineWidth = lineWidth;
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.stroke();

        ctx.fillStyle = colors.text;
        ctx.font = 'bold 14px monospace';
        ctx.textAlign = 'center';
        ctx.fillText('0', cx, cy + 5);

        ctx.fillStyle = colors.muted;
        ctx.font = '11px monospace';
        ctx.textAlign = 'left';
        ctx.fillText('Belum ada ancaman diblokir', cx + r + 20, cy + 4);
        return;
    }

    let startAngle = -Math.PI / 2;
    entries.forEach(([label, val], idx) => {
        const slice = (val / total) * Math.PI * 2;
        const color = palette[idx % palette.length];

        ctx.strokeStyle = color;
        ctx.lineWidth = lineWidth;
        ctx.beginPath();
        ctx.arc(cx, cy, r, startAngle, startAngle + slice);
        ctx.stroke();

        startAngle += slice;

        if (idx < 4) {
            const legendStartX = cx + r + 22;
            const maxTextWidth = w - legendStartX - 8;
            const ly = 34 + idx * 32;

            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(legendStartX + 4, ly - 4, 4, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = colors.text;
            ctx.font = 'bold 10px monospace';
            ctx.textAlign = 'left';

            let displayLabel = `${label} (${val})`;
            while (ctx.measureText(displayLabel).width > maxTextWidth && displayLabel.length > 6) {
                displayLabel = displayLabel.slice(0, -5) + '… (' + val + ')';
            }
            ctx.fillText(displayLabel, legendStartX + 14, ly);
        }
    });

    // Total di tengah donat (sinkron dengan badge BLOCKED)
    ctx.fillStyle = colors.text;
    ctx.font = 'bold 16px monospace';
    ctx.textAlign = 'center';
    ctx.fillText(String(total), cx, cy + 3);

    ctx.fillStyle = colors.muted;
    ctx.font = 'bold 8px monospace';
    ctx.fillText('BLOCKED', cx, cy + 14);
}

function drawOverviewFromCache() {
    const counts = { ALLOWED: 0, SUSPICIOUS: 0, BLOCKED: 0 };
    let totalLatency = 0;

    cachedLogs.forEach((item) => {
        if (counts[item.status] !== undefined) counts[item.status]++;
        totalLatency += Number(item.detection_latency_ms || 0);
    });

    const avgLatency = cachedLogs.length ? (totalLatency / cachedLogs.length).toFixed(2) : '0.00';

    const kpiMap = {
        'kpi-blocked-count': counts.BLOCKED,
        'kpi-suspicious-count': counts.SUSPICIOUS,
        'kpi-avg-latency': `${avgLatency} ms`,
        'kpi-total-events': `Dari ${cachedLogs.length} total event`,
        'chart-login-total': cachedLogs.length,
        'chart-threat-blocked': `${counts.BLOCKED} BLOCKED`,
    };

    Object.entries(kpiMap).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });

    renderBarChart(document.getElementById('chartLoginAttempts'), [
        { label: 'ALLOWED', value: counts.ALLOWED, color: '#10b981' },
        { label: 'SUSPICIOUS', value: counts.SUSPICIOUS, color: '#f59e0b' },
        { label: 'BLOCKED', value: counts.BLOCKED, color: '#e11d48' },
    ]);

    // Hanya hitung event yang benar-benar BLOCKED agar sinkron dengan header "X BLOCKED"
    const threatGroups = {};
    cachedLogs.forEach((item) => {
        if (item.status === 'BLOCKED') {
            const key = item.event_type || 'BLOCKED_EVENT';
            threatGroups[key] = (threatGroups[key] || 0) + 1;
        }
    });
    renderDonutChart(document.getElementById('chartThreatDetection'), threatGroups);
}

export function initCharts() {}
// export async function initCharts() {
//     try {
//         const res = await fetch('/api/datalogs?per_page=100', {
//             headers: { Accept: 'application/json' },
//         });
//         if (res.ok) {
//             const json = await res.json();
//             cachedLogs = json.data || [];
//         }
//     } catch (err) {
//         console.error('Gagal memuat data chart:', err);
//     }

//     drawOverviewFromCache();

//     const refreshBtn = document.getElementById('overview-telemetry-refresh');
//     if (refreshBtn && !refreshBtn.dataset.bound) {
//         refreshBtn.dataset.bound = '1';
//         refreshBtn.addEventListener('click', () => initCharts());
//     }

//     if (!window.__mkiChartResizeBound) {
//         window.__mkiChartResizeBound = true;
//         window.addEventListener('resize', () => {
//             drawOverviewFromCache();
//             initFullTrafficChart();
//         });
//     }
// }

// export function initFullTrafficChart() {
//     const trafficCanvas = document.getElementById('chartFullTraffic');
//     if (!trafficCanvas) return;

//     const sourceGroups = { GATEWAY_LOGIN: 0, WEB_WAF: 0, TSHARK_CAPTURE: 0 };
//     cachedLogs.forEach((item) => {
//         const src = item.source || 'GATEWAY_LOGIN';
//         sourceGroups[src] = (sourceGroups[src] || 0) + 1;
//     });

//     renderBarChart(trafficCanvas, [
//         { label: 'GATEWAY_LOGIN', value: sourceGroups.GATEWAY_LOGIN, color: '#0284c7' },
//         { label: 'WEB_WAF', value: sourceGroups.WEB_WAF, color: '#e11d48' },
//         { label: 'TSHARK_CAPTURE', value: sourceGroups.TSHARK_CAPTURE, color: '#10b981' },
//     ]);
// }

export function initFullTrafficChart() {}
export function updateChartsColor() {}

// export function updateChartsColor() {
//     drawOverviewFromCache();
//     initFullTrafficChart();
// }