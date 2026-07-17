<?php
$docTitle  = 'Laporan Barang Keluar';
$awalDisp  = $awal ?? '';
$akhirDisp = $akhir ?? '';
if (is_string($awalDisp) && preg_match('/^\d{4}-\d{2}-\d{2}/', $awalDisp)) {
    $awalDisp = date('d-m-Y', strtotime($awalDisp));
}
if (is_string($akhirDisp) && preg_match('/^\d{4}-\d{2}-\d{2}/', $akhirDisp)) {
    $akhirDisp = date('d-m-Y', strtotime($akhirDisp));
}
$pdfMeta ??= [];
$pdfMeta['doc_ref'] = 'Keluar ' . $awalDisp . ' s/d ' . $akhirDisp;

$sumQty = 0;
$sumRp  = 0.0;

foreach ($items as $r) {
    $sumQty += (int) ($r['total_quantity'] ?? 0);
    $sumRp += (float) ($r['total_harga'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= esc($docTitle) ?></title>
<?= view('pdf/_styles') ?>
</head>
<body>
<?= view('pdf/_header', ['store' => $store, 'docTitle' => $docTitle]) ?>

<table class="pdf-meta-table">
    <tr>
        <td class="lbl">Periode</td>
        <td class="val" colspan="3"><?= esc($awalDisp) ?> s/d <?= esc($akhirDisp) ?></td>
    </tr>
</table>

<table class="data">
    <thead>
    <tr>
        <th scope="col" class="ctr" style="width:6%">No</th>
        <th scope="col">No Transaksi</th>
        <th scope="col" style="width:14%">Tanggal</th>
        <th scope="col">Tujuan</th>
        <th scope="col" class="num" style="width:10%">Qty</th>
        <th scope="col" class="num" style="width:16%">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;

foreach ($items as $r):
    $tgl = $r['tanggal_keluar'] ?? '';
    if (is_string($tgl) && preg_match('/^\d{4}-\d{2}-\d{2}/', $tgl)) {
        $tgl = date('d-m-Y', strtotime($tgl));
    }
    ?>
        <tr>
            <td class="ctr"><?= $no++ ?></td>
            <td><?= esc($r['no_transaksi'] ?? '-') ?></td>
            <td><?= esc($tgl) ?></td>
            <td><?= esc($r['tujuan'] ?? '-') ?></td>
            <td class="num"><?= (int) ($r['total_quantity'] ?? 0) ?></td>
            <td class="num">Rp <?= number_format((float) ($r['total_harga'] ?? 0), 0, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($items === []): ?>
        <tr><td colspan="6" class="ctr">Tidak ada data pada periode ini.</td></tr>
    <?php endif; ?>
    </tbody>
    <?php if ($items !== []): ?>
    <tfoot>
    <tr>
        <th colspan="4" class="num">Total</th>
        <th class="num"><?= (int) $sumQty ?></th>
        <th class="num">Rp <?= number_format($sumRp, 0, ',', '.') ?></th>
    </tr>
    </tfoot>
    <?php endif; ?>
</table>

<p class="pdf-summary"><?= count($items) ?> transaksi</p>

<?= view('pdf/_footer', ['store' => $store, 'pdfMeta' => $pdfMeta]) ?>
</body>
</html>
