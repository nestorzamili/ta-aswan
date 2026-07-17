<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$hasFilter   = $q !== '' || ($tanggal_awal ?? '') !== '' || ($tanggal_akhir ?? '') !== '';
$filterCount = (int) (($tanggal_awal ?? '') !== '') + (int) (($tanggal_akhir ?? '') !== '');
$routePrefix = 'barang-keluar/';
?>
<div class="page-toolbar">
    <form method="get" class="filters filters-inline has-filter-panel">
        <div class="filters-primary">
            <div class="field-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="q" value="<?= esc($q) ?>" class="form-control"
                       placeholder="Cari no. transaksi atau tujuan…" aria-label="Cari transaksi" autocomplete="off">
            </div>
            <button type="button" class="btn btn-outline-secondary btn-filter-toggle"
                    data-bs-toggle="collapse" data-bs-target="#filterPanelKeluar"
                    aria-expanded="<?= $filterCount ? 'true' : 'false' ?>" aria-controls="filterPanelKeluar">
                <i class="bi bi-funnel" aria-hidden="true"></i> Filter
                <?php if ($filterCount): ?><span class="filter-badge"><?= $filterCount ?></span><?php endif; ?>
            </button>
        </div>
        <div class="collapse filter-panel<?= $filterCount ? ' show' : '' ?>" id="filterPanelKeluar">
            <fieldset class="date-range border-0 p-0 m-0">
                <legend class="visually-hidden">Filter periode</legend>
                <input type="text" name="tanggal_awal" value="<?= esc($tanggal_awal ?? '') ?>"
                       class="form-control input-date" placeholder="DD-MM-YYYY"
                       aria-label="Tanggal dari" autocomplete="off" readonly>
                <span class="date-sep" aria-hidden="true">–</span>
                <input type="text" name="tanggal_akhir" value="<?= esc($tanggal_akhir ?? '') ?>"
                       class="form-control input-date" placeholder="DD-MM-YYYY"
                       aria-label="Tanggal sampai" autocomplete="off" readonly>
            </fieldset>
        </div>
    </form>
    <div class="page-toolbar-actions">
        <?php if ($hasFilter): ?>
            <a href="<?= site_url('barang-keluar') ?>" class="btn btn-outline-secondary btn-reset-filter"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
        <a href="<?= site_url('barang-keluar/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">No. transaksi</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Tujuan</th>
                <th scope="col" class="text-end">Item</th>
                <th scope="col" class="text-end">Qty</th>
                <th scope="col" class="text-end">Total</th>
                <th scope="col">Oleh</th>
                <th scope="col" class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
if (! empty($pager)) {
    $no = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1;
}

foreach ($items as $row):
    $detailUrl = site_url($routePrefix . $row['id_keluar']);
    ?>
                <tr class="row-link" data-href="<?= esc($detailUrl, 'attr') ?>" title="Buka detail">
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="code">
                        <a class="row-link-main" href="<?= esc($detailUrl) ?>"><?= esc($row['no_transaksi']) ?></a>
                    </td>
                    <td class="num"><?= esc(date('d-m-Y', strtotime($row['tanggal_keluar']))) ?></td>
                    <td><?= esc($row['tujuan']) ?></td>
                    <td class="text-end num"><?= (int) $row['total_item'] ?></td>
                    <td class="text-end num"><?= (int) $row['total_quantity'] ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $row['total_harga'], 0, ',', '.') ?></td>
                    <td class=""><?= esc($row['nama_admin']) ?></td>
                    <td class="text-end">
                        <div class="dropdown table-actions">
                            <button class="btn-action-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end action-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url($routePrefix . $row['id_keluar']) ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= site_url($routePrefix . $row['id_keluar'] . '/pdf') ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                                    </a>
                                </li>
                                <?php if (session('level') === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= site_url($routePrefix . $row['id_keluar'] . '/edit') ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form class="dropdown-item-form" method="post" action="<?= site_url($routePrefix . $row['id_keluar'] . '/delete') ?>" data-confirm="Hapus transaksi? Stok akan dikembalikan.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $items): ?>
                <?= view('partials/empty_state', [
                    'empty_in_table'     => true,
                    'empty_colspan'      => 9,
                    'empty_icon'         => 'bi-box-arrow-up',
                    'empty_title'        => 'Belum ada barang keluar',
                    'empty_text'         => 'Catat penjualan atau pemakaian barang di sini.',
                    'empty_action_url'   => site_url('barang-keluar/create'),
                    'empty_action_label' => 'Tambah barang keluar',
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (! empty($pager)): ?><?= $pager->links() ?><?php endif; ?>
<?= $this->endSection() ?>
