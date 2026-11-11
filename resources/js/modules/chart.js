import { activeTheme } from './theme.js';

let loginAttemptsChart = null;
let threatDetectionChart = null;
let fullTrafficChart = null;

export function initCharts() {
  const ctx1 = document.getElementById('chartLoginAttempts');
  if (ctx1) {
    loginAttemptsChart = new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
        datasets: [{
          label: 'Attempts',
          data: [35, 48, 62, 76, 42, 30],
          backgroundColor: ['#0066D6', '#38BDF8', '#6499CC', '#0066D6', '#94A3B8', '#38BDF8'],
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } },
          y: { grid: { color: 'rgba(0, 102, 214, 0.08)' }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } }
        }
      }
    });
  }

  const ctx2 = document.getElementById('chartThreatDetection');
  if (ctx2) {
    threatDetectionChart = new Chart(ctx2, {
      type: 'line',
      data: {
        labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
        datasets: [
          {
            label: 'Threat Spike',
            data: [15, 45, 35, 25, 18, 12],
            borderColor: '#0066D6',
            backgroundColor: 'rgba(0, 102, 214, 0.15)',
            fill: true,
            tension: 0.3,
            borderWidth: 2.5
          },
          {
            label: 'Baseline',
            data: [-15, -45, -35, -25, -18, -12],
            borderColor: '#38BDF8',
            backgroundColor: 'rgba(56, 189, 248, 0.15)',
            fill: true,
            tension: 0.3,
            borderWidth: 2.5
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } },
          y: { grid: { color: 'rgba(0, 102, 214, 0.08)' }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } }
        }
      }
    });
  }
}

export function initFullTrafficChart() {
  if (fullTrafficChart) return;
  const ctx3 = document.getElementById('chartTrafficFull');
  if (!ctx3) return;

  fullTrafficChart = new Chart(ctx3, {
    type: 'line',
    data: {
      labels: ['Jan 2021', 'Jul 2021', 'Jan 2022', 'Jul 2022', 'Jan 2023', 'Jul 2023', 'Jan 2024', 'Jul 2024', 'Jan 2025', 'Nov 2026'],
      datasets: [
        {
          label: 'Mobile',
          data: [12, 19, 15, 22, 18, 24, 21, 25, 22, 28],
          borderColor: '#0066D6',
          backgroundColor: 'rgba(0, 102, 214, 0.12)',
          tension: 0.3,
          fill: true,
          borderWidth: 2.5
        },
        {
          label: 'Desktop',
          data: [8, 12, 10, 14, 12, 16, 15, 18, 17, 20],
          borderColor: '#38BDF8',
          backgroundColor: 'transparent',
          tension: 0.3,
          borderWidth: 2.5
        },
        {
          label: 'Tablet',
          data: [4, 6, 5, 8, 7, 9, 8, 10, 9, 11],
          borderColor: '#6499CC',
          tension: 0.3,
          borderWidth: 2.5
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(0, 102, 214, 0.08)' }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } },
        y: { grid: { color: 'rgba(0, 102, 214, 0.08)' }, ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } } }
      }
    }
  });
}

export function updateChartsColor() {
  if (!loginAttemptsChart || !threatDetectionChart) return;

  let attemptColors = ['#0066D6', '#38BDF8', '#6499CC', '#0066D6', '#94A3B8', '#38BDF8'];
  let spikeColor = '#0066D6';
  let baselineColor = '#38BDF8';
  let fontFam = 'Inter';

  if (activeTheme === 'soc') {
    attemptColors = ['#CFEFFF', '#7AD7F0', '#9FD6E8', '#CFEFFF', '#485A55', '#7AD7F0'];
    spikeColor = '#7AD7F0';
    baselineColor = '#485A55';
  } else if (activeTheme === 'retro') {
    attemptColors = ['#00E5E8', '#FDF833', '#80F555', '#FAF0CA', '#00E5E8', '#80F555'];
    spikeColor = '#FDF833';
    baselineColor = '#00E5E8';
    fontFam = 'Space Grotesk';
  }

  loginAttemptsChart.data.datasets[0].backgroundColor = attemptColors;
  loginAttemptsChart.options.scales.x.ticks.font.family = fontFam;
  loginAttemptsChart.update();

  threatDetectionChart.data.datasets[0].borderColor = spikeColor;
  threatDetectionChart.data.datasets[1].borderColor = baselineColor;
  threatDetectionChart.update();

  if (fullTrafficChart) {
    if (activeTheme === 'soc') {
      fullTrafficChart.data.datasets[0].borderColor = '#CFEFFF';
      fullTrafficChart.data.datasets[1].borderColor = '#7AD7F0';
      fullTrafficChart.data.datasets[2].borderColor = '#9FD6E8';
    } else if (activeTheme === 'retro') {
      fullTrafficChart.data.datasets[0].borderColor = '#00E5E8';
      fullTrafficChart.data.datasets[1].borderColor = '#FDF833';
      fullTrafficChart.data.datasets[2].borderColor = '#80F555';
    } else {
      fullTrafficChart.data.datasets[0].borderColor = '#0066D6';
      fullTrafficChart.data.datasets[1].borderColor = '#38BDF8';
      fullTrafficChart.data.datasets[2].borderColor = '#6499CC';
    }
    fullTrafficChart.update();
  }
}