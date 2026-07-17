<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<form method="post" action="<?= site_url('reset-password/' . $token) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="password">Password baru</label>
        <input type="password" name="password" id="password" class="<?= esc(input_class('password')) ?>"
               autocomplete="new-password" aria-required="true"
               <?= field_is_invalid('password') ? 'aria-invalid="true"' : '' ?>>
        <?= field_feedback('password') ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password_confirm">Ulangi password</label>
        <input type="password" name="password_confirm" id="password_confirm" class="<?= esc(input_class('password_confirm')) ?>"
               autocomplete="new-password" aria-required="true"
               <?= field_is_invalid('password_confirm') ? 'aria-invalid="true"' : '' ?>>
        <?= field_feedback('password_confirm') ?>
    </div>
    <button class="btn btn-primary" type="submit">Simpan password</button>
</form>
<?= $this->endSection() ?>
