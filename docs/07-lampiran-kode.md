# Lampiran — Kode Program per Fitur

## 1. Autentikasi & Keamanan

```php
<?php

class AuthController extends BaseController
{
    private const ROUTE_LOGIN         = '/login';
    private const MSG_BAD_CREDENTIALS = 'Username/email atau password salah.';
    private const DATETIME_FORMAT     = 'Y-m-d H:i:s';

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $throttler  = Services::throttler();
        $throttled  = ! $throttler->check('login_' . md5($this->request->getIPAddress()), 5, MINUTE);
        $validInput = $throttled || $this->validate([
            'username' => ['label' => 'Username atau Email', 'rules' => 'required'],
            'password' => ['label' => 'Password', 'rules' => 'required'],
        ]);

        if ($throttled || ! $validInput) {
            $response = $throttled
                ? $this->redirectBackWithFieldErrors([], 'Terlalu banyak percobaan login. Silakan coba lagi nanti.')
                : $this->redirectBackWithFieldErrors($this->validator->getErrors());
        } else {
            $login    = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');
            $admin    = (new AdminModel())->findByLogin($login);

            if ($admin && $admin['status'] === 'aktif' && password_verify($password, $admin['password'])) {
                session()->regenerate();
                session()->set([
                    'isLoggedIn' => true,
                    'id_admin'   => $admin['id_admin'],
                    'username'   => $admin['username'],
                    'nama'       => $admin['nama'],
                    'level'      => $admin['level'],
                    'foto'       => $admin['foto'] ?? null,
                ]);
                log_activity('login', 'auth', (int) $admin['id_admin'], 'Login: ' . $admin['username']);
                $response = redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $admin['nama']);
            } else {
                $response = $this->redirectBackWithFieldErrors(
                    [
                        'username' => self::MSG_BAD_CREDENTIALS,
                        'password' => self::MSG_BAD_CREDENTIALS,
                    ],
                    self::MSG_BAD_CREDENTIALS,
                );
            }
        }

        return $response;
    }

    public function logout()
    {
        log_activity('logout', 'auth', session('id_admin') !== null ? (int) session('id_admin') : null, 'Logout: ' . (string) session('username'));
        session()->destroy();

        return redirect()->to(self::ROUTE_LOGIN);
    }

    public function sendResetLink()
    {
        $email = trim((string) $this->request->getPost('email'));
        $admin = (new AdminModel())->findByEmail($email);

        if ($admin) {
            $token = bin2hex(random_bytes(32));
            (new PasswordResetTokenModel())->insert([
                'id_admin'   => $admin['id_admin'],
                'token'      => $token,
                'expires_at' => date(self::DATETIME_FORMAT, time() + 3600),
                'created_at' => date(self::DATETIME_FORMAT),
            ]);
            $link = base_url('reset-password/' . $token);

            $emailSvc = Services::email();
            $emailSvc->setTo($admin['email']);
            $emailSvc->setSubject('Permintaan Reset Password');
            $emailSvc->setMessage("<p>Halo {$admin['nama']},</p><p>Klik link berikut untuk melakukan reset password Anda:</p><p><a href=\"{$link}\">{$link}</a></p><p>Link ini berlaku selama 1 jam.</p>");
            if (! $emailSvc->send()) {
                log_message('error', $emailSvc->printDebugger(['headers']));
            }
        }

        return redirect()->to(self::ROUTE_LOGIN)->with(
            'success',
            'Jika email terdaftar, instruksi pemulihan sandi telah dikirim ke email tersebut.',
        );
    }

    public function updatePassword(string $token)
    {
        $row = (new PasswordResetTokenModel())
            ->where('token', $token)
            ->where('used_at', null)
            ->first();

        if (! $row || strtotime($row['expires_at']) < time()) {
            return redirect()->to(self::ROUTE_LOGIN)->with('error', 'Token reset tidak valid atau sudah kedaluwarsa.');
        }

        if (! $this->validate([
            'password'         => ['label' => 'Password', 'rules' => 'required|min_length[6]'],
            'password_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'required|matches[password]'],
        ])) {
            return $this->redirectBackWithFieldErrors($this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');

        (new AdminModel())->update($row['id_admin'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        (new PasswordResetTokenModel())->update($row['id'], [
            'used_at' => date(self::DATETIME_FORMAT),
        ]);

        return redirect()->to(self::ROUTE_LOGIN)->with('success', 'Password berhasil diubah. Silakan login.');
    }
}
```

```php
<?php

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id_admin';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'username', 'password', 'nama', 'email', 'nomor_telepon', 'foto', 'level', 'status', 'deleted_at',
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && ! str_starts_with($data['data']['password'], '$2y$')) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    public function findByLogin(string $login): ?array
    {
        return filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $this->findByEmail($login)
            : $this->findByUsername($login);
    }
}
```

## 2. Kontrol Akses (RBAC)

```php
<?php

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $admin = model(AdminModel::class)->find(session()->get('id_admin'));
        if (! $admin || $admin['status'] !== 'aktif') {
            session()->destroy();

            return redirect()->to('/login')->with('error', 'Akun Anda telah dinonaktifkan atau tidak ditemukan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $level   = session()->get('level');
        $allowed = $arguments ?? [];

        if (! $allowed || ! in_array($level, $allowed, true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
```

## 3. Manajemen Stok

```php
<?php

class StockService
{
    private const MSG_QTY_POSITIVE = 'Qty stok harus lebih dari 0.';
    private const MSG_NOT_FOUND    = 'Barang tidak ditemukan.';

    public function statusFromQty(int $qty): string
    {
        if ($qty <= 0) {
            return 'habis';
        }
        if ($qty < 3) {
            return 'rendah';
        }

        return 'aman';
    }

    public function increase(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }
        $this->adjustAtomic($idBarang, $qty, false);
    }

    public function decrease(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }
        $this->adjustAtomic($idBarang, -$qty, true);
    }

    public function assertAvailable(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }

        $db  = Database::connect();
        $row = $db->table('barang')
            ->select('stok, nama_barang')
            ->where('id_barang', $idBarang)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $row) {
            throw new BusinessException(self::MSG_NOT_FOUND);
        }

        if ((int) $row['stok'] < $qty) {
            $nama = $row['nama_barang'] ?? '-';

            throw new BusinessException("Stok tidak cukup untuk: {$nama}");
        }
    }

    protected function adjustAtomic(int $idBarang, int $delta, bool $failIfInsufficient): void
    {
        $db      = Database::connect();
        $table   = $db->prefixTable('barang');
        $pk      = 'id_barang';
        $namaCol = 'nama_barang';

        if ($failIfInsufficient && $delta < 0) {
            $need = abs($delta);
            $sql  = "UPDATE `{$table}` SET
                `stok` = `stok` + ?,
                `status_stok` = CASE
                    WHEN `stok` <= 0 THEN 'habis'
                    WHEN `stok` < 3 THEN 'rendah'
                    ELSE 'aman'
                END
                WHERE `{$pk}` = ? AND `stok` >= ? AND `deleted_at` IS NULL";
            $db->query($sql, [$delta, $idBarang, $need]);
            if ($db->affectedRows() === 0) {
                $row = $db->table('barang')
                    ->select($namaCol)
                    ->where($pk, $idBarang)
                    ->where('deleted_at', null)
                    ->get()
                    ->getRowArray();
                if (! $row) {
                    throw new BusinessException(self::MSG_NOT_FOUND);
                }
                $nama = $row[$namaCol] ?? '-';

                throw new BusinessException("Stok tidak cukup untuk: {$nama}");
            }

            return;
        }

        $sql = "UPDATE `{$table}` SET
            `stok` = GREATEST(0, `stok` + ?),
            `status_stok` = CASE
                WHEN `stok` <= 0 THEN 'habis'
                WHEN `stok` < 3 THEN 'rendah'
                ELSE 'aman'
            END
            WHERE `{$pk}` = ? AND `deleted_at` IS NULL";
        $db->query($sql, [$delta, $idBarang]);
        if ($db->affectedRows() === 0) {
            throw new BusinessException(self::MSG_NOT_FOUND);
        }
    }

    public function setStatusForRow(array $data): array
    {
        $stok                = (int) ($data['stok'] ?? 0);
        $data['status_stok'] = $this->statusFromQty($stok);

        return $data;
    }
}
```

## 4. Penomoran Dokumen Otomatis

```php
<?php

class NumberingService
{
    public function next(string $prefix, string $table, string $column, ?string $tipeBarang = null): string
    {
        $year     = date('Y');
        $lockName = 'num_' . $table . '_' . $prefix . '_' . $year;
        $db       = Database::connect();

        $got = $db->query('SELECT GET_LOCK(?, 10) AS locked', [$lockName])->getRowArray();
        if ((int) ($got['locked'] ?? 0) !== 1) {
            throw new BusinessException('Gagal mengambil nomor dokumen. Coba lagi.');
        }

        try {
            $builder = $db->table($table)
                ->select($column)
                ->like($column, "{$prefix}-{$year}-", 'after');

            if ($tipeBarang !== null && $table === 'barang') {
                $builder->where('tipe_barang', $tipeBarang);
            }

            $row = $builder->orderBy($column, 'DESC')->get(1)->getRowArray();

            $seq = 1;
            if (! empty($row[$column])) {
                $parts = explode('-', (string) $row[$column]);
                $seq   = (int) end($parts) + 1;
            }

            return sprintf('%s-%s-%04d', $prefix, $year, $seq);
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function nextSparepart(): string
    {
        return $this->next('SP', 'barang', 'kode_barang', 'sparepart');
    }

    public function nextAksesoris(): string
    {
        return $this->next('AK', 'barang', 'kode_barang', 'aksesoris');
    }

    public function nextFakturMasuk(): string
    {
        return $this->next('FM', 'barang_masuk', 'no_faktur');
    }

    public function nextTransaksiKeluar(): string
    {
        return $this->next('TK', 'barang_keluar', 'no_transaksi');
    }
}
```

## 5. Transaksi Barang Masuk & Keluar

```php
<?php

class TransactionService
{
    protected StockService $stock;
    protected NumberingService $numbering;

    public function __construct(?StockService $stock = null, ?NumberingService $numbering = null)
    {
        $this->stock     = $stock ?? new StockService();
        $this->numbering = $numbering ?? new NumberingService();
    }

    public function processBarangMasuk(array $headerData, array $lines): int
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        $model = new BarangMasukModel();
        $model->insert([
            'no_faktur'      => $this->numbering->nextFakturMasuk(),
            'tanggal_masuk'  => $headerData['tanggal_masuk'],
            'id_supplier'    => $headerData['id_supplier'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
            'id_admin'       => $headerData['id_admin'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $idMasuk = (int) $model->getInsertID();
        $detail  = new DetailMasukModel();

        foreach ($lines['rows'] as $line) {
            $line['id_masuk'] = $idMasuk;
            $detail->insert($line);
            $this->stock->increase((int) $line['id_barang'], (int) $line['quantity']);
        }

        $db->transComplete();

        return $idMasuk;
    }

    public function processBarangKeluar(array $headerData, array $lines): int
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($lines['rows'] as $line) {
            $this->stock->assertAvailable((int) $line['id_barang'], (int) $line['quantity']);
        }

        $model = new BarangKeluarModel();
        $model->insert([
            'no_transaksi'   => $this->numbering->nextTransaksiKeluar(),
            'tanggal_keluar' => $headerData['tanggal_keluar'],
            'tujuan'         => $headerData['tujuan'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
            'id_admin'       => $headerData['id_admin'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $idKeluar = (int) $model->getInsertID();
        $detail   = new DetailKeluarModel();

        foreach ($lines['rows'] as $line) {
            $line['id_keluar'] = $idKeluar;
            $detail->insert($line);
            $this->stock->decrease((int) $line['id_barang'], (int) $line['quantity']);
        }

        $db->transComplete();

        return $idKeluar;
    }

    public function deleteBarangMasuk(int $id, array $details): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($details as $d) {
            $this->stock->decrease((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailMasukModel())->where('id_masuk', $id)->delete();
        (new BarangMasukModel())->delete($id);

        $db->transComplete();
    }

    public function deleteBarangKeluar(int $id, array $details): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($details as $d) {
            $this->stock->increase((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailKeluarModel())->where('id_keluar', $id)->delete();
        (new BarangKeluarModel())->delete($id);

        $db->transComplete();
    }

    public function updateBarangKeluar(int $id, array $existingDetails, array $headerData, array $lines): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($existingDetails as $d) {
            $this->stock->increase((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailKeluarModel())->where('id_keluar', $id)->delete();

        foreach ($lines['rows'] as $line) {
            $this->stock->assertAvailable((int) $line['id_barang'], (int) $line['quantity']);
        }

        $detail = new DetailKeluarModel();

        foreach ($lines['rows'] as $line) {
            $line['id_keluar'] = $id;
            $detail->insert($line);
            $this->stock->decrease((int) $line['id_barang'], (int) $line['quantity']);
        }

        (new BarangKeluarModel())->update($id, [
            'tanggal_keluar' => $headerData['tanggal_keluar'],
            'tujuan'         => $headerData['tujuan'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
        ]);

        $db->transComplete();
    }
}
```

## 6. Master Data Barang

```php
<?php

abstract class BarangController extends BaseController
{
    protected string $tipe;

    abstract protected function cfg(): array;

    protected function model(): BarangModel
    {
        return new BarangModel();
    }

    protected function nextKode(): string
    {
        $n = service('numbering');

        return $this->tipe === 'aksesoris' ? $n->nextAksesoris() : $n->nextSparepart();
    }

    public function index()
    {
        $c        = $this->cfg();
        $q        = trim((string) $this->request->getGet('q'));
        $kategori = trim((string) $this->request->getGet('kategori'));
        $merk     = trim((string) $this->request->getGet('merk'));
        $status   = trim((string) $this->request->getGet('status_stok'));
        $perPage  = $this->perPage();
        $m        = $this->model();

        $builder = $m->where('tipe_barang', $this->tipe)->orderBy($c['pk'], 'DESC');
        if ($q !== '') {
            $builder->groupStart()
                ->like($c['nama'], $q)
                ->orLike($c['kode'], $q)
                ->orLike('kode_manual', $q)
                ->groupEnd();
        }
        if ($kategori !== '') {
            $builder->where('kategori', $kategori);
        }
        if ($merk !== '') {
            $builder->where('merk', $merk);
        }
        if ($status !== '') {
            $builder->where('status_stok', $status);
        }

        return view('barang/index', [
            'title'     => 'Data ' . $c['label'],
            'cfg'       => $c,
            'items'     => $builder->paginate($perPage),
            'pager'     => $m->pager,
            'q'         => $q,
            'kategori'  => $kategori,
            'merk'      => $merk,
            'status'    => $status,
            'perPage'   => $perPage,
            'kategoris' => $m->distinctKategori($this->tipe),
            'merks'     => $m->distinctMerk($this->tipe),
        ]);
    }

    public function store()
    {
        $c    = $this->cfg();
        $data = $this->collect(true);
        if ($errors = $this->validateBarang($data, true)) {
            return $this->redirectWithFieldErrors('/' . $c['route'] . '/create', $errors);
        }
        $data['tipe_barang'] = $this->tipe;
        $data[$c['kode']]    = $this->nextKode();
        $data                = service('stock')->setStatusForRow($data);
        $this->model()->insert($data);

        log_activity('create', $this->tipe, (int) $this->model()->getInsertID(), 'Menambah ' . $c['label'] . ': ' . $data[$c['nama']]);

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' berhasil ditambahkan.');
    }

    public function update($id)
    {
        $c    = $this->cfg();
        $item = $this->findForTipe((int) $id);
        if (! $item) {
            return redirect()->to('/' . $c['route'])->with('error', self::MSG_NOT_FOUND);
        }
        $data = $this->collect(false);
        if ($errors = $this->validateBarang($data, false)) {
            return $this->redirectWithFieldErrors('/' . $c['route'] . '/' . $id . '/edit', $errors);
        }
        $data[$c['kode']] = $item[$c['kode']];
        $this->model()->update($id, $data);

        log_activity('update', $this->tipe, (int) $id, 'Mengubah ' . $c['label'] . ': ' . $data[$c['nama']]);

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' diperbarui.');
    }

    public function delete($id)
    {
        $c    = $this->cfg();
        $item = $this->findForTipe((int) $id);
        if (! $item) {
            return redirect()->to('/' . $c['route'])->with('error', self::MSG_NOT_FOUND);
        }
        if (method_exists($this->model(), 'hasTransactionHistory')
            && $this->model()->hasTransactionHistory((int) $id)) {
            return redirect()->to('/' . $c['route'])
                ->with('error', 'Tidak dapat dihapus: sudah ada riwayat transaksi.');
        }
        $this->model()->delete($id);

        log_activity('delete', $this->tipe, (int) $id, 'Menghapus ' . $c['label'] . ': ' . ($item[$c['nama']] ?? ('#' . $id)));

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' dihapus.');
    }

    protected function collect(bool $includeStok): array
    {
        $c = $this->cfg();

        $data = [
            'kode_manual' => $this->request->getPost('kode_manual') ?: null,
            $c['nama']    => trim((string) $this->request->getPost($c['nama'])),
            'kategori'    => trim((string) $this->request->getPost('kategori')),
            'merk'        => trim((string) $this->request->getPost('merk')),
            'satuan'      => trim((string) $this->request->getPost('satuan')) ?: 'pcs',
            'harga_beli'  => $this->parseMoney($this->request->getPost('harga_beli')),
            'harga_jual'  => $this->parseMoney($this->request->getPost('harga_jual')),
        ];

        if ($includeStok) {
            $data['stok'] = max(0, (int) $this->request->getPost('stok'));
        }

        return $data;
    }

    protected function validateBarang(array $data, bool $includeStok): array
    {
        $c      = $this->cfg();
        $errors = [];

        if ($data[$c['nama']] === '') {
            $errors[$c['nama']] = 'Nama ' . strtolower($c['label']) . ' wajib diisi.';
        }
        if ((int) $data['harga_beli'] < 0) {
            $errors['harga_beli'] = 'Harga beli tidak boleh negatif.';
        }
        if ((int) $data['harga_jual'] < 0) {
            $errors['harga_jual'] = 'Harga jual tidak boleh negatif.';
        }
        if ($includeStok && (int) ($data['stok'] ?? 0) < 0) {
            $errors['stok'] = 'Stok tidak boleh negatif.';
        }

        return $errors;
    }
}
```

## 7. Dashboard & Statistik

```php
<?php

class DashboardService
{
    public function getStatistikKpi(): array
    {
        $db = db_connect();

        $sp = (int) $db->table('barang')->where('tipe_barang', 'sparepart')->where('deleted_at', null)->countAllResults();
        $ak = (int) $db->table('barang')->where('tipe_barang', 'aksesoris')->where('deleted_at', null)->countAllResults();

        $unit = (int) $db->query(
            'SELECT COALESCE(SUM(stok),0) AS t FROM barang WHERE deleted_at IS NULL',
        )->getRow()->t;

        $nilaiPersediaan = (int) $db->query(
            'SELECT COALESCE(SUM(stok * harga_beli),0) AS t FROM barang WHERE deleted_at IS NULL',
        )->getRow()->t;

        $kritis = (int) $db->query(
            "SELECT COUNT(*) AS t FROM barang
             WHERE deleted_at IS NULL AND status_stok IN ('rendah', 'habis')",
        )->getRow()->t;

        $totalSupplier = (int) $db->table('supplier')->where('deleted_at', null)->countAllResults();

        [$from, $to] = $this->periodeDefault();

        $trxMasuk = (int) $db->table('barang_masuk')
            ->where('tanggal_masuk >=', $from)
            ->where('tanggal_masuk <=', $to)
            ->countAllResults();
        $trxKeluar = (int) $db->table('barang_keluar')
            ->where('tanggal_keluar >=', $from)
            ->where('tanggal_keluar <=', $to)
            ->countAllResults();

        return [
            'sparepart'        => $sp,
            'aksesoris'        => $ak,
            'unit_stok'        => $unit,
            'nilai_persediaan' => $nilaiPersediaan,
            'total_supplier'   => $totalSupplier,
            'trx_bulan'        => $trxMasuk + $trxKeluar,
            'stok_kritis'      => $kritis,
        ];
    }

    public function getStatusStok(): array
    {
        $db   = db_connect();
        $rows = $db->query('
            SELECT status_stok, COUNT(*) AS jml
            FROM barang
            WHERE deleted_at IS NULL
            GROUP BY status_stok
        ')->getResultArray();

        $out = ['aman' => 0, 'rendah' => 0, 'habis' => 0];
        foreach ($rows as $r) {
            $out[$r['status_stok']] = (int) $r['jml'];
        }

        return $out;
    }

    public function getTrenTransaksi(): array
    {
        $db = db_connect();

        [$from, $to] = $this->periodeDefault();

        $query = $db->query("
            SELECT tanggal,
                   SUM(CASE WHEN jenis = 'masuk' THEN 1 ELSE 0 END) AS masuk,
                   SUM(CASE WHEN jenis = 'keluar' THEN 1 ELSE 0 END) AS keluar
            FROM (
                SELECT tanggal_masuk AS tanggal, 'masuk' AS jenis FROM barang_masuk WHERE tanggal_masuk >= ? AND tanggal_masuk <= ?
                UNION ALL
                SELECT tanggal_keluar AS tanggal, 'keluar' AS jenis FROM barang_keluar WHERE tanggal_keluar >= ? AND tanggal_keluar <= ?
            ) t
            GROUP BY tanggal
        ", [$from, $to, $from, $to])->getResultArray();

        $indexed = array_column($query, null, 'tanggal');
        $out     = [];

        $cursor = strtotime($from);
        $last   = strtotime($to);
        while ($cursor <= $last) {
            $d     = date('Y-m-d', $cursor);
            $out[] = [
                'tanggal' => $d,
                'masuk'   => isset($indexed[$d]) ? (int) $indexed[$d]['masuk'] : 0,
                'keluar'  => isset($indexed[$d]) ? (int) $indexed[$d]['keluar'] : 0,
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        return $out;
    }

    private function periodeDefault(): array
    {
        $to = date('Y-m-d');

        return [date('Y-m-d', strtotime('-13 days', strtotime($to))), $to];
    }
}
```

## 8. Laporan PDF

```php
<?php

class LaporanController extends BaseController
{
    public function index()
    {
        return view('laporan/index', ['title' => 'Laporan']);
    }

    public function pdf()
    {
        $jenis   = (string) $this->request->getPost('jenis');
        $allowed = ['stok', 'masuk', 'keluar'];
        if (! in_array($jenis, $allowed, true)) {
            return redirect()->to('/laporan')->with('error', 'Jenis laporan tidak valid.');
        }

        $awal  = $this->parseDateFilter($this->request->getPost('tanggal_awal')) ?: date('Y-m-01');
        $akhir = $this->parseDateFilter($this->request->getPost('tanggal_akhir')) ?: date('Y-m-d');
        if (strtotime($awal) > strtotime($akhir)) {
            [$awal, $akhir] = [$akhir, $awal];
        }
        $db = db_connect();

        if ($jenis === 'stok') {
            $items = $db->query('
                SELECT tipe_barang AS tipe, kode_barang AS kode, nama_barang AS nama, stok, status_stok
                FROM barang
                WHERE deleted_at IS NULL
                ORDER BY tipe, nama
            ')->getResultArray();
            $view     = 'pdf/laporan_stok';
            $filename = 'Laporan-Stok-' . date('Ymd') . '.pdf';
        } elseif ($jenis === 'masuk') {
            $items = $db->table('barang_masuk bm')
                ->select('bm.*, s.nama_supplier')
                ->join('supplier s', 's.id_supplier = bm.id_supplier')
                ->where('bm.tanggal_masuk >=', $awal)
                ->where('bm.tanggal_masuk <=', $akhir)
                ->orderBy('bm.tanggal_masuk')
                ->get()->getResultArray();
            $view     = 'pdf/laporan_masuk';
            $filename = 'Laporan-Masuk-' . $awal . '_' . $akhir . '.pdf';
        } else {
            $items = $db->table('barang_keluar')
                ->where('tanggal_keluar >=', $awal)
                ->where('tanggal_keluar <=', $akhir)
                ->orderBy('tanggal_keluar')
                ->get()->getResultArray();
            $view     = 'pdf/laporan_keluar';
            $filename = 'Laporan-Keluar-' . $awal . '_' . $akhir . '.pdf';
        }

        return $this->pdfResponse(
            service('pdf')->render($view, compact('items', 'awal', 'akhir')),
            $filename,
        );
    }
}
```

```php
<?php

class PdfService
{
    public function render(
        string $view,
        array $data = [],
        string $paper = 'A4',
        string $orientation = 'portrait',
    ): string {
        $data['store'] ??= config(Store::class);
        $data['pdfMeta'] ??= $this->defaultMeta($data);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data));
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    protected function defaultMeta(array $data): array
    {
        $nama  = session('nama');
        $user  = session('username');
        $level = session('level');

        $by = is_string($nama) && $nama !== ''
            ? $nama
            : (is_string($user) && $user !== '' ? $user : 'Sistem');

        return [
            'printed_at'    => date('d-m-Y H:i') . ' WIB',
            'printed_by'    => $by,
            'printed_level' => is_string($level) && $level !== '' ? $level : null,
            'doc_ref'       => isset($data['docRef']) ? (string) $data['docRef'] : null,
        ];
    }
}
```

## 9. Profil & Foto Pengguna

```php
<?php

class ProfileController extends BaseController
{
    private const ROUTE_INDEX = '/profil';

    private const MAX_FOTO_MB = 2;

    private const ALLOWED_FOTO_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    protected AdminModel $model;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    public function update()
    {
        $id = (int) session('id_admin');
        if (! $this->model->find($id)) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $data = [
            'nama'          => trim((string) $this->request->getPost('nama')),
            'email'         => trim((string) $this->request->getPost('email')),
            'nomor_telepon' => trim((string) $this->request->getPost('nomor_telepon')),
        ];

        $errors = $this->validateProfile($data, $id);
        if ($errors !== []) {
            return $this->redirectBackWithFieldErrors($errors);
        }

        $this->model->update($id, $data);
        session()->set('nama', $data['nama']);

        log_activity('update', 'profil', $id, 'Memperbarui profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateFoto()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $file = $this->request->getFile('foto');
        if ($file === null || ! $file->isValid()) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Berkas foto tidak valid.']);
        }
        if ($file->getSizeByUnit('mb') > self::MAX_FOTO_MB) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Ukuran foto maksimal ' . self::MAX_FOTO_MB . ' MB.']);
        }
        if (! in_array($file->getMimeType(), self::ALLOWED_FOTO_MIME, true)) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Format harus JPG, PNG, atau WebP.']);
        }

        $dir = rtrim(WRITEPATH, '/\\') . '/uploads/avatars';
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Gagal menyiapkan folder unggahan.']);
        }

        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        $this->deleteFotoFile($user['foto'] ?? null);
        $this->model->update($id, ['foto' => $newName]);
        session()->set('foto', $newName);

        log_activity('update', 'profil', $id, 'Memperbarui foto profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Foto profil diperbarui.');
    }

    public function deleteFoto()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $this->deleteFotoFile($user['foto'] ?? null);
        $this->model->update($id, ['foto' => null]);
        session()->remove('foto');

        log_activity('update', 'profil', $id, 'Menghapus foto profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Foto profil dihapus.');
    }

    public function serveFoto()
    {
        $user = $this->model->find(session('id_admin'));
        $name = basename((string) ($user['foto'] ?? ''));
        $path = rtrim(WRITEPATH, '/\\') . '/uploads/avatars/' . $name;
        if ($name === '' || ! is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody((string) file_get_contents($path));
    }

    private function deleteFotoFile(?string $name): void
    {
        $safe = basename((string) $name);
        if ($safe === '') {
            return;
        }
        $path = rtrim(WRITEPATH, '/\\') . '/uploads/avatars/' . $safe;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function updatePassword()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $passwordLama    = (string) $this->request->getPost('password_lama');
        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        $errors = [];
        if (! password_verify($passwordLama, $user['password'])) {
            $errors['password_lama'] = 'Password lama tidak sesuai.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Password baru minimal 6 karakter.';
        }
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Konfirmasi password tidak cocok.';
        }

        if ($errors !== []) {
            return $this->redirectBackWithFieldErrors($errors);
        }

        $this->model->update($id, ['password' => $password]);

        log_activity('update', 'profil', $id, 'Mengubah password');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Password berhasil diubah.');
    }

    protected function validateProfile(array $data, int $id): array
    {
        $errors = [];
        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($this->model->where('email', $data['email'])->where('id_admin !=', $id)->first()) {
            $errors['email'] = 'Email sudah digunakan.';
        }

        return $errors;
    }
}
```

## 10. Audit Trail

```php
<?php

class AuditService
{
    private ActivityLogModel $model;

    public function __construct(?ActivityLogModel $model = null)
    {
        $this->model = $model ?? new ActivityLogModel();
    }

    public function log(string $action, ?string $entity = null, ?int $entityId = null, ?string $description = null): void
    {
        try {
            $this->model->insert([
                'id_admin'    => session('id_admin') !== null ? (int) session('id_admin') : null,
                'nama_admin'  => session('nama') !== null ? (string) session('nama') : null,
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip_address'  => service('request')->getIPAddress(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'AuditService gagal mencatat: ' . $e->getMessage());
        }
    }
}
```

```php
<?php

if (! function_exists('log_activity')) {
    function log_activity(string $action, ?string $entity = null, ?int $entityId = null, ?string $description = null): void
    {
        service('audit')->log($action, $entity, $entityId, $description);
    }
}
```
