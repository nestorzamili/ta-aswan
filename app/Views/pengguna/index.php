<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $hasFilter = $q !== '' || $level !== '' || $status !== ''; ?>
<div class="page-toolbar">
    <form class="filters filters-inline" method="get">
        <div class="field-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= esc($q) ?>" class="form-control"
                   placeholder="Cari nama, username, email, telepon…"
                   aria-label="Cari pengguna" autocomplete="off">
        </div>
        <select name="level" class="form-select" aria-label="Filter level" onchange="this.form.submit()">
            <option value="">Semua level</option>
            <option value="admin" <?= $level === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="karyawan" <?= $level === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
        </select>
        <select name="status" class="form-select" aria-label="Filter status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </form>
    <div class="page-toolbar-actions">
        <?php if ($hasFilter): ?>
            <a href="<?= site_url('pengguna') ?>" class="btn btn-outline-secondary btn-reset-filter"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
        <a href="<?= site_url('pengguna/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah pengguna</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">Nama</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Telepon</th>
                <th scope="col">Level</th>
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
                    <td><?= esc($row['nama']) ?></td>
                    <td class="code"><?= esc($row['username']) ?></td>
                    <td><?= esc($row['email']) ?></td>
                    <td class="num"><?= esc($row['nomor_telepon']) ?></td>
                    <td><span class="badge badge-jenis"><?= esc($row['level']) ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'aktif'): ?>
                            <span class="badge status-aman">aktif</span>
                        <?php else: ?>
                            <span class="badge status-habis">nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="dropdown table-actions">
                            <button class="btn-action-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end action-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('pengguna/' . $row['id_admin'] . '/edit') ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form class="dropdown-item-form" method="post" action="<?= site_url('pengguna/' . $row['id_admin'] . '/delete') ?>" data-confirm="Hapus pengguna ini?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $items): ?>
                <?= view('partials/empty_state', [
                    'empty_in_table' => true,
                    'empty_colspan'  => 8,
                    'empty_icon'     => 'bi-people',
                    'empty_title'    => $hasFilter ? 'Tidak ada hasil' : 'Belum ada pengguna',
                    'empty_text'     => $hasFilter
                        ? 'Coba ubah kata kunci atau filter, lalu cari lagi.'
                        : 'Tambah akun admin atau karyawan untuk akses sistem.',
                    'empty_action_url'   => $hasFilter ? site_url('pengguna') : site_url('pengguna/create'),
                    'empty_action_label' => $hasFilter ? 'Reset filter' : 'Tambah pengguna',
                ], ['saveData' => false]) ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (! empty($pager)): ?><?= $pager->links() ?><?php endif; ?>
<?= $this->endSection() ?>
