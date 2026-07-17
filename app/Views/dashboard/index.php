<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="kpi-grid" id="kpiRow">
    <a class="kpi-card" href="<?= site_url('sparepart') ?>" aria-busy="true" aria-label="Lihat data sparepart">
        <div class="kpi-icon" aria-hidden="true"><i class="bi bi-cpu"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Sparepart</div>
            <div class="kpi-value skeleton-text" id="kpiSp">    </div>
            <div class="kpi-hint">SKU aktif</div>
        </div>
    </a>
    <a class="kpi-card" href="<?= site_url('aksesoris') ?>" aria-busy="true" aria-label="Lihat data aksesoris">
        <div class="kpi-icon is-warm" aria-hidden="true"><i class="bi bi-earbuds"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Aksesoris</div>
            <div class="kpi-value skeleton-text" id="kpiAk">    </div>
            <div class="kpi-hint">SKU aktif</div>
        </div>
    </a>
    <a class="kpi-card kpi-card-stok" href="<?= site_url('stok') ?>" aria-busy="true" aria-label="Lihat monitoring stok">
        <div class="kpi-icon is-ok" aria-hidden="true"><i class="bi bi-layers"></i></div>
        <div class="kpi-body">
            <div class="kpi-label-row">
                <div class="kpi-label">Total unit stok</div>
                <span class="kpi-badge kpi-badge-kritis is-hidden" id="kpiKritisBadge" hidden>0 kritis</span>
            </div>
            <div class="kpi-value skeleton-text" id="kpiUnit">    </div>
            <div class="kpi-hint" id="kpiUnitHint">Semua barang</div>
        </div>
    </a>
    <a class="kpi-card" href="<?= site_url('barang-masuk') ?>" aria-busy="true" aria-label="Lihat transaksi barang masuk">
        <div class="kpi-icon is-ink" aria-hidden="true"><i class="bi bi-arrow-left-right"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Transaksi bulan ini</div>
            <div class="kpi-value skeleton-text" id="kpiTrx">    </div>
            <div class="kpi-hint">Masuk + keluar</div>
        </div>
    </a>
</div>

<div class="dash-charts">
    <div class="card">
        <div class="card-body">
            <div class="panel-head">
                <h2 class="panel-title">Komposisi status stok</h2>
                <a class="panel-link" href="<?= site_url('stok') ?>">Lihat stok</a>
            </div>
            <div class="chart-box chart-box-sm skeleton-box">
                <canvas id="chartStatus" aria-label="Grafik status stok"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="panel-head">
                <h2 class="panel-title">Stok per kategori (top 8)</h2>
            </div>
            <div class="chart-box chart-box-sm skeleton-box">
                <canvas id="chartKat" aria-label="Grafik stok kategori"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="panel-head">
            <h2 class="panel-title">Tren transaksi · 14 hari terakhir</h2>
            <a class="panel-link" href="<?= site_url('laporan') ?>">Laporan</a>
        </div>
        <div class="chart-box chart-box-lg skeleton-box">
            <canvas id="chartTren" aria-label="Tren transaksi"></canvas>
        </div>
    </div>
</div>

<div class="dash-bottom">
    <div class="card">
        <div class="card-header card-header-row">
            <span>Stok menipis</span>
            <a class="panel-link" href="<?= site_url('stok?status_stok=kritis') ?>">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="col" class="col-no">No</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Kode</th>
                    <th scope="col">Nama</th>
                    <th scope="col" class="text-end">Stok</th>
                    <th scope="col">Status</th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 1;

foreach ($menipis as $r): ?>
                    <?php
    $editUrl = $r['tipe'] === 'aksesoris'
        ? site_url('aksesoris/' . $r['id'] . '/edit')
        : site_url('sparepart/' . $r['id'] . '/edit');
    ?>
                    <tr class="row-link" title="Buka edit barang">
                        <td class="col-no"><?= $no++ ?></td>
                        <td><span class="badge badge-jenis"><?= esc($r['tipe']) ?></span></td>
                        <td class="code"><?= esc($r['kode']) ?></td>
                        <td class=""><a class="row-link-main" href="<?= esc($editUrl) ?>" aria-label="Edit <?= esc($r['nama'], 'attr') ?>"><span><?= esc($r['nama']) ?></span></a></td>
                        <td class="text-end num"><?= (int) $r['stok'] ?></td>
                        <td><span class="badge status-<?= esc($r['status_stok']) ?>"><?= esc($r['status_stok']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (! $menipis): ?>
                    <?= view('partials/empty_state', [
                        'empty_in_table' => true,
                        'empty_colspan'  => 6,
                        'empty_icon'     => 'bi-check2-circle',
                        'empty_title'    => 'Semua stok aman',
                        'empty_text'     => 'Tidak ada barang dengan stok menipis saat ini.',
                    ], ['saveData' => false]) ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header card-header-row">
            <span>Transaksi terbaru</span>
            <a class="panel-link" href="<?= site_url('barang-masuk') ?>">Lihat transaksi</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="col" class="col-no">No</th>
                    <th scope="col">Nomor</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Jenis</th>
                    <th scope="col" class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 1;

foreach ($terbaru as $r): ?>
                    <?php $detailUrl = site_url($r['link'] ?? '#'); ?>
                    <tr class="row-link" title="Buka detail">
                        <td class="col-no"><?= $no++ ?></td>
                        <td class="code"><a class="row-link-main" href="<?= esc($detailUrl) ?>" aria-label="Detail <?= esc($r['nomor'], 'attr') ?>"><span><?= esc($r['nomor']) ?></span></a></td>
                        <td class="num"><?= esc(date('d-m-Y', strtotime($r['tanggal']))) ?></td>
                        <td><span class="badge badge-jenis"><?= esc($r['jenis']) ?></span></td>
                        <td class="text-end num num-money"><?= number_format((float) $r['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (! $terbaru): ?>
                    <?= view('partials/empty_state', [
                        'empty_in_table' => true,
                        'empty_colspan'  => 5,
                        'empty_icon'     => 'bi-receipt',
                        'empty_title'    => 'Belum ada transaksi',
                        'empty_text'     => 'Transaksi masuk dan keluar akan muncul di sini.',
                    ], ['saveData' => false]) ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4" crossorigin="anonymous"></script>
<script src="<?= base_url('js/dashboard.js') ?>"></script>
<?= $this->endSection() ?>
