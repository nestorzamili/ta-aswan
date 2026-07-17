<?php
$loggedIn = session()->get('isLoggedIn');
$homeUrl  = $loggedIn ? site_url('dashboard') : site_url('login');
$homeLabel = $loggedIn ? 'Ke dashboard' : 'Ke halaman login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Permintaan tidak valid · Android Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('css/base.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/components.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/responsive.css') ?>" rel="stylesheet">
</head>
<body class="error-page-body">
<main class="error-page">
    <div class="error-page-card">
        <div class="error-page-icon"><i class="bi bi-exclamation-octagon"></i></div>
        <h1>Permintaan tidak valid</h1>
        <p>
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                Permintaan tidak dapat diproses. Periksa data yang dikirim lalu coba lagi.
            <?php endif; ?>
        </p>
        <a class="btn btn-primary" href="<?= esc($homeUrl) ?>">
            <i class="bi bi-arrow-left"></i> <?= esc($homeLabel) ?>
        </a>
    </div>
</main>
</body>
</html>
