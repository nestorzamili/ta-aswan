<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($item); ?>
<a href="<?= site_url('barang-keluar') ?>" class="page-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke daftar</a>

<div class="card form-card form-card-wide">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? site_url('barang-keluar/' . $item['id_keluar']) : site_url('barang-keluar') ?>" id="formKeluar" novalidate>
            <?= csrf_field() ?>
            <div class="form-section">
                <h2 class="form-section-title">Header transaksi</h2>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="no_transaksi">No. transaksi</label>
                        <input type="text" id="no_transaksi" class="form-control code" value="<?= esc($no_transaksi) ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tanggal_keluar">Tanggal <span class="req">*</span></label>
                        <?php
                        $tglKeluar = $item['tanggal_keluar'] ?? date('Y-m-d');
$tglKeluarDisp                     = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tglKeluar)
    ? date('d-m-Y', strtotime($tglKeluar))
    : $tglKeluar;
$tglKeluarDisp = old('tanggal_keluar', $tglKeluarDisp);
?>
                        <input type="text" name="tanggal_keluar" id="tanggal_keluar" class="form-control datepicker"
                               value="<?= esc($tglKeluarDisp) ?>" placeholder="DD-MM-YYYY" autocomplete="off" aria-required="true">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="tujuan">Tujuan / pelanggan <span class="req">*</span></label>
                        <input type="text" name="tujuan" id="tujuan" class="<?= esc(input_class('tujuan')) ?>"
                               value="<?= esc(old('tujuan', $item['tujuan'] ?? '')) ?>" placeholder="Nama pelanggan atau tujuan" autocomplete="off"
                               aria-required="true"
                               <?= field_is_invalid('tujuan') ? 'aria-invalid="true"' : '' ?>>
                        <?= field_feedback('tujuan') ?>
                        <div class="invalid-feedback d-block" id="tujuan_error" hidden></div>
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
                <div class="table-responsive lines-table-wrap <?= field_is_invalid('lines') ? 'border-danger' : '' ?>">
                    <table class="table table-hover mb-0 lines-table" id="tblItems">
                        <thead>
                        <tr>
                            <th scope="col">Barang</th>
                            <th scope="col" class="col-qty text-end">Qty</th>
                            <th scope="col" class="col-harga text-end">Harga jual</th>
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
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update transaksi' : 'Simpan transaksi' ?></button>
                <a href="<?= site_url('barang-keluar') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/tx-lines.js') ?>"></script>
<script>
TxLines.init({
  form: 'formKeluar',
  hargaEditable: false,
  showStok: true,
  barang: <?= json_encode(array_map(static fn ($r) => [
      'id' => $r['id_barang'], 'nama' => $r['nama_barang'] . ' [' . $r['tipe_barang'] . ']', 'harga' => (int) $r['harga_jual'], 'stok' => (int) $r['stok'],
  ], $barang), JSON_UNESCAPED_UNICODE) ?>,
  existing: <?= json_encode((static function () use ($details) {
      $old = old_transaction_lines(false);
      if ($old !== []) {
          return $old;
      }

      return array_map(static fn ($r) => [
          'id_barang'    => $r['id_barang'],
          'quantity'     => (int) $r['quantity'],
          'harga_satuan' => (float) $r['harga_satuan'],
      ], $details ?? []);
  })(), JSON_UNESCAPED_UNICODE) ?>,
  validate(e) {
    const tujuan = document.getElementById('tujuan');
    const errEl = document.getElementById('tujuan_error');
    tujuan.classList.remove('is-invalid');
    if (errEl) {
      errEl.hidden = true;
      errEl.textContent = '';
    }
    if (!tujuan.value.trim()) {
      e.preventDefault();
      const msg = 'Tujuan / pelanggan wajib diisi.';
      tujuan.classList.add('is-invalid');
      tujuan.setAttribute('aria-invalid', 'true');
      if (errEl) {
        errEl.textContent = msg;
        errEl.hidden = false;
      }
      if (window.AppFeedback && AppFeedback.showToast) {
        AppFeedback.showToast('error', msg);
      }
      tujuan.focus();
      return false;
    }
    tujuan.removeAttribute('aria-invalid');
    return true;
  },
});
document.getElementById('tujuan')?.addEventListener('input', function () {
  this.classList.remove('is-invalid');
  this.removeAttribute('aria-invalid');
  const errEl = document.getElementById('tujuan_error');
  if (errEl) {
    errEl.hidden = true;
    errEl.textContent = '';
  }
});
</script>
<?= $this->endSection() ?>
