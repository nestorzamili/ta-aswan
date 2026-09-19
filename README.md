# Sistem Informasi Inventaris Toko (Android Service)

Aplikasi berbasis web untuk mengelola data inventaris (sparepart dan aksesori), mencatat transaksi barang masuk dan keluar, serta memantau pergerakan stok secara *real-time*. Proyek ini dibangun sebagai pemenuhan Tugas Akhir.

## Teknologi Utama
- **Framework:** CodeIgniter 4 (PHP 8.2+)
- **Database:** MySQL
- **Frontend:** Bootstrap 5, Vanilla CSS, Chart.js
- **Laporan:** Dompdf (Ekspor PDF)

## Fitur
- Master data sparepart & aksesori, supplier, dan pengguna.
- Transaksi barang masuk & keluar dengan pembaruan stok atomik.
- Dashboard dengan **filter periode** dan KPI (termasuk **nilai persediaan** dan **total supplier**).
- **Profil & ganti password** untuk semua pengguna (verifikasi password lama).
- **Audit trail** aktivitas (create/update/delete + login/logout) tersimpan di tabel `activity_logs`.
- Laporan PDF (stok, barang masuk, barang keluar).
- Kualitas kode terjaga otomatis via **PHP-CS-Fixer** dan **PHPStan** di CI.

## Prasyarat (Dev Lokal)

Aplikasi dijalankan langsung di host (tanpa Docker). Yang perlu terpasang:

- **PHP 8.2+** dengan ekstensi: `intl`, `mysqli`, `pdo_mysql`, `mbstring`, `curl`, `zip`, `fileinfo`, `gd`.
- **Composer 2**.
- **MySQL** yang dapat diakses dari host (mis. container MySQL yang sudah ada dengan port `3306` di-expose ke `localhost`).

### Contoh instalasi PHP & Composer (Windows)

Pilih salah satu:

**Opsi A — Laragon (disarankan):**
1. Unduh & pasang [Laragon](https://laragon.org/download/) (sudah menyertakan PHP 8.2+ dan Composer).
2. Buka **Menu → PHP → Extensions**, pastikan ekstensi berikut aktif: `intl`, `mysqli`, `pdo_mysql`, `mbstring`, `curl`, `zip`, `fileinfo`, `gd`.

**Opsi B — XAMPP:**
1. Unduh & pasang [XAMPP](https://www.apachefriends.org/) dengan PHP 8.2+.
2. Pasang [Composer for Windows](https://getcomposer.org/Composer-Setup.exe).
3. Aktifkan ekstensi di `C:\xampp\php\php.ini` (hilangkan `;` di depan baris):
   ```ini
   extension=intl
   extension=mysqli
   extension=pdo_mysql
   extension=gd
   extension=curl
   extension=zip
   extension=mbstring
   extension=fileinfo
   ```

**Opsi C — PHP manual:**
1. Unduh [PHP 8.2+ (Non Thread Safe / Thread Safe)](https://windows.php.net/download/), ekstrak (mis. `C:\php`), tambahkan ke `PATH`.
2. Salin `php.ini-development` menjadi `php.ini`, lalu aktifkan ekstensi seperti pada Opsi B.
3. Pasang [Composer for Windows](https://getcomposer.org/Composer-Setup.exe).

Verifikasi ekstensi (PowerShell / CMD):
```powershell
php -m
```
Pastikan `intl`, `mysqli`, `pdo_mysql`, `mbstring`, `curl`, `zip`, `fileinfo`, dan `gd` muncul di daftar.

## Panduan Instalasi (Dev Lokal)

### 1. Persiapan Database
Gunakan MySQL yang tersedia (mis. container MySQL existing dengan port `3306` ter-expose ke host), lalu buat database `db_inventory`.

### 2. Instalasi Dependensi
```bash
composer install
```

### 3. Konfigurasi Environment
1. Salin file `env` menjadi `.env`.
2. Sesuaikan koneksi database di `.env`. Gunakan `127.0.0.1` (bukan `localhost`) agar driver MySQLi memakai koneksi TCP:
   ```env
   app_baseURL = 'http://localhost:8080'
   database.default.hostname = 127.0.0.1
   database.default.database = db_inventory
   database.default.username = <user>
   database.default.password = <password>
   database.default.port = 3306
   ```

### 4. Migrasi & Seed Data
```bash
php spark migrate
php spark db:seed AdminSeeder
```

### 5. Jalankan Aplikasi
```bash
php spark serve
```
Buka browser dan akses URL: **http://localhost:8080**

### Kualitas Kode
```bash
composer cs        # cek format kode (PHP-CS-Fixer)
composer cs:fix    # perbaiki format kode
composer analyze   # analisis statis (PHPStan)
```

## Akun Login Default
Gunakan kredensial berikut untuk masuk pertama kali:
- **Username:** `admin`
- **Password:** `secret` (atau sesuai `admin.defaultPassword` di `.env`)

## CI/CD

- PR → [`.github/workflows/ci.yml`](.github/workflows/ci.yml)
- main → [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) via [gha-workflows](https://github.com/nestorzamili/gha-workflows)

Environment `production` secrets: `SSH_CONFIG`, `ENV_FILE` (CI4 `.env` → `/var/www/html/.env`).

Buat folder di vm prod
`mkdir -p /var/lib/ta-aswan/writable/{cache,debugbar,logs,session,uploads} && chown -R 82:82 /var/lib/ta-aswan/writable`