<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<a href="<?= site_url('dashboard') ?>" class="page-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke dashboard</a>

<?php
$fotoUrl    = avatar_url($item['foto'] ?? null);
$initial    = mb_strtoupper(mb_substr((string) ($item['nama'] ?? '?'), 0, 1));
$levelTxt   = ($item['level'] ?? '') === 'admin' ? 'Administrator' : 'Karyawan';
$dataErrors = field_is_invalid('nama') || field_is_invalid('email') || field_is_invalid('nomor_telepon');
?>

<div class="profile-layout">
    <aside class="card profile-side">
        <div class="card-body">
            <form method="post" action="<?= site_url('profil/foto') ?>" enctype="multipart/form-data" id="formFoto" class="profile-side-photo">
                <?= csrf_field() ?>
                <button type="button" class="profile-avatar-edit" id="avatarTrigger" aria-label="Ubah foto profil" title="Klik untuk ubah foto">
                    <span class="profile-avatar">
                        <?php if ($fotoUrl !== null): ?>
                            <img src="<?= esc($fotoUrl) ?>" alt="Foto profil">
                        <?php else: ?>
                            <span class="profile-avatar-initial"><?= esc($initial) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="profile-avatar-cam" aria-hidden="true"><i class="bi bi-camera-fill"></i></span>
                </button>
                <input type="file" name="foto" id="fotoInput" class="d-none" accept="image/jpeg,image/png,image/webp">
            </form>

            <h2 class="profile-side-name"><?= esc($item['nama'] ?? '-') ?></h2>
            <span class="badge profile-badge"><i class="bi bi-shield-check" aria-hidden="true"></i> <?= esc($levelTxt) ?></span>

            <ul class="profile-side-list">
                <li><i class="bi bi-person-badge" aria-hidden="true"></i> <span><?= esc($item['username'] ?? '-') ?></span></li>
                <li><i class="bi bi-envelope" aria-hidden="true"></i> <span><?= esc($item['email'] ?? '-') ?></span></li>
                <li><i class="bi bi-telephone" aria-hidden="true"></i> <span><?= esc($item['nomor_telepon'] ?? '') ?: '—' ?></span></li>
            </ul>

            <?php if ($fotoUrl !== null): ?>
                <form method="post" action="<?= site_url('profil/foto/hapus') ?>" id="formHapusFoto">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash" aria-hidden="true"></i> Hapus foto</button>
                </form>
            <?php endif; ?>
            <?php if (field_is_invalid('foto')): ?>
                <div class="invalid-feedback d-block mt-2"><?= esc(field_error('foto')) ?></div>
            <?php endif; ?>
        </div>
    </aside>

    <section class="card profile-main">
        <div class="card-body">
            <div class="profile-section">
                <div class="profile-tab-head">
                    <div>
                        <h3 class="profile-tab-title"><i class="bi bi-person-vcard" aria-hidden="true"></i> Data diri</h3>
                        <p class="profile-tab-desc">Nama, email, dan nomor telepon Anda.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="editDataBtn">
                        <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                    </button>
                </div>

                <form method="post" action="<?= site_url('profil') ?>" class="needs-validation profile-form <?= $dataErrors ? '' : 'is-locked' ?>"
                      id="formData" novalidate>
                    <?= csrf_field() ?>
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
                            <label class="form-label" for="username">Username</label>
                            <input type="text" id="username" class="form-control" value="<?= esc($item['username'] ?? '') ?>" disabled>
                            <div class="form-text">Username tidak dapat diubah.</div>
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
                    <div class="profile-panel-actions" id="dataActions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i> Simpan Perubahan</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelDataBtn">Batal</button>
                    </div>
                </form>
            </div>

            <hr class="profile-divider">

            <div class="profile-section">
                <div class="profile-tab-head">
                    <div>
                        <h3 class="profile-tab-title"><i class="bi bi-shield-lock" aria-hidden="true"></i> Keamanan</h3>
                        <p class="profile-tab-desc">Verifikasi password lama sebelum menyimpan yang baru.</p>
                    </div>
                </div>

                <form method="post" action="<?= site_url('profil/password') ?>" class="needs-validation profile-form" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="password_lama">Password lama <span class="req" title="Wajib">*</span></label>
                            <input type="password" name="password_lama" id="password_lama" class="<?= esc(input_class('password_lama')) ?>"
                                   autocomplete="current-password" required aria-required="true"
                                   placeholder="Password saat ini" <?= aria_invalid_attr('password_lama') ?>>
                            <?= field_feedback('password_lama') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password baru <span class="req" title="Wajib">*</span></label>
                            <input type="password" name="password" id="password" class="<?= esc(input_class('password')) ?>"
                                   autocomplete="new-password" minlength="6" required aria-required="true"
                                   placeholder="Min. 6 karakter" <?= aria_invalid_attr('password') ?>>
                            <?= field_feedback('password') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirm">Konfirmasi <span class="req" title="Wajib">*</span></label>
                            <input type="password" name="password_confirm" id="password_confirm" class="<?= esc(input_class('password_confirm')) ?>"
                                   autocomplete="new-password" minlength="6" required aria-required="true"
                                   placeholder="Ulangi password baru" <?= aria_invalid_attr('password_confirm') ?>>
                            <?= field_feedback('password_confirm') ?>
                        </div>
                    </div>
                    <div class="profile-panel-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-key" aria-hidden="true"></i> Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  const trigger = document.getElementById('avatarTrigger');
  const input = document.getElementById('fotoInput');
  const form = document.getElementById('formFoto');
  if (trigger && input && form) {
    trigger.addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
      if (input.files && input.files.length > 0) form.submit();
    });
  }

  const formData = document.getElementById('formData');
  const editBtn = document.getElementById('editDataBtn');
  const cancelBtn = document.getElementById('cancelDataBtn');
  const setLocked = (locked) => {
    if (!formData) return;
    formData.classList.toggle('is-locked', locked);
    if (editBtn) editBtn.classList.toggle('d-none', !locked);
    if (!locked) {
      const first = formData.querySelector('input:not([disabled])');
      if (first) first.focus();
    }
  };
  if (editBtn) editBtn.addEventListener('click', () => setLocked(false));
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      if (formData) formData.reset();
      setLocked(true);
    });
  }
})();
</script>
<?= $this->endSection() ?>
