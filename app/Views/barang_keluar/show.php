<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-toolbar">
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= site_url('barang-keluar') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <a href="<?= site_url('barang-keluar/' . $header['id_keluar'] . '/pdf') ?>" class="btn btn-primary" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
        </a>
        <?php if (session('level') === 'admin'): ?>
            <a href="<?= site_url('barang-keluar/' . $header['id_keluar'] . '/edit') ?>" class="btn btn-outline-primary">Edit</a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-label">No. transaksi</div>
                <div class="code fw-semibold"><?= esc($header['no_transaksi']) ?></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-label">Tanggal</div>
                <div class="num fw-semibold"><?= esc(date('d-m-Y', strtotime($header['tanggal_keluar']))) ?></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-label">Tujuan</div>
                <div class="fw-semibold"><?= esc($header['tujuan']) ?></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-label">Dicatat oleh</div>
                <div class="fw-semibold"><?= esc($header['nama_admin']) ?></div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/transaction_detail_table', ['details' => $details, 'total_harga' => $header['total_harga']]) ?>
<?= $this->endSection() ?>
