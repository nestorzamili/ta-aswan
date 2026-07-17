<?php

use Config\Store;

/** @var Store $store */
/** @var string $docTitle */
$store ??= config(Store::class);
$docTitle ??= 'Dokumen';
?>
<div class="pdf-header">
    <p class="pdf-brand"><?= esc($store->name) ?></p>
    <?php if (trim($store->tagline) !== ''): ?>
        <p class="pdf-tagline"><?= esc($store->tagline) ?></p>
    <?php endif; ?>
    <?php if ($store->contactLine() !== ''): ?>
        <p class="pdf-contact"><?= esc($store->contactLine()) ?></p>
    <?php endif; ?>
</div>
<p class="pdf-doc-title"><?= esc($docTitle) ?></p>
