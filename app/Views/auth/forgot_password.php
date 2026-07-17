<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<p class="small text-muted mb-3">Masukkan email Anda yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</p>
<form method="post" action="<?= site_url('lupa-password') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" name="email" id="email" class="form-control" required autocomplete="email"
               placeholder="nama@contoh.com">
    </div>
    <button class="btn btn-primary" type="submit">Kirim tautan reset</button>
    <div class="auth-links">
        <a href="<?= site_url('login') ?>">← Kembali ke login</a>
    </div>
</form>
<?= $this->endSection() ?>
