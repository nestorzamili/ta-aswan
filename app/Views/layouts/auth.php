<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login') ?> · Android Service</title>
    <meta name="description" content="Login sistem inventory sparepart dan aksesoris Toko Android Service.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📱</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link href="<?= base_url('css/base.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/components.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/forms.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/auth-charts.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/responsive.css') ?>" rel="stylesheet">
</head>
<body class="is-auth">
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-head">
            <div class="mark" aria-hidden="true"><i class="bi bi-phone-flip"></i></div>
            <h1>Android Service</h1>
            <p>Sistem inventory sparepart &amp; aksesoris</p>
        </div>
        <div class="auth-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
        <div class="auth-foot">
            Toko Android Service · Teluk Dalam
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= base_url('js/ui-feedback.js') ?>"></script>
</body>
</html>
