<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $hasFilter = $q !== '' || $status !== ''; ?>
<div class="page-toolbar">
    <form class="filters" method="get">
        <div class="field-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= esc($q) ?>" class="form-control"
                   placeholder="Cari nama atau kode…" aria-label="Cari nama atau kode" autocomplete="off">
        </div>
        <select name="status_stok" class="form-select" aria-label="Filter status stok" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="kritis" <?= $status === 'kritis' ? 'selected' : '' ?>>Kritis (rendah + habis)</option>
            <?php foreach (['aman', 'rendah', 'habis'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($hasFilter): ?>
        <div class="page-toolbar-actions">
            <a href="<?= site_url('stok') ?>" class="btn btn-outline-secondary btn-reset-filter"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">Tipe</th>
                <th scope="col">Kode</th>
                <th scope="col">Nama</th>
                <th scope="col">Kategori</th>
                <th scope="col">Merk</th>
                <th scope="col" class="text-end">Stok</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php $no = ($page - 1) * $perPage + 1;

foreach ($items as $r): ?>
                <?php
    $editUrl = $r['tipe'] === 'aksesoris'
        ? site_url('aksesoris/' . $r['id'] . '/edit')
        : site_url('sparepart/' . $r['id'] . '/edit');
    ?>
                <tr class="row-link" title="Buka edit barang">
                    <td class="col-no"><?= $no++ ?></td>
                    <td><span class="badge badge-jenis"><?= esc($r['tipe']) ?></span></td>
                    <td class="code"><?= esc($r['kode']) ?></td>
                    <td><a class="row-link-main" href="<?= esc($editUrl) ?>" aria-label="Edit <?= esc($r['nama'], 'attr') ?>"><?= esc($r['nama']) ?></a></td>
                    <td><?= esc($r['kategori']) ?></td>
                    <td><?= esc($r['merk']) ?></td>
                    <td class="text-end num"><?= (int) $r['stok'] ?></td>
                    <td><span class="badge status-<?= esc($r['status_stok']) ?>"><?= esc(ucfirst((string) $r['status_stok'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $items): ?>
                <?= view('partials/empty_state', [
                    'empty_in_table' => true,
                    'empty_colspan'  => 8,
                    'empty_icon'     => 'bi-layers',
                    'empty_title'    => 'Tidak ada data stok',
                    'empty_text'     => 'Belum ada sparepart atau aksesoris, atau filter tidak cocok.',
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pager): ?>
    <?= $pager ?>
<?php endif; ?>
<?= $this->endSection() ?>
