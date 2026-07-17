<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isAdmin = session('level') === 'admin'; ?>
<div class="page-toolbar">
    <a href="<?= site_url('supplier') ?>" class="page-back mb-0"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar</a>
    <div class="page-toolbar-actions">
        <a href="<?= site_url('supplier/' . $item['id_supplier'] . '/edit') ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="card mb-0">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="kpi-label">Nama supplier</div>
                <div class="fw-semibold fs-5"><?= esc($item['nama_supplier']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="kpi-label">Telepon</div>
                <div class="num fw-semibold"><?= esc($item['telepon'] ?: '—') ?></div>
            </div>
            <div class="col-md-3">
                <div class="kpi-label">Email</div>
                <div class="fw-semibold"><?= esc($item['email'] ?: '—') ?></div>
            </div>
            <div class="col-12">
                <div class="kpi-label">Alamat</div>
                <div><?= esc($item['alamat'] ?: '—') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-card" style="cursor: default;">
        <div class="kpi-icon" aria-hidden="true"><i class="bi bi-box-arrow-in-down"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Transaksi masuk</div>
            <div class="kpi-value"><?= number_format((int) ($stats['jml_trx'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="kpi-card" style="cursor: default;">
        <div class="kpi-icon is-ok" aria-hidden="true"><i class="bi bi-layers"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Total qty</div>
            <div class="kpi-value"><?= number_format((int) ($stats['total_qty'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="kpi-card" style="cursor: default;">
        <div class="kpi-icon is-ink" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Total nilai</div>
            <div class="kpi-value" style="font-size:1.15rem">Rp <?= number_format((float) ($stats['total_nilai'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header card-header-row">
        <span>Transaksi masuk terbaru</span>
        <a class="panel-link" href="<?= site_url('barang-masuk?id_supplier=' . (int) $item['id_supplier']) ?>">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">No. faktur</th>
                <th scope="col">Tanggal</th>
                <th scope="col" class="text-end">Qty</th>
                <th scope="col" class="text-end">Total</th>
                <th scope="col">Oleh</th>
            </tr>
            </thead>
            <tbody>
            <?php $no = 1;

foreach ($recent as $r):
    $url = site_url('barang-masuk/' . $r['id_masuk']);
    ?>
                <tr class="row-link" data-href="<?= esc($url, 'attr') ?>" title="Buka detail transaksi">
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="code"><a class="row-link-main" href="<?= esc($url) ?>"><?= esc($r['no_faktur']) ?></a></td>
                    <td class="num"><?= esc(date('d-m-Y', strtotime($r['tanggal_masuk']))) ?></td>
                    <td class="text-end num"><?= (int) $r['total_quantity'] ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $r['total_harga'], 0, ',', '.') ?></td>
                    <td><?= esc($r['nama_admin'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $recent): ?>
                <?= view('partials/empty_state', [
                    'empty_in_table' => true,
                    'empty_colspan'  => 6,
                    'empty_icon'     => 'bi-inbox',
                    'empty_title'    => 'Belum ada transaksi',
                    'empty_text'     => 'Supplier ini belum dipakai di barang masuk.',
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
