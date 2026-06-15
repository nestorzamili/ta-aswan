# Bab III — Alur & Antarmuka

[← Kembali ke README](README.md) · [Kebutuhan sistem](02-kebutuhan-sistem.md)

Hak akses per level: matriks tunggal di [02-kebutuhan-sistem.md §1](02-kebutuhan-sistem.md#1-aktor--hak-akses).

---

## 1. Struktur Navigasi

### Sidebar Menu

Urutan menu sesuai mockup proposal (Gambar 3.2):

| No | Menu | Route CI4 | Keterangan |
|----|------|-----------|------------|
| 1 | Dashboard | `/dashboard` | KPI & grafik |
| 2 | Data Sparepart | `/sparepart` | CRUD sparepart |
| 3 | Data Aksesoris | `/aksesoris` | CRUD aksesoris |
| 4 | Barang Masuk | `/barang-masuk` | Transaksi masuk |
| 5 | Barang Keluar | `/barang-keluar` | Transaksi keluar |
| 6 | Stok Barang | `/stok` | Monitoring stok |
| 7 | Supplier | `/supplier` | CRUD supplier |
| 8 | Laporan | `/laporan` | Generate PDF |
| 9 | Pengguna | `/pengguna` | CRUD pengguna (admin only) |
| 10 | Logout | `/logout` | Keluar sistem |

### Layout Umum

Setiap halaman (kecuali login) memiliki:

- **Sidebar** — navigasi menu
- **Header** — tanggal & waktu real-time, info user
- **Content area** — konten modul
- **Footer** — nama sistem & tahun

### Halaman Publik vs Terproteksi

| Halaman | Akses |
|---------|-------|
| Login | Publik |
| Lupa Password / Reset Password | Publik |
| Semua menu lainnya | Session required (`admin` atau `karyawan`) |

Tombol **Hapus** pada transaksi & master data hanya ditampilkan untuk **admin**. Menu **Pengguna** disembunyikan untuk karyawan.

---

## 2. Alur Aktivitas

Berlaku untuk admin dan karyawan (dengan batasan hak akses).

### Diagram Utama

```mermaid
flowchart TD
    start([Mulai]) --> login[Login]
    login --> valid{Valid?}
    valid -->|Tidak| error[Pesan error] --> login
    valid -->|Ya| dashboard[Dashboard]
    dashboard --> sparepart[Kelola Sparepart]
    dashboard --> aksesoris[Kelola Aksesoris]
    dashboard --> masuk[Barang Masuk]
    dashboard --> keluar[Barang Keluar]
    dashboard --> stok[Monitoring Stok]
    dashboard --> supplier[Kelola Supplier]
    dashboard --> laporan[Laporan PDF]
    dashboard --> pengguna[Kelola Pengguna]
    sparepart --> dashboard
    aksesoris --> dashboard
    masuk --> updateStok1[Auto-update stok +]
    keluar --> updateStok2[Auto-update stok -]
    updateStok1 --> dashboard
    updateStok2 --> dashboard
    stok --> dashboard
    supplier --> dashboard
    laporan --> dashboard
    pengguna --> dashboard
    dashboard --> logout[Logout]
    logout --> endNode([Selesai])
```

### Login

```mermaid
flowchart TD
    A([Start]) --> B[Buka halaman login]
    B --> C[Input username dan password]
    C --> D{Sistem validasi}
    D -->|Valid| E[Redirect dashboard]
    D -->|Invalid| F[Pesan error]
    F --> C
    E --> G([End])
```

### Barang Masuk

Tidak dapat diedit setelah disimpan.

```mermaid
flowchart TD
    A([Start]) --> B[Buka Barang Masuk]
    B --> C[no_faktur auto-generate]
    C --> D[Isi tanggal, supplier, item]
    D --> E{Valid?}
    E -->|Ya| F[Simpan transaksi]
    F --> G[Stok bertambah, update status]
    G --> H([End])
    E -->|Tidak| I[Pesan error]
    I --> D
```

### Barang Keluar

```mermaid
flowchart TD
    A([Start]) --> B{Buka Barang Keluar}
    B -->|Tambah| C[no_transaksi auto-generate]
    B -->|Edit admin| C2[Load transaksi existing]
    C --> D[Isi tujuan, item]
    C2 --> D2[Ubah item — recalculate stok]
    D --> E{Stok cukup?}
    D2 --> E
    E -->|Ya| F[Simpan]
    F --> G[harga dari harga_jual master]
    G --> H[Stok berkurang, update status]
    H --> I([End])
    E -->|Tidak| J[Pesan error]
    J --> D
```

### Laporan

1. Pilih jenis (stok / masuk / keluar) + filter periode
2. Query database on-the-fly
3. Render Dompdf → download
