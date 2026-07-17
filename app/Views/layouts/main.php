<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?> · Android Service</title>
    <meta name="description" content="Sistem inventory sparepart dan aksesoris Toko Android Service — monitoring stok, transaksi, dan laporan.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📱</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet" integrity="sha384-RkASv+6KfBMW9eknReJIJ6b3UnjKOKC5bOUaNgIY778NFbQ8MtWq9Lr/khUgqtTt" crossorigin="anonymous">
    <link href="<?= base_url('css/base.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/components.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/forms.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/tables.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/auth-charts.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/responsive.css') ?>" rel="stylesheet">
    <?= $this->renderSection('head') ?>
</head>
<?php
$flashSuccess     = session()->getFlashdata('success');
    $flashError   = session()->getFlashdata('error');
    $flashWarning = session()->getFlashdata('warning');
    $flashInfo    = session()->getFlashdata('info');
    ?>
<body
    <?php if ($flashSuccess): ?>data-flash-success="<?= esc($flashSuccess, 'attr') ?>"<?php endif; ?>
    <?php if ($flashError): ?>data-flash-error="<?= esc($flashError, 'attr') ?>"<?php endif; ?>
    <?php if ($flashWarning): ?>data-flash-warning="<?= esc($flashWarning, 'attr') ?>"<?php endif; ?>
    <?php if ($flashInfo): ?>data-flash-info="<?= esc($flashInfo, 'attr') ?>"<?php endif; ?>
>
<script>
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>
<?php
    $level    = session('level');
    $uri      = service('uri')->getPath();
    $isActive = static function (string $path) use ($uri): string {
        $u = ltrim($uri, '/');
        $p = ltrim($path, '/');
        if ($p === 'dashboard') {
            return $u === 'dashboard' || $u === '' ? 'active' : '';
        }

        return str_starts_with($u, $p) ? 'active' : '';
    };
    $initials = mb_strtoupper(mb_substr((string) session('nama'), 0, 1));
    ?>
<button class="menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNav" aria-controls="sidebarNav" aria-expanded="false">
    <i class="bi bi-list"></i> Menu navigasi
</button>

<aside class="sidebar collapse" id="sidebarNav">
    <a class="brand" href="<?= site_url('dashboard') ?>" data-tooltip="Android Service">
        <span class="brand-mark" aria-hidden="true"><i class="bi bi-phone-flip"></i></span>
        <span class="brand-text">
            <strong>Android Service</strong>
            <small>Inventory Toko</small>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu utama">
        <div class="nav-section"><span>Utama</span></div>
        <a class="nav-link <?= $isActive('dashboard') ?>" href="<?= site_url('dashboard') ?>" data-tooltip="Dashboard">
            <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
        </a>

        <div class="nav-section"><span>Master data</span></div>
        <a class="nav-link <?= $isActive('sparepart') ?>" href="<?= site_url('sparepart') ?>" data-tooltip="Sparepart">
            <i class="bi bi-cpu"></i> <span>Sparepart</span>
        </a>
        <a class="nav-link <?= $isActive('aksesoris') ?>" href="<?= site_url('aksesoris') ?>" data-tooltip="Aksesoris">
            <i class="bi bi-earbuds"></i> <span>Aksesoris</span>
        </a>
        <a class="nav-link <?= $isActive('supplier') ?>" href="<?= site_url('supplier') ?>" data-tooltip="Supplier">
            <i class="bi bi-building"></i> <span>Supplier</span>
        </a>

        <div class="nav-section"><span>Transaksi</span></div>
        <a class="nav-link <?= $isActive('barang-masuk') ?>" href="<?= site_url('barang-masuk') ?>" data-tooltip="Barang masuk">
            <i class="bi bi-box-arrow-in-down"></i> <span>Barang masuk</span>
        </a>
        <a class="nav-link <?= $isActive('barang-keluar') ?>" href="<?= site_url('barang-keluar') ?>" data-tooltip="Barang keluar">
            <i class="bi bi-box-arrow-up"></i> <span>Barang keluar</span>
        </a>
        <a class="nav-link <?= $isActive('stok') ?>" href="<?= site_url('stok') ?>" data-tooltip="Monitoring stok">
            <i class="bi bi-layers"></i> <span>Monitoring stok</span>
        </a>

        <div class="nav-section"><span>Lainnya</span></div>
        <a class="nav-link <?= $isActive('laporan') ?>" href="<?= site_url('laporan') ?>" data-tooltip="Laporan">
            <i class="bi bi-printer"></i> <span>Laporan</span>
        </a>
        <?php if ($level === 'admin'): ?>
            <a class="nav-link <?= $isActive('pengguna') ?>" href="<?= site_url('pengguna') ?>" data-tooltip="Pengguna">
                <i class="bi bi-people"></i> <span>Pengguna</span>
            </a>
        <?php endif; ?>
    </nav>

    <form action="<?= site_url('logout') ?>" method="POST" class="nav-logout-form mt-auto">
        <?= csrf_field() ?>
        <button type="submit" class="nav-link nav-logout border-0 bg-transparent w-100 text-start" data-tooltip="Keluar">
            <i class="bi bi-box-arrow-left"></i> <span>Keluar</span>
        </button>
    </form>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn-action-menu d-none d-md-flex align-items-center justify-content-center border-0 bg-transparent" id="desktopMenuToggle" aria-label="Toggle Menu" style="color: var(--ink);">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h1><?= esc($title ?? 'Dashboard') ?></h1>
        </div>
        <div class="topbar-meta">
            <span class="topbar-clock" id="clock" title="Waktu server browser"><?= date('d M Y · H:i') ?></span>
            <span class="user-chip">
                <span class="avatar" aria-hidden="true"><?= esc($initials) ?></span>
                <span><?= esc(session('nama')) ?></span>
            </span>
        </div>
    </header>

    <main class="content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="app-footer">
        Inventory sparepart &amp; aksesoris · Toko Android Service · <?= date('Y') ?>
    </footer>
</div>

<div id="toast-root" aria-live="polite" aria-atomic="true"></div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-6" id="confirmModalLabel">Konfirmasi</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="confirmModalMessage">Lanjutkan tindakan ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmModalOk">Ya, lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" integrity="sha384-5JqMv4L/Xa0hfvtF06qboNdhvuYXUku9ZrhZh3bSk8VXF0A/RuSLHpLsSV9Zqhl6" crossorigin="anonymous"></script>
<script src="<?= base_url('js/datepicker.js') ?>"></script>
<script src="<?= base_url('js/money.js') ?>"></script>
<script src="<?= base_url('js/ui-feedback.js') ?>"></script>
<script src="<?= base_url('js/form-validation.js') ?>"></script>
<script>
(function () {
  const el = document.getElementById('clock');
  if (!el) return;
  const tick = () => {
    const d = new Date();
    const date = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    el.textContent = date + ' · ' + time;
  };
  tick();
  setInterval(tick, 1000);
})();

document.querySelectorAll('.btn-action-menu').forEach((btn) => {
  bootstrap.Dropdown.getOrCreateInstance(btn, {
    autoClose: true,
    popperConfig(defaultConfig) {
      return {
        ...defaultConfig,
        strategy: 'fixed',
        modifiers: [
          ...(defaultConfig.modifiers || []).filter((m) => m.name !== 'preventOverflow' && m.name !== 'offset'),
          { name: 'preventOverflow', options: { boundary: 'viewport', padding: 8 } },
          { name: 'offset', options: { offset: [0, 4] } },
        ],
      };
    },
  });
});

const desktopToggle = document.getElementById('desktopMenuToggle');
if (desktopToggle) {
  desktopToggle.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
    
    // Hide open tooltips when toggling
    document.querySelectorAll('[data-tooltip]').forEach(el => {
      const instance = bootstrap.Tooltip.getInstance(el);
      if (instance) instance.hide();
    });
  });
}

// Initialize Bootstrap Tooltips for sidebar items (only active when collapsed)
document.querySelectorAll('[data-tooltip]').forEach(el => {
  new bootstrap.Tooltip(el, {
    title: function() {
      return document.body.classList.contains('sidebar-collapsed') ? el.getAttribute('data-tooltip') : '';
    },
    placement: 'right',
    trigger: 'hover'
  });
});
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
