<div class="card">
    <div class="card-header">Detail item</div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col" class="col-no">No</th>
                <th scope="col">Tipe</th>
                <th scope="col">Kode</th>
                <th scope="col">Nama</th>
                <th scope="col" class="text-end">Qty</th>
                <th scope="col" class="text-end">Harga</th>
                <th scope="col" class="text-end">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <?php $no = 1;

            foreach ($details as $d): ?>
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td><span class="badge badge-jenis"><?= esc($d['tipe_barang']) ?></span></td>
                    <td class="code"><?= esc($d['kode']) ?></td>
                    <td><?= esc($d['nama_barang']) ?></td>
                    <td class="text-end num"><?= (int) $d['quantity'] ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $d['harga_satuan'], 0, ',', '.') ?></td>
                    <td class="text-end num num-money"><?= number_format((float) $d['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th scope="row" colspan="6" class="text-end">Total</th>
                <th scope="row" class="text-end num num-money"><?= number_format((float) $total_harga, 0, ',', '.') ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
