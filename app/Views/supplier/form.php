<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($item); ?>
<a href="<?= site_url($isEdit ? 'supplier/' . $item['id_supplier'] : 'supplier') ?>" class="page-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= $isEdit ? 'Kembali ke detail' : 'Kembali ke daftar' ?>
</a>

<div class="card form-card content-narrow">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? site_url('supplier/' . $item['id_supplier']) : site_url('supplier') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="form-section">
                <h2 class="form-section-title">Identitas supplier</h2>
                <div class="mb-3">
                    <label class="form-label" for="nama_supplier">Nama supplier <span class="req" title="Wajib">*</span></label>
                    <input type="text" name="nama_supplier" id="nama_supplier" class="<?= esc(input_class('nama_supplier')) ?>"
                           value="<?= esc(old('nama_supplier', $item['nama_supplier'] ?? '')) ?>"
                           placeholder="Nama perusahaan / toko" autocomplete="organization"
                           maxlength="100" required aria-required="true" autofocus
                           <?= aria_invalid_attr('nama_supplier') ?>>
                    <?= field_feedback('nama_supplier') ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea name="alamat" id="alamat" class="<?= esc(input_class('alamat')) ?>" rows="2"
                              placeholder="Alamat lengkap (opsional)"
                              <?= aria_invalid_attr('alamat') ?>><?= esc(old('alamat', $item['alamat'] ?? '')) ?></textarea>
                    <?= field_feedback('alamat') ?>
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-section-title">Kontak</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="telepon">Telepon <span class="opt">(opsional)</span></label>
                        <input type="tel" name="telepon" id="telepon" class="<?= esc(input_class('telepon')) ?>"
                               value="<?= esc(old('telepon', $item['telepon'] ?? '')) ?>"
                               placeholder="08…" autocomplete="tel" maxlength="20"
                               <?= aria_invalid_attr('telepon') ?>>
                        <?= field_feedback('telepon') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email <span class="opt">(opsional)</span></label>
                        <input type="email" name="email" id="email" class="<?= esc(input_class('email')) ?>"
                               value="<?= esc(old('email', $item['email'] ?? '')) ?>"
                               placeholder="email@contoh.com" autocomplete="email" maxlength="100"
                               <?= aria_invalid_attr('email') ?>>
                        <?= field_feedback('email') ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
                <a href="<?= site_url($isEdit ? 'supplier/' . $item['id_supplier'] : 'supplier') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
