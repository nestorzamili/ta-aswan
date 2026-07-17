<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $hasFilter = $q !== ''; ?>
<div class="page-toolbar">
    <form class="filters" method="get">
        <div class="field-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= esc($q) ?>" class="form-control"
                   placeholder="Cari nama, alamat, telepon, email…"
                   aria-label="Cari supplier" autocomplete="off">
        </div>
    </form>
    <div class="page-toolbar-actions">
        <?php if ($hasFilter): ?>
            <a href="<?= site_url('supplier') ?>" class="btn btn-outline-secondary btn-reset-filter"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
        <a href="<?= site_url('supplier/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">Nama</th>
                <th scope="col">Kontak</th>
                <th scope="col">Alamat</th>
                <th scope="col" class="text-end">Transaksi</th>
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
    $detailUrl = site_url('supplier/' . $row['id_supplier']);
    $trxCount  = (int) ($row['trx_count'] ?? 0);
    ?>
                <tr class="row-link" data-href="<?= esc($detailUrl, 'attr') ?>" title="Buka detail supplier">
                    <td class="col-no"><?= $no++ ?></td>
                    <td>
                        <a class="row-link-main" href="<?= esc($detailUrl) ?>"><?= esc($row['nama_supplier']) ?></a>
                    </td>
                    <td>
                        <?php if (! empty($row['telepon']) || ! empty($row['email'])): ?>
                            <?php if (! empty($row['telepon'])): ?>
                                <div class="num"><?= esc($row['telepon']) ?></div>
                            <?php endif; ?>
                            <?php if (! empty($row['email'])): ?>
                                <div class="text-muted small"><?= esc($row['email']) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= esc($row['alamat'] ?: '—') ?></td>
                    <td class="text-end">
                        <?php if ($trxCount > 0): ?>
                            <span class="badge badge-jenis"><?= $trxCount ?> masuk</span>
                        <?php else: ?>
                            <span class="text-muted">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="dropdown table-actions">
                            <button class="btn-action-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end action-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= esc($detailUrl) ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('supplier/' . $row['id_supplier'] . '/edit') ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </li>
                                <?php if (session('level') === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form class="dropdown-item-form" method="post" action="<?= site_url('supplier/' . $row['id_supplier'] . '/delete') ?>"
                                              data-confirm="<?= $trxCount > 0
                                                  ? 'Supplier ini punya riwayat transaksi dan tidak dapat dihapus. Lanjutkan coba hapus?'
                                                  : 'Hapus supplier ini?' ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger" <?= $trxCount > 0 ? 'disabled title="Sudah dipakai transaksi"' : '' ?>>
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
                    'empty_in_table' => true,
                    'empty_colspan'  => 6,
                    'empty_icon'     => 'bi-building',
                    'empty_title'    => $hasFilter ? 'Tidak ada hasil' : 'Belum ada supplier',
                    'empty_text'     => $hasFilter
                        ? 'Coba ubah kata kunci pencarian.'
                        : 'Tambah supplier untuk transaksi barang masuk.',
                    'empty_action_url' => $hasFilter
                        ? site_url('supplier')
                        : (session('level') === 'admin' ? site_url('supplier/create') : null),
                    'empty_action_label' => $hasFilter
                        ? 'Reset pencarian'
                        : (session('level') === 'admin' ? 'Tambah supplier' : null),
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pager): ?><?= $pager->links() ?><?php endif; ?>
<?= $this->endSection() ?>
