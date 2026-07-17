<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($item); ?>
<a href="<?= site_url('pengguna') ?>" class="page-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar</a>

<div class="card form-card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? site_url('pengguna/' . $item['id_admin']) : site_url('pengguna') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="form-section">
                <h2 class="form-section-title">Profil pengguna</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama <span class="req" title="Wajib">*</span></label>
                        <input type="text" name="nama" id="nama" class="<?= esc(input_class('nama')) ?>"
                               value="<?= esc(old('nama', $item['nama'] ?? '')) ?>"
                               autocomplete="name" placeholder="Nama lengkap" required aria-required="true"
                               <?= aria_invalid_attr('nama') ?>>
                        <?= field_feedback('nama') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username <span class="req" title="Wajib">*</span></label>
                        <input type="text" name="username" id="username" class="<?= esc(input_class('username')) ?>"
                               value="<?= esc(old('username', $item['username'] ?? '')) ?>"
                               autocomplete="username" placeholder="untuk login" required aria-required="true"
                               <?= aria_invalid_attr('username') ?>>
                        <?= field_feedback('username') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email <span class="req" title="Wajib">*</span></label>
                        <input type="email" name="email" id="email" class="<?= esc(input_class('email')) ?>"
                               value="<?= esc(old('email', $item['email'] ?? '')) ?>"
                               autocomplete="email" placeholder="email@contoh.com" required aria-required="true"
                               <?= aria_invalid_attr('email') ?>>
                        <?= field_feedback('email') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nomor_telepon">Telepon</label>
                        <input type="tel" name="nomor_telepon" id="nomor_telepon" class="<?= esc(input_class('nomor_telepon')) ?>"
                               value="<?= esc(old('nomor_telepon', $item['nomor_telepon'] ?? '')) ?>"
                               autocomplete="tel" placeholder="08…">
                        <?= field_feedback('nomor_telepon') ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-section-title">Akses &amp; keamanan</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="level">Level</label>
                        <select name="level" id="level" class="<?= esc(input_class('level', 'form-select')) ?>">
                            <option value="karyawan" <?= old('level', $item['level'] ?? '') === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="admin" <?= old('level', $item['level'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?= field_feedback('level') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="<?= esc(input_class('status', 'form-select')) ?>">
                            <option value="aktif" <?= old('status', $item['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= old('status', $item['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <?= field_feedback('status') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password">
                            Password
                            <?php if ($isEdit): ?>
                                <span class="opt">(opsional)</span>
                            <?php else: ?>
                                <span class="req" title="Wajib">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="password" name="password" id="password" class="<?= esc(input_class('password')) ?>"
                               autocomplete="new-password" minlength="6" <?= $isEdit ? '' : 'required' ?>
                               placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 6 karakter' ?>"
                               <?= aria_invalid_attr('password') ?>>
                        <?= field_feedback('password') ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
                <a href="<?= site_url('pengguna') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
