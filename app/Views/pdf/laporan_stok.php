<?php
$docTitle = 'Laporan Stok Inventory';
$pdfMeta ??= [];
$pdfMeta['doc_ref'] = 'Stok ' . date('d-m-Y');

$countAman = $countRendah = $countHabis = 0;

foreach ($items as $r) {
    $s = (string) ($r['status_stok'] ?? '');
    if ($s === 'aman') {
        $countAman++;
    } elseif ($s === 'rendah') {
        $countRendah++;
    } elseif ($s === 'habis') {
        $countHabis++;
    }
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
        <td class="lbl">Posisi stok</td>
        <td class="val" colspan="3"><?= date('d-m-Y H:i') ?> WIB</td>
    </tr>
    <tr>
        <td class="lbl">Ringkasan</td>
        <td class="val" colspan="3">
            Aman <?= (int) $countAman ?>
            · Rendah <?= (int) $countRendah ?>
            · Habis <?= (int) $countHabis ?>
            · Total <?= count($items) ?> SKU
        </td>
    </tr>
</table>

<table class="data">
    <thead>
    <tr>
        <th scope="col" class="ctr" style="width:6%">No</th>
        <th scope="col" style="width:12%">Tipe</th>
        <th scope="col" style="width:14%">Kode</th>
        <th scope="col">Nama</th>
        <th scope="col" class="num" style="width:10%">Stok</th>
        <th scope="col" style="width:12%">Status</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;

foreach ($items as $r):
    $st  = (string) ($r['status_stok'] ?? '-');
    $cls = in_array($st, ['aman', 'rendah', 'habis'], true) ? 'status-' . $st : '';
    ?>
        <tr>
            <td class="ctr"><?= $no++ ?></td>
            <td><?= esc($r['tipe'] ?? '-') ?></td>
            <td><?= esc($r['kode'] ?? '-') ?></td>
            <td><?= esc($r['nama'] ?? '-') ?></td>
            <td class="num"><?= (int) ($r['stok'] ?? 0) ?></td>
            <td><span class="status-pill <?= esc($cls) ?>"><?= esc($st) ?></span></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($items === []): ?>
        <tr><td colspan="6" class="ctr">Tidak ada data stok.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?= view('pdf/_footer', ['store' => $store, 'pdfMeta' => $pdfMeta]) ?>
</body>
</html>
