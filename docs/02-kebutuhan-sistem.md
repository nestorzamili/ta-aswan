# Bab III — Kebutuhan Sistem

[← Kembali ke README](README.md)

Dokumen ini memuat aktor, aturan bisnis, dan spesifikasi modul. **Aturan bisnis** adalah sumber kebenaran desain implementasi.

---

## 1. Aktor & Hak Akses

### Aktor

| Aktor | Level | Deskripsi |
|-------|-------|-----------|
| **Admin** | `admin` | Pemilik / pengelola utama; hak akses penuh |
| **Karyawan** | `karyawan` | Staff operasional; hak akses terbatas |

### Matriks Hak Akses

| Modul | Admin | Karyawan |
|-------|:-----:|:--------:|
| Dashboard | ✓ | ✓ |
| Data Sparepart — lihat | ✓ | ✓ |
| Data Sparepart — tambah/edit | ✓ | ✓ |
| Data Sparepart — hapus¹ | ✓ | ✗ |
| Data Aksesoris — lihat | ✓ | ✓ |
| Data Aksesoris — tambah/edit | ✓ | ✓ |
| Data Aksesoris — hapus¹ | ✓ | ✗ |
| Barang Masuk — tambah/lihat/cetak | ✓ | ✓ |
| Barang Masuk — hapus | ✓ | ✗ |
| Barang Keluar — tambah/lihat/cetak | ✓ | ✓ |
| Barang Keluar — edit | ✓ | ✗ |
| Barang Keluar — hapus | ✓ | ✗ |
| Monitoring Stok | ✓ | ✓ |
| Supplier — lihat | ✓ | ✓ |
| Supplier — tambah/edit/hapus | ✓ | ✗ |
| Laporan — lihat/cetak | ✓ | ✓ |
| Kelola Pengguna | ✓ | ✗ |
| Lupa Password (email) | ✓ | ✓ |

¹ Hapus barang **ditolak** jika sudah ada riwayat transaksi (semua role).

Implementasi: CI4 **Filter** per route (`AuthFilter` + `RoleFilter`).

### Responden Penelitian

| Nama | Jabatan | Level Sistem |
|------|---------|--------------|
| Perubahan Loi | Admin Utama | `admin` |
| Capan Zalogo | Karyawan | `karyawan` |
| Rizky Sarumaha | Karyawan | `karyawan` |

---

## 2. Aturan Bisnis

### Database

| Item | Nilai |
|------|-------|
| Nama database | `db_inventory_android` |
| Engine | MySQL 8.4 |

### Supplier

Tabel **`supplier` terpisah**, direferensikan oleh transaksi barang masuk:

- `barang_masuk.id_supplier` → FK ke `supplier.id_supplier`
- CRUD supplier: hanya **admin**
- Karyawan: lihat data supplier (referensi saat input barang masuk)

### Detail Transaksi

| Tabel | Kolom kunci |
|-------|-------------|
| `detail_masuk` | `id_masuk`, `tipe_barang`, `id_barang`, `quantity`, `harga_satuan`, `subtotal` |
| `detail_keluar` | `id_keluar`, `tipe_barang`, `id_barang`, `quantity`, `harga_satuan`, `subtotal` |

- `tipe_barang`: `sparepart` atau `aksesoris`
- `id_barang`: merujuk ke `id_sparepart` atau `id_aksesoris` sesuai tipe

### Stok & Threshold

| Kondisi | Aturan |
|---------|--------|
| Stok habis | `stok = 0` → `status_stok = habis` |
| Stok rendah | `stok < 3` → `status_stok = rendah` |
| Stok aman | `stok >= 3` → `status_stok = aman` |

**Update otomatis** saat transaksi **disimpan** (bukan saat dihapus).

### Hapus Transaksi

| Aturan | Nilai |
|--------|-------|
| Hapus transaksi masuk/keluar | **Tidak mengembalikan stok** |
| Dampak | Data transaksi terhapus; stok tetap pada nilai terakhir |

> Hanya role **admin** yang dapat menghapus transaksi.

### Penomoran Otomatis

| Entitas | Field | Format (contoh) | Perilaku |
|---------|-------|-----------------|----------|
| Barang masuk | `no_faktur` | `FM-2026-0001` | Auto-generate, unik, read-only setelah simpan |
| Barang keluar | `no_transaksi` | `TK-2026-0001` | Auto-generate, unik, read-only setelah simpan |
| Sparepart | `kode_sparepart` | `SP-2026-0001` | Auto-generate sebagai default di form |
| Aksesoris | `kode_aksesoris` | `AK-2026-0001` | Auto-generate sebagai default di form |

Setiap sparepart/aksesoris memiliki **`kode_sparepart`/`kode_aksesoris`** (auto, wajib) dan **`kode_manual`** (input opsional, kode referensi toko/supplier).

### Master Data — Proteksi Hapus

Barang yang sudah memiliki riwayat di `detail_masuk` atau `detail_keluar` **tidak boleh dihapus**. Sistem cek relasi sebelum hapus → tolak dengan pesan error.

### Transaksi Barang Keluar — Edit

| Aturan | Nilai |
|--------|-------|
| Edit transaksi keluar | Hanya **admin** |
| Barang masuk | **Tidak dapat diedit** setelah disimpan |
| Saat edit keluar | Stok di-recalculate: kembalikan qty lama → kurangi qty baru |
| Harga item keluar | Diambil dari `harga_jual` master (read-only di form transaksi) |

### Laporan & Cetak PDF

Laporan di-**generate on-the-fly** dari data transaksi & stok — **tanpa tabel `laporan`**.

Alur: pilih jenis + filter periode → query DB → render PDF (Dompdf) → download.

| Jenis | Output |
|-------|--------|
| Laporan stok / masuk / keluar | Download PDF |
| Cetak faktur barang masuk / transaksi keluar | PDF |

> Kelas **Laporan** di Class Diagram diimplementasikan sebagai **LaporanService** (service/controller), bukan entitas tabel.

### Lupa Password & Email

| Item | Nilai |
|------|-------|
| Provider | Resend.com — dev & production |
| Masa berlaku token | **60 menit** |
| Token | Sekali pakai, tabel `password_reset_tokens` |

Alur: input email → generate token → kirim link via Resend → set password baru → token di-mark `used_at`.

Konfigurasi Resend (domain & API key **menyusul**): [06-implementasi-pengujian.md](06-implementasi-pengujian.md).

---

## 3. Modul Fitur

| No | Modul | Operasi Utama |
|----|-------|---------------|
| 1 | Autentikasi | Login, lupa password, logout |
| 2 | Dashboard | Ringkasan stok, grafik, transaksi terbaru |
| 3 | Data Sparepart | CRUD + pencarian & filter |
| 4 | Data Aksesoris | CRUD + pencarian & filter |
| 5 | Barang Masuk | Tambah transaksi, detail, cetak, hapus |
| 6 | Barang Keluar | Tambah transaksi, detail, cetak, hapus |
| 7 | Monitoring Stok | Tampilan stok real-time |
| 8 | Supplier | CRUD pemasok |
| 9 | Laporan | Stok, masuk, keluar + cetak |
| 10 | Pengguna | CRUD pengguna (admin & karyawan) |

### 3.1 Autentikasi

| Fitur | Deskripsi |
|-------|-----------|
| Login | Username + password, validasi kredensial |
| Toggle password | Show/hide password |
| Lupa password | Reset via email (aturan di §2) |
| Logout | Keluar sistem dengan aman |
| Kontrol akses | Role-based: `admin` / `karyawan` |

### 3.2 Dashboard

UI: Bootstrap 5.3 + Chart.js 4 + Bootstrap Icons. Layout card-based, responsive.

```
┌─────────────────────────────────────────────────────────┐
│ Header: judul, tanggal/waktu real-time, nama user       │
├──────────┬──────────┬──────────┬──────────────────────┤
│ KPI 1    │ KPI 2    │ KPI 3    │ KPI 4                │
├────────────────────────────┬────────────────────────────┤
│ Donut: Status Stok         │ Bar H: Stok per Kategori  │
├────────────────────────────┴────────────────────────────┤
│ Line: Tren Transaksi 14 Hari (masuk vs keluar)          │
├────────────────────────────┬────────────────────────────┤
│ Tabel: Stok Menipis        │ Tabel: Transaksi Terbaru  │
└────────────────────────────┴────────────────────────────┘
```

| Komponen | Visual | Metrik |
|----------|--------|--------|
| KPI cards (×4) | Angka + ikon | Total sparepart, aksesoris, unit stok, transaksi bulan ini |
| Status stok | **Donut** | Proporsi aman / rendah / habis |
| Stok per kategori | **Horizontal bar** | Top 8 kategori by `SUM(stok)` |
| Tren transaksi | **Line** | Masuk vs keluar, 14 hari terakhir |
| Stok menipis | Tabel + badge | Item `rendah` & `habis`, max 10 |
| Transaksi terbaru | Tabel timeline | 10 transaksi terakhir |

Endpoint internal (JSON) untuk Chart.js:

| Endpoint | Return |
|----------|--------|
| `GET /api/dashboard/kpi` | 4 angka KPI |
| `GET /api/dashboard/status-stok` | `{ aman, rendah, habis }` |
| `GET /api/dashboard/stok-kategori` | `[{ kategori, total }]` |
| `GET /api/dashboard/tren-transaksi` | `[{ tanggal, masuk, keluar }]` |

### 3.3 Data Sparepart

| Field | Tipe | Keterangan |
|-------|------|------------|
| kode_sparepart | string | Auto-generate (SP-YYYY-NNNN), unik |
| kode_manual | string | Input manual opsional |
| nama_sparepart | string | Nama sparepart |
| kategori | string | LCD, Baterai, Touchscreen, IC, Kamera, dll. |
| merk, satuan | string | — |
| harga_beli, harga_jual | decimal | — |
| stok | integer | Jumlah persediaan |
| status_stok | enum | `aman` (≥3) / `rendah` (<3) / `habis` (0) |

**Fitur tambahan:** Pencarian (nama/kode), filter kategori/merk/status stok, pagination.

### 3.4 Data Aksesoris

Struktur identik sparepart. Kategori contoh: Charger, Audio, Pelindung Layar, Case, Kabel. Kode: `AK-YYYY-NNNN`.

### 3.5 Barang Masuk

**Operasi:** Tambah, Lihat detail, Cetak PDF, Hapus — **tidak dapat diedit**

| Field Header | Keterangan |
|--------------|------------|
| no_faktur | Auto-generate (FM-YYYY-NNNN), read-only |
| tanggal_masuk | Tanggal transaksi |
| supplier | FK ke `supplier` |
| total_item, total_quantity, total_harga | Agregat dari detail |
| id_admin | Pencatat |

**Detail item:** `tipe_barang`, `id_barang`, `quantity`, `harga_satuan`, `subtotal`. Auto-update stok saat simpan.

### 3.6 Barang Keluar

**Operasi:** Tambah, **Edit** (admin only), Lihat detail, Cetak PDF, Hapus

| Field Header | Keterangan |
|--------------|------------|
| no_transaksi | Auto-generate (TK-YYYY-NNNN), read-only |
| tanggal_keluar | Tanggal transaksi |
| tujuan | Pelanggan / tujuan |
| total_item, total_quantity, total_harga | Agregat dari detail |
| id_admin | Pencatat |

`harga_satuan` dari `harga_jual` master (read-only). Validasi stok tidak negatif.

### 3.7 Monitoring Stok

Tampilan sparepart & aksesoris real-time dengan status aman / rendah / habis. Terhubung otomatis dengan transaksi masuk/keluar.

### 3.8 Supplier

CRUD hanya **admin**. Karyawan lihat saat input barang masuk.

### 3.9 Laporan Inventory

| Jenis | Parameter |
|-------|-----------|
| Stok / masuk / keluar | `tanggal_awal`, `tanggal_akhir` |

Generate on-the-fly → download PDF (Dompdf). Cetak transaksi individual juga PDF.

### 3.10 Manajemen Pengguna

| Field | Keterangan |
|-------|------------|
| nama, username, password | Kredensial |
| email | **Wajib** — reset password |
| nomor_telepon | Kontak |
| level | `admin` / `karyawan` |
| status | `aktif` / `nonaktif` |

Operasi hanya **admin**: tambah, edit, hapus. Pencarian & pagination.
