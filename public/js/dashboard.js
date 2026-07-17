const palette = {
  ok: '#166534',
  warn: '#c2410c',
  danger: '#991b1b',
  accent: '#0f5c56',
  ink: '#1c1917',
  muted: '#57534e',
  grid: '#e4ddd3',
};

Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";
Chart.defaults.color = palette.muted;
Chart.defaults.borderColor = palette.grid;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.responsive = true;

async function loadDashboard() {
  const get = async (path) => {
    const res = await fetch(path, { credentials: 'same-origin' });
    if (!res.ok) throw new Error(path);
    return res.json();
  };

  try {
    const kpi = await get('/dashboard/chart/kpi');

    // Remove skeleton classes from KPI
    document.querySelectorAll('.kpi-card[aria-busy="true"]').forEach((el) => {
      el.removeAttribute('aria-busy');
    });
    const kpiVals = [
      document.getElementById('kpiSp'),
      document.getElementById('kpiAk'),
      document.getElementById('kpiUnit'),
      document.getElementById('kpiTrx'),
    ];
    kpiVals.forEach((el) => el.classList.remove('skeleton-text'));

    const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
    document.getElementById('kpiSp').textContent = fmt(kpi.sparepart);
    document.getElementById('kpiAk').textContent = fmt(kpi.aksesoris);
    document.getElementById('kpiUnit').textContent = fmt(kpi.unit_stok);
    document.getElementById('kpiTrx').textContent = fmt(kpi.trx_bulan);

    const kritis = Number(kpi.stok_kritis || 0);
    const badge = document.getElementById('kpiKritisBadge');
    const stokCard = document.querySelector('.kpi-card-stok');
    const unitHint = document.getElementById('kpiUnitHint');
    if (badge) {
      if (kritis > 0) {
        badge.hidden = false;
        badge.classList.remove('is-hidden');
        badge.textContent = fmt(kritis) + ' kritis';
        badge.title = 'Barang dengan status stok rendah atau habis';
        if (stokCard) {
          stokCard.href = '/stok?status_stok=kritis';
          stokCard.setAttribute('aria-label', 'Lihat stok kritis: ' + kritis + ' barang');
        }
        if (unitHint) {
          unitHint.textContent = 'Ada stok menipis · buka stok kritis';
        }
      } else {
        badge.hidden = true;
        badge.classList.add('is-hidden');
        badge.textContent = '0 kritis';
        if (stokCard) {
          stokCard.href = '/stok';
          stokCard.setAttribute('aria-label', 'Lihat monitoring stok');
        }
        if (unitHint) {
          unitHint.textContent = 'Semua barang · stok aman';
        }
      }
    }

    const st = await get('/dashboard/chart/status-stok');
    document.getElementById('chartStatus').parentElement.classList.remove('skeleton-box');
    new Chart(document.getElementById('chartStatus'), {
      type: 'doughnut',
      data: {
        labels: ['Aman', 'Rendah', 'Habis'],
        datasets: [
          {
            data: [st.aman, st.rendah, st.habis],
            backgroundColor: [palette.ok, palette.warn, palette.danger],
            borderWidth: 0,
            hoverOffset: 3,
          },
        ],
      },
      options: {
        cutout: '64%',
        layout: { padding: 4 },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 10, padding: 12, font: { size: 12 } },
          },
        },
      },
    });

    const kat = await get('/dashboard/chart/stok-kategori');
    document.getElementById('chartKat').parentElement.classList.remove('skeleton-box');
    new Chart(document.getElementById('chartKat'), {
      type: 'bar',
      data: {
        labels: kat.map((r) => r.kategori || '—'),
        datasets: [
          {
            label: 'Unit',
            data: kat.map((r) => Number(r.total)),
            backgroundColor: palette.accent,
            borderRadius: 4,
            maxBarThickness: 18,
          },
        ],
      },
      options: {
        indexAxis: 'y',
        layout: { padding: { top: 0, right: 8, bottom: 0, left: 0 } },
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { color: palette.grid },
            ticks: { precision: 0, font: { size: 11 } },
            border: { display: false },
          },
          y: {
            grid: { display: false },
            ticks: { font: { size: 11 } },
            border: { display: false },
          },
        },
      },
    });

    const tren = await get('/dashboard/chart/tren-transaksi');
    document.getElementById('chartTren').parentElement.classList.remove('skeleton-box');
    new Chart(document.getElementById('chartTren'), {
      type: 'line',
      data: {
        labels: tren.map((r) => r.tanggal.slice(5)),
        datasets: [
          {
            label: 'Masuk',
            data: tren.map((r) => r.masuk),
            borderColor: palette.ok,
            backgroundColor: 'transparent',
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
            borderWidth: 2,
          },
          {
            label: 'Keluar',
            data: tren.map((r) => r.keluar),
            borderColor: palette.danger,
            backgroundColor: 'transparent',
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
            borderWidth: 2,
          },
        ],
      },
      options: {
        layout: { padding: { top: 4, right: 8, bottom: 0, left: 0 } },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 10, padding: 12, font: { size: 12 } },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8, font: { size: 11 } },
            border: { display: false },
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 11 } },
            grid: { color: palette.grid },
            border: { display: false },
          },
        },
      },
    });
  } catch (e) {
    console.error('Dashboard chart error', e);
  }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
