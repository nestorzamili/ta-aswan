<?php

use Config\Store;

/**
 * Digital issuance footer (no wet-signature blocks).
 *
 * @var array{printed_at?: string, printed_by?: string, printed_level?: string|null, doc_ref?: string|null} $pdfMeta
 * @var Store|null                                                                                          $store
 */
$pdfMeta ??= [];
$store ??= config(Store::class);

$by    = (string) ($pdfMeta['printed_by'] ?? 'Sistem');
$level = $pdfMeta['printed_level'] ?? null;
$at    = (string) ($pdfMeta['printed_at'] ?? date('d-m-Y H:i') . ' WIB');
$ref   = $pdfMeta['doc_ref'] ?? ($docRef ?? null);

$petugas = $by;
if (is_string($level) && $level !== '') {
    $petugas .= ' (' . $level . ')';
}
?>
<div class="pdf-footer">
    <p class="pdf-note">
        Dokumen digital · digenerate sistem inventory.
    </p>
    <table class="pdf-footer-meta">
        <tr>
            <td style="width:55%">
                <strong>Petugas:</strong> <?= esc($petugas) ?><br>
                <strong>Dicetak:</strong> <?= esc($at) ?>
            </td>
            <td style="width:45%; text-align:right">
                <?php if ($ref !== null && (string) $ref !== ''): ?>
                    <strong>Ref:</strong> <?= esc((string) $ref) ?><br>
                <?php endif; ?>
                <?= esc($store->name) ?><?= trim($store->city) !== '' ? ' · ' . esc($store->city) : '' ?>
            </td>
        </tr>
    </table>
</div>
