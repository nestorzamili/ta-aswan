<?php

$routes->get('/', static fn () => redirect()->to('/login'));

$routes->get('health', static function () {
    try {
        $db = \Config\Database::connect();
        if (!$db->initialize()) {
            return \Config\Services::response()->setStatusCode(500)->setJSON(['status' => 'ERROR', 'message' => 'DB init failed']);
        }
        return \Config\Services::response()->setJSON(['status' => 'OK', 'database' => 'connected']);
    } catch (\Throwable $e) {
        return \Config\Services::response()->setStatusCode(500)->setJSON(['status' => 'ERROR', 'message' => $e->getMessage()]);
    }
});

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout');
$routes->get('lupa-password', 'AuthController::forgotPassword');
$routes->post('lupa-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reset-password/(:segment)', 'AuthController::updatePassword/$1');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/chart/kpi', 'DashboardController::chartKpi');
    $routes->get('dashboard/chart/status-stok', 'DashboardController::chartStatusStok');
    $routes->get('dashboard/chart/stok-kategori', 'DashboardController::chartStokKategori');
    $routes->get('dashboard/chart/tren-transaksi', 'DashboardController::chartTrenTransaksi');

    $routes->get('sparepart', 'SparepartController::index');
    $routes->get('sparepart/create', 'SparepartController::create');
    $routes->post('sparepart', 'SparepartController::store');
    $routes->get('sparepart/(:num)/edit', 'SparepartController::edit/$1');
    $routes->post('sparepart/(:num)', 'SparepartController::update/$1');

    $routes->get('aksesoris', 'AksesorisController::index');
    $routes->get('aksesoris/create', 'AksesorisController::create');
    $routes->post('aksesoris', 'AksesorisController::store');
    $routes->get('aksesoris/(:num)/edit', 'AksesorisController::edit/$1');
    $routes->post('aksesoris/(:num)', 'AksesorisController::update/$1');

    $routes->get('barang-masuk', 'BarangMasukController::index');
    $routes->get('barang-masuk/create', 'BarangMasukController::create');
    $routes->post('barang-masuk', 'BarangMasukController::store');
    $routes->get('barang-masuk/(:num)', 'BarangMasukController::show/$1');
    $routes->get('barang-masuk/(:num)/pdf', 'BarangMasukController::pdf/$1');

    $routes->get('barang-keluar', 'BarangKeluarController::index');
    $routes->get('barang-keluar/create', 'BarangKeluarController::create');
    $routes->post('barang-keluar', 'BarangKeluarController::store');
    $routes->get('barang-keluar/(:num)', 'BarangKeluarController::show/$1');
    $routes->get('barang-keluar/(:num)/pdf', 'BarangKeluarController::pdf/$1');

    $routes->get('stok', 'StokController::index');
    $routes->get('supplier', 'SupplierController::index');
    $routes->get('supplier/create', 'SupplierController::create');
    $routes->post('supplier', 'SupplierController::store');
    $routes->get('supplier/(:num)/edit', 'SupplierController::edit/$1');
    $routes->post('supplier/(:num)', 'SupplierController::update/$1');
    $routes->get('supplier/(:num)', 'SupplierController::show/$1');

    $routes->get('laporan', 'LaporanController::index');
    $routes->post('laporan/pdf', 'LaporanController::pdf');

    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->post('sparepart/(:num)/delete', 'SparepartController::delete/$1');
        $routes->post('aksesoris/(:num)/delete', 'AksesorisController::delete/$1');

        $routes->post('barang-masuk/(:num)/delete', 'BarangMasukController::delete/$1');

        $routes->get('barang-keluar/(:num)/edit', 'BarangKeluarController::edit/$1');
        $routes->post('barang-keluar/(:num)', 'BarangKeluarController::update/$1');
        $routes->post('barang-keluar/(:num)/delete', 'BarangKeluarController::delete/$1');

        $routes->post('supplier/(:num)/delete', 'SupplierController::delete/$1');

        $routes->get('pengguna', 'PenggunaController::index');
        $routes->get('pengguna/create', 'PenggunaController::create');
        $routes->post('pengguna', 'PenggunaController::store');
        $routes->get('pengguna/(:num)/edit', 'PenggunaController::edit/$1');
        $routes->post('pengguna/(:num)', 'PenggunaController::update/$1');
        $routes->post('pengguna/(:num)/delete', 'PenggunaController::delete/$1');
    });
});
