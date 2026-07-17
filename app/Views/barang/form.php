<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit      = ! empty($item);
$route       = $cfg['route'];
$label       = $cfg['label'];
$pk          = $cfg['pk'];
$kodeCol     = $cfg['kode'];
$namaCol     = $cfg['nama'];
$hargaBeli   = old('harga_beli', isset($item['harga_beli']) ? (int) $item['harga_beli'] : 0);
$hargaJual   = old('harga_jual', isset($item['harga_jual']) ? (int) $item['harga_jual'] : 0);
$action      = $isEdit ? site_url($route . '/' . $item[$pk]) : site_url($route);
$placeholder = $route === 'aksesoris' ? 'Contoh: Charger Type-C' : 'Contoh: LCD Oppo A3S';
$katList     = $route === 'aksesoris'
    ? ['Charger', 'Audio', 'Pelindung Layar', 'Case', 'Kabel']
    : ['LCD', 'Baterai', 'Touchscreen', 'IC', 'Kamera'];
?>
<a href="<?= site_url($route) ?>" class="page-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar</a>

<div class="card form-card">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="form-section">
                <h2 class="form-section-title">Identitas barang</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="<?= esc($kodeCol) ?>">Kode <?= esc(strtolower($label)) ?></label>
                        <input type="text" id="<?= esc($kodeCol) ?>" class="form-control code" value="<?= esc($kode) ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="kode_manual">Kode manual <span class="opt">(opsional)</span></label>
                        <input type="text" name="kode_manual" id="kode_manual" class="<?= esc(input_class('kode_manual')) ?>"
                               value="<?= esc(old('kode_manual', $item['kode_manual'] ?? '')) ?>"
                               placeholder="Kode toko / supplier" autocomplete="off">
                        <?= field_feedback('kode_manual') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= esc($namaCol) ?>">Nama <span class="req" title="Wajib">*</span></label>
                        <input type="text" name="<?= esc($namaCol) ?>" id="<?= esc($namaCol) ?>"
                               class="<?= esc(input_class($namaCol)) ?>"
                               value="<?= esc(old($namaCol, $item[$namaCol] ?? '')) ?>"
                               placeholder="<?= esc($placeholder) ?>" autocomplete="off"
                               <?= $isEdit ? '' : 'autofocus' ?> required aria-required="true"
                               <?= aria_invalid_attr($namaCol) ?>>
                        <?= field_feedback($namaCol) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="kategori">Kategori</label>
                        <input type="text" name="kategori" id="kategori" class="<?= esc(input_class('kategori')) ?>" list="kat-list"
                               value="<?= esc(old('kategori', $item['kategori'] ?? '')) ?>"
                               placeholder="Kategori…" autocomplete="off">
                        <datalist id="kat-list">
                            <?php foreach ($katList as $opt): ?>
                                <option value="<?= esc($opt) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?= field_feedback('kategori') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="merk">Merk</label>
                        <input type="text" name="merk" id="merk" class="<?= esc(input_class('merk')) ?>"
                               value="<?= esc(old('merk', $item['merk'] ?? '')) ?>"
                               placeholder="Merk…" autocomplete="off">
                        <?= field_feedback('merk') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="satuan">Satuan</label>
                        <input type="text" name="satuan" id="satuan" class="<?= esc(input_class('satuan')) ?>"
                               value="<?= esc(old('satuan', $item['satuan'] ?? 'pcs')) ?>"
                               placeholder="pcs" autocomplete="off">
                        <?= field_feedback('satuan') ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-section-title">Harga &amp; stok</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="harga_beli">Harga beli</label>
                        <div class="input-group field-money <?= field_is_invalid('harga_beli') ? 'is-invalid' : '' ?>">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_beli" id="harga_beli"
                                   class="<?= esc(input_class('harga_beli', 'form-control input-money text-end')) ?>"
                                   inputmode="numeric" autocomplete="off"
                                   value="<?= esc($hargaBeli) ?>" placeholder="0"
                                   <?= aria_invalid_attr('harga_beli') ?>>
                        </div>
                        <?= field_feedback('harga_beli') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="harga_jual">Harga jual</label>
                        <div class="input-group field-money <?= field_is_invalid('harga_jual') ? 'is-invalid' : '' ?>">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_jual" id="harga_jual"
                                   class="<?= esc(input_class('harga_jual', 'form-control input-money text-end')) ?>"
                                   inputmode="numeric" autocomplete="off"
                                   value="<?= esc($hargaJual) ?>" placeholder="0"
                                   <?= aria_invalid_attr('harga_jual') ?>>
                        </div>
                        <?= field_feedback('harga_jual') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= $isEdit ? 'stok_saat_ini' : 'stok' ?>"><?= $isEdit ? 'Stok saat ini' : 'Stok awal' ?></label>
                        <?php if ($isEdit): ?>
                            <input type="number" id="stok_saat_ini" class="form-control" min="0" step="1"
                                   value="<?= esc((string) (int) ($item['stok'] ?? 0)) ?>"
                                   readonly tabindex="-1"
                                   title="Stok hanya berubah lewat transaksi masuk/keluar">
                            <div class="form-text">Stok dikunci — ubah lewat transaksi masuk/keluar.</div>
                        <?php else: ?>
                            <input type="number" name="stok" id="stok" class="<?= esc(input_class('stok')) ?>" min="0" step="1"
                                   value="<?= esc(old('stok', $item['stok'] ?? 0)) ?>"
                                   <?= aria_invalid_attr('stok') ?>>
                            <?= field_feedback('stok') ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
                <a href="<?= site_url($route) ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
