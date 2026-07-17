<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card form-card content-narrow">
    <div class="card-body">
        <div class="form-section">
            <h2 class="form-section-title">Laporan PDF</h2>
            <form method="post" action="<?= site_url('laporan/pdf') ?>" target="_blank" rel="noopener">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="jenis">Jenis laporan <span class="req" title="Wajib">*</span></label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        <option value="stok">Stok barang</option>
                        <option value="masuk">Barang masuk</option>
                        <option value="keluar">Barang keluar</option>
                    </select>
                </div>
                <div class="row g-3 mb-1">
                    <div class="col-md-6">
                        <label class="form-label" for="lap_awal">Tanggal awal</label>
                        <input type="text" name="tanggal_awal" id="lap_awal" class="form-control datepicker"
                               value="<?= date('01-m-Y') ?>" placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="lap_akhir">Tanggal akhir</label>
                        <input type="text" name="tanggal_akhir" id="lap_akhir" class="form-control datepicker"
                               value="<?= date('d-m-Y') ?>" placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
