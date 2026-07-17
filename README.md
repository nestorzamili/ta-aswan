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
