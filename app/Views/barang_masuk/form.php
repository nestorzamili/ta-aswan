<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<a href="<?= site_url('barang-masuk') ?>" class="page-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar</a>

<div class="card form-card form-card-wide">
    <div class="card-body">
        <form method="post" action="<?= site_url('barang-masuk') ?>" id="formMasuk" novalidate>
            <?= csrf_field() ?>
            <?php
            $oldSupplier = old('id_supplier', '');
$oldTanggal              = old('tanggal_masuk', date('d-m-Y'));
?>
            <div class="form-section">
                <h2 class="form-section-title">Header transaksi</h2>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="no_faktur">No. faktur</label>
                        <input type="text" id="no_faktur" class="form-control code" value="<?= esc($no_faktur) ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tanggal_masuk">Tanggal <span class="req">*</span></label>
                        <input type="text" name="tanggal_masuk" id="tanggal_masuk" class="form-control datepicker"
                               value="<?= esc($oldTanggal) ?>" placeholder="DD-MM-YYYY" autocomplete="off" aria-required="true">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="id_supplier">Supplier <span class="req">*</span></label>
                        <select name="id_supplier" id="id_supplier" class="<?= esc(input_class('id_supplier', 'form-select')) ?>" aria-required="true"
                            <?= field_is_invalid('id_supplier') ? 'aria-invalid="true"' : '' ?>>
                            <option value="">Pilih supplier…</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id_supplier'] ?>" <?= (string) $oldSupplier === (string) $s['id_supplier'] ? 'selected' : '' ?>>
                                    <?= esc($s['nama_supplier']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= field_feedback('id_supplier') ?>
                        <div class="invalid-feedback d-block" id="id_supplier_error" hidden></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="panel-head">
                    <h2 class="form-section-title mb-0 border-0 pb-0">Detail item</h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                        <i class="bi bi-plus-lg"></i> Tambah baris
                    </button>
                </div>
                <?= field_feedback('lines') ?>
                <div class="table-responsive border rounded <?= field_is_invalid('lines') ? 'border-danger' : '' ?>">
                    <table class="table table-hover mb-0 lines-table" id="tblItems">
                        <thead>
                        <tr>

                            <th scope="col">Barang</th>
                            <th scope="col" class="col-qty text-end">Qty</th>
                            <th scope="col" class="col-harga text-end">Harga beli</th>
                            <th scope="col" class="col-sub text-end">Subtotal</th>
                            <th scope="col" class="col-act"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="tx-total-bar mt-2">
                    <span class="label">Total</span>
                    <span class="value" id="grandTotal">0</span>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Simpan transaksi</button>
                <a href="<?= site_url('barang-masuk') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/tx-lines.js') ?>"></script>
<script>
TxLines.init({
  form: 'formMasuk',
  hargaEditable: true,
  showStok: false,
  barang: <?= json_encode(array_map(static fn ($r) => [
      'id' => $r['id_barang'], 'nama' => $r['nama_barang'] . ' [' . $r['tipe_barang'] . ']', 'harga' => (int) $r['harga_beli'], 'stok' => (int) $r['stok'],
  ], $barang), JSON_UNESCAPED_UNICODE) ?>,
  existing: <?= json_encode(old_transaction_lines(true), JSON_UNESCAPED_UNICODE) ?>,
  validate(e) {
    const supplier = document.getElementById('id_supplier');
    const errEl = document.getElementById('id_supplier_error');
    supplier.classList.remove('is-invalid');
    if (errEl) {
      errEl.hidden = true;
      errEl.textContent = '';
    }
    if (!supplier.value) {
      e.preventDefault();
      const msg = 'Pilih supplier terlebih dahulu.';
      supplier.classList.add('is-invalid');
      supplier.setAttribute('aria-invalid', 'true');
      if (errEl) {
        errEl.textContent = msg;
        errEl.hidden = false;
      }
      if (window.AppFeedback && AppFeedback.showToast) {
        AppFeedback.showToast('error', msg);
      }
      supplier.focus();
      return false;
    }
    supplier.removeAttribute('aria-invalid');
    return true;
  },
});
document.getElementById('id_supplier')?.addEventListener('change', function () {
  this.classList.remove('is-invalid');
  this.removeAttribute('aria-invalid');
  const errEl = document.getElementById('id_supplier_error');
  if (errEl) {
    errEl.hidden = true;
    errEl.textContent = '';
  }
});
</script>
<?= $this->endSection() ?>
