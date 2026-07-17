<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$hasFilter = $q !== '' || $kategori !== '' || $merk !== '' || $status !== '';
$route     = $cfg['route'];
$label     = $cfg['label'];
$pk        = $cfg['pk'];
$kodeCol   = $cfg['kode'];
$namaCol   = $cfg['nama'];
$icon      = $cfg['icon'] ?? 'bi-box';
?>
<div class="page-toolbar">
    <form class="filters" method="get" id="filterForm">
        <div class="field-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" id="f-q" value="<?= esc($q) ?>"
                   class="form-control" placeholder="Cari nama atau kode…"
                   aria-label="Cari nama atau kode" autocomplete="off">
        </div>
        <select name="kategori" id="f-kategori" class="form-select" aria-label="Filter kategori" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            <?php foreach ($kategoris as $k): ?>
                <option value="<?= esc($k) ?>" <?= $kategori === $k ? 'selected' : '' ?>><?= esc($k) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="merk" id="f-merk" class="form-select" aria-label="Filter merk" onchange="this.form.submit()">
            <option value="">Semua merk</option>
            <?php foreach ($merks as $m): ?>
                <option value="<?= esc($m) ?>" <?= $merk === $m ? 'selected' : '' ?>><?= esc($m) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status_stok" id="f-status" class="form-select" aria-label="Filter status stok" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <?php foreach (['aman', 'rendah', 'habis'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="page-toolbar-actions">
        <?php if ($hasFilter): ?>
            <a href="<?= site_url($route) ?>" class="btn btn-outline-secondary btn-reset-filter"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
        <a href="<?= site_url($route . '/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">Kode</th>
                <th scope="col">Manual</th>
                <th scope="col">Nama</th>
                <th scope="col">Kategori</th>
                <th scope="col">Merk</th>
                <th scope="col" class="text-end">Harga beli</th>
                <th scope="col" class="text-end">Harga jual</th>
                <th scope="col" class="text-end">Stok</th>
                <th scope="col">Status</th>
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
    ?>
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="code"><?= esc($row[$kodeCol]) ?></td>
                    <td class="code text-muted"><?= esc($row['kode_manual'] ?? '—') ?></td>
                    <td><?= esc($row[$namaCol]) ?></td>
                    <td><?= esc($row['kategori']) ?></td>
                    <td><?= esc($row['merk']) ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $row['harga_beli'], 0, ',', '.') ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $row['harga_jual'], 0, ',', '.') ?></td>
                    <td class="text-end num"><?= (int) $row['stok'] ?></td>
                    <td><span class="badge status-<?= esc($row['status_stok']) ?>"><?= esc(ucfirst((string) $row['status_stok'])) ?></span></td>
                    <td class="text-end">
                        <div class="dropdown table-actions">
                            <button class="btn-action-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end action-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url($route . '/' . $row[$pk] . '/edit') ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </li>
                                <?php if (session('level') === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form class="dropdown-item-form" method="post"
                                              action="<?= site_url($route . '/' . $row[$pk] . '/delete') ?>"
                                              data-confirm="Hapus <?= esc(strtolower($label), 'attr') ?> ini?">
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
                    'empty_colspan'      => 11,
                    'empty_icon'         => $icon,
                    'empty_title'        => $hasFilter ? 'Tidak ada ' . strtolower($label) . ' yang cocok' : 'Belum ada ' . strtolower($label),
                    'empty_text'         => $hasFilter ? 'Coba ubah filter atau reset pencarian.' : 'Tambah data pertama untuk mulai mengelola stok.',
                    'empty_action_url'   => $hasFilter ? null : site_url($route . '/create'),
                    'empty_action_label' => $hasFilter ? null : 'Tambah ' . strtolower($label),
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pager): ?><?= $pager->links() ?><?php endif; ?>
<?= $this->endSection() ?>
