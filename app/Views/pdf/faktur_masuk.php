<?php
$docTitle = 'Faktur Barang Masuk';
$docRef   = $header['no_faktur'] ?? '';
$pdfMeta ??= [];
$pdfMeta['doc_ref'] = $docRef;

$tgl = $header['tanggal_masuk'] ?? '';
if (is_string($tgl) && preg_match('/^\d{4}-\d{2}-\d{2}/', $tgl)) {
    $tgl = date('d-m-Y', strtotime($tgl));
}

$totalItem = count($details);
$totalQty  = 0;

foreach ($details as $d) {
    $totalQty += (int) ($d['quantity'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= esc($docTitle) ?> · <?= esc($docRef) ?></title>
<?= view('pdf/_styles') ?>
</head>
<body>
<?= view('pdf/_header', ['store' => $store, 'docTitle' => $docTitle]) ?>

<table class="pdf-meta-table">
    <tr>
        <td class="lbl">No. faktur</td>
        <td class="val"><?= esc($header['no_faktur'] ?? '-') ?></td>
        <td class="lbl">Tanggal</td>
        <td class="val"><?= esc($tgl) ?></td>
    </tr>
    <tr>
        <td class="lbl">Supplier</td>
        <td class="val"><?= esc($header['nama_supplier'] ?? '-') ?></td>
        <td class="lbl">Petugas</td>
        <td class="val"><?= esc($header['nama_admin'] ?? '-') ?></td>
    </tr>
</table>

<table class="data">
    <thead>
    <tr>
        <th scope="col" class="ctr" style="width:6%">No</th>
        <th scope="col" style="width:12%">Tipe</th>
        <th scope="col" style="width:14%">Kode</th>
        <th scope="col">Nama</th>
        <th scope="col" class="num" style="width:8%">Qty</th>
        <th scope="col" class="num" style="width:14%">Harga</th>
        <th scope="col" class="num" style="width:14%">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;

foreach ($details as $d): ?>
        <tr>
            <td class="ctr"><?= $no++ ?></td>
            <td><?= esc($d['tipe_barang'] ?? '-') ?></td>
            <td><?= esc($d['kode'] ?? '-') ?></td>
            <td><?= esc($d['nama_barang'] ?? '-') ?></td>
            <td class="num"><?= (int) ($d['quantity'] ?? 0) ?></td>
            <td class="num">Rp <?= number_format((float) ($d['harga_satuan'] ?? 0), 0, ',', '.') ?></td>
            <td class="num">Rp <?= number_format((float) ($d['subtotal'] ?? 0), 0, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="6" class="num">Total</th>
        <th class="num">Rp <?= number_format((float) ($header['total_harga'] ?? 0), 0, ',', '.') ?></th>
    </tr>
    </tfoot>
</table>

<p class="pdf-summary">
    Total item: <strong><?= (int) $totalItem ?></strong>
    · Total qty: <strong><?= (int) $totalQty ?></strong>
</p>

<?= view('pdf/_footer', ['store' => $store, 'pdfMeta' => $pdfMeta, 'docRef' => $docRef]) ?>
</body>
</html>
