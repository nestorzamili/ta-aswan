<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<form method="post" action="<?= site_url('login') ?>" autocomplete="on" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="username">Username atau Email</label>
        <input type="text" name="username" id="username" class="<?= esc(input_class('username')) ?>"
               value="<?= esc(old('username')) ?>" autofocus
               autocomplete="username" placeholder="username atau email@domain.com" aria-required="true"
               <?= field_is_invalid('username') ? 'aria-invalid="true"' : '' ?>>
        <?= field_feedback('username') ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group <?= field_is_invalid('password') ? 'is-invalid' : '' ?>">
            <input type="password" name="password" id="password" class="<?= esc(input_class('password')) ?>"
                   autocomplete="current-password" placeholder="••••••••" aria-required="true"
                   <?= field_is_invalid('password') ? 'aria-invalid="true"' : '' ?>>
            <button class="btn btn-outline-secondary" type="button" id="togglePw" aria-label="Tampilkan password">
                <i class="bi bi-eye" id="pwIcon"></i>
            </button>
        </div>
        <?= field_feedback('password') ?>
    </div>
    <button class="btn btn-primary" type="submit">Masuk</button>
    <div class="auth-links">
        <a href="<?= site_url('lupa-password') ?>">Lupa password?</a>
    </div>
</form>
<script>
document.getElementById('togglePw')?.addEventListener('click', () => {
  const i = document.getElementById('password');
  const icon = document.getElementById('pwIcon');
  const show = i.type === 'password';
  i.type = show ? 'text' : 'password';
  icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
<?= $this->endSection() ?>
