# Sistem Informasi Inventaris Toko (Android Service)

Aplikasi berbasis web untuk mengelola data inventaris (sparepart dan aksesoris), mencatat transaksi barang masuk dan keluar, serta memantau pergerakan stok secara *real-time*. Proyek ini dibangun sebagai pemenuhan Tugas Akhir.

## Teknologi Utama
- **Framework:** CodeIgniter 4 (PHP 8.2+)
- **Database:** MySQL
- **Frontend:** Bootstrap 5, Vanilla CSS, Chart.js
- **Laporan:** Dompdf (Ekspor PDF)

## Panduan Instalasi (Lokal)

Langkah-langkah untuk menjalankan aplikasi menggunakan **XAMPP** atau **Laragon**:

### 1. Persiapan Database
Buat database baru di MySQL (misalnya beri nama `db_inventory`).

### 2. Instalasi Dependensi
Buka terminal/Command Prompt di dalam folder proyek ini, lalu jalankan perintah:
```bash
composer install
```

### 3. Konfigurasi Environment
1. Salin (copy) file bernama `env` menjadi `.env`.
2. Buka file `.env` dan sesuaikan pengaturan koneksi database (hilangkan tanda pagar `#` di awal baris):
   ```env
   database.default.hostname = localhost
   database.default.database = db_inventory
   database.default.username = root
   database.default.password = 
   ```

### 4. Migrasi & Seed Data
Jalankan perintah berikut di terminal untuk membuat struktur tabel dan mengisi akun pengguna *default*:
```bash
php spark migrate
php spark db:seed AdminSeeder
```

### 5. Jalankan Aplikasi
Jalankan server pengembangan bawaan CodeIgniter:
```bash
php spark serve
```
Buka browser dan akses URL: **http://localhost:8080**

---

## Akun Login Default
Gunakan kredensial berikut untuk masuk pertama kali:
- **Username:** `admin`
- **Password:** `secret`

## CI/CD

- PR → [`.github/workflows/ci.yml`](.github/workflows/ci.yml)
- main → [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) via [gha-workflows](https://github.com/nestorzamili/gha-workflows)

Environment `production` secrets: `SSH_CONFIG`, `GHCR_USERNAME`, `GHCR_TOKEN`, `ENV_FILE` (CI4 `.env` → `/var/www/html/.env`).

VM (sekali): Docker, network `proxy`, Caddy,  
`mkdir -p /var/lib/ta-aswan/writable && chown -R 82:82 /var/lib/ta-aswan/writable`  
(www-data UID 82 on Alpine).

## Build notes

Docker builds target the VM arch from `SSH_CONFIG` (`platform=`).  
Composer runs on the runner native arch; PHP extensions install in the final image (slow under QEMU when runner is amd64 and VM is arm64).