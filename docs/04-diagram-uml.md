# Bab III — Diagram UML

[← Kembali ke README](README.md)

Deliverable desain untuk skripsi. Activity diagram ada di [03-alur-antarmuka.md](03-alur-antarmuka.md).

---

## 1. Use Case Diagram

### 1.1 Admin

Aktor **Admin** (`admin`) — hak akses penuh termasuk kelola pengguna, hapus data, dan edit transaksi keluar.

```mermaid
flowchart LR
    admin((Admin))
    subgraph system [Sistem Inventory]
        UC1[Login]
        UC2[Kelola Sparepart]
        UC3[Kelola Aksesoris]
        UC4[Barang Masuk]
        UC5[Barang Keluar]
        UC6[Monitoring Stok]
        UC7[Kelola Supplier]
        UC8[Laporan Inventory]
        UC9[Kelola Pengguna]
        UC10[Lupa Password]
        UC11[Logout]
    end
    admin --> UC1
    admin --> UC2
    admin --> UC3
    admin --> UC4
    admin --> UC5
    admin --> UC6
    admin --> UC7
    admin --> UC8
    admin --> UC9
    admin --> UC10
    admin --> UC11
```

| Use Case Admin | Operasi |
|----------------|---------|
| Kelola Sparepart | Lihat, tambah, edit, hapus¹ |
| Kelola Aksesoris | Lihat, tambah, edit, hapus¹ |
| Barang Masuk | Tambah, lihat, cetak, **hapus** |
| Barang Keluar | Tambah, **edit**, lihat, cetak, **hapus** |
| Kelola Supplier | Lihat, tambah, edit, hapus |
| Kelola Pengguna | Lihat, tambah, edit, hapus |

¹ Hapus barang ditolak jika sudah ada riwayat transaksi.

### 1.2 Karyawan

Aktor **Karyawan** (`karyawan`) — operasional harian tanpa kelola pengguna, hapus transaksi, dan edit transaksi keluar.

```mermaid
flowchart LR
    karyawan((Karyawan))
    subgraph system [Sistem Inventory]
        UC1[Login]
        UC2[Kelola Sparepart]
        UC3[Kelola Aksesoris]
        UC4[Barang Masuk]
        UC5[Barang Keluar]
        UC6[Monitoring Stok]
        UC7[Lihat Supplier]
        UC8[Laporan Inventory]
        UC9[Lupa Password]
        UC10[Logout]
    end
    karyawan --> UC1
    karyawan --> UC2
    karyawan --> UC3
    karyawan --> UC4
    karyawan --> UC5
    karyawan --> UC6
    karyawan --> UC7
    karyawan --> UC8
    karyawan --> UC9
    karyawan --> UC10
```

| Use Case Karyawan | Operasi |
|-------------------|---------|
| Kelola Sparepart | Lihat, tambah, edit |
| Kelola Aksesoris | Lihat, tambah, edit |
| Barang Masuk | Tambah, lihat, cetak |
| Barang Keluar | Tambah, lihat, cetak |
| Lihat Supplier | Referensi saat input barang masuk |

| ID | Use Case | Admin | Karyawan |
|----|----------|:-----:|:--------:|
| UC01 | Login | ✓ | ✓ |
| UC02 | Kelola Sparepart | CRUD penuh | Lihat, tambah, edit |
| UC03 | Kelola Aksesoris | CRUD penuh | Lihat, tambah, edit |
| UC04 | Barang Masuk | + hapus | Tambah, lihat, cetak |
| UC05 | Barang Keluar | + hapus, edit | Tambah, lihat, cetak |
| UC06 | Monitoring Stok | ✓ | ✓ |
| UC07 | Kelola Supplier | CRUD | Lihat saja |
| UC08 | Laporan Inventory | ✓ | ✓ |
| UC09 | Kelola Pengguna | ✓ | ✗ |
| UC10 | Lupa Password | ✓ | ✓ |
| UC11 | Logout | ✓ | ✓ |

| Relasi | Keterangan |
|--------|------------|
| Login `<<include>>` semua UC | Session aktif diperlukan |
| Barang Masuk/Keluar `<<extend>>` Update Stok | Stok diperbarui saat **simpan**, bukan saat hapus |
| Lupa Password `<<include>>` Kirim Email | Token reset dikirim ke email terdaftar |

---

## 2. Class Diagram

```mermaid
classDiagram
    class Admin {
        +int id_admin
        +string username
        +string password
        +string email
        +enum level
        +login()
        +logout()
    }
    class Sparepart {
        +int id_sparepart
        +string kode_sparepart
        +string kode_manual
        +string nama_sparepart
        +string kategori
        +decimal harga_beli
        +decimal harga_jual
        +int stok
        +enum status_stok
    }
    class Aksesoris {
        +int id_aksesoris
        +string kode_aksesoris
        +string kode_manual
        +string nama_aksesoris
        +string kategori
        +decimal harga_beli
        +decimal harga_jual
        +int stok
        +enum status_stok
    }
    class Supplier {
        +int id_supplier
        +string nama_supplier
        +string telepon
    }
    class BarangMasuk {
        +int id_masuk
        +string no_faktur
        +date tanggal_masuk
        +int id_supplier
        +int id_admin
    }
    class DetailMasuk {
        +int id_detail_masuk
        +enum tipe_barang
        +int id_barang
        +int quantity
    }
    class BarangKeluar {
        +int id_keluar
        +string no_transaksi
        +date tanggal_keluar
        +string tujuan
        +int id_admin
    }
    class DetailKeluar {
        +int id_detail_keluar
        +enum tipe_barang
        +int id_barang
        +int quantity
    }
    class LaporanService {
        +generatePDF(jenis, periode)
    }
    Admin "1" --> "*" BarangMasuk
    Admin "1" --> "*" BarangKeluar
    Supplier "1" --> "*" BarangMasuk
    BarangMasuk "1" --> "*" DetailMasuk
    BarangKeluar "1" --> "*" DetailKeluar
    Admin ..> LaporanService : generate
```

| Catatan | Keterangan |
|---------|------------|
| LaporanService | On-the-fly PDF, bukan entitas DB |
| DetailMasuk/Keluar | Polymorphic via `tipe_barang` + `id_barang` |

---

## 3. Sequence Diagram

Status: **draft** — dilengkapi saat implementasi.

### Login

```mermaid
sequenceDiagram
    actor User
    participant View as Login View
    participant Ctrl as AuthController
    participant Model as AdminModel
    participant DB as MySQL

    User->>View: Input username & password
    View->>Ctrl: POST /login
    Ctrl->>Model: validate(credentials)
    Model->>DB: SELECT admin
    DB-->>Model: record + level
    Model-->>Ctrl: valid / invalid
    alt valid
        Ctrl-->>View: set session, redirect /dashboard
    else invalid
        Ctrl-->>View: flash error
    end
```

### Barang Masuk

```mermaid
sequenceDiagram
    actor User
    participant View as BarangMasuk View
    participant Ctrl as BarangMasukController
    participant Model as TransaksiModel
    participant DB as MySQL

    User->>View: Submit faktur + items
    View->>Ctrl: POST /barang-masuk
    Ctrl->>Model: generateNoFaktur()
    Ctrl->>Model: saveHeader + saveDetails
    Model->>DB: INSERT barang_masuk
    Model->>DB: INSERT detail_masuk
    Model->>DB: UPDATE stok + status_stok
    DB-->>Model: OK
    Model-->>Ctrl: success
    Ctrl-->>View: redirect + flash
```

### Barang Keluar

```mermaid
sequenceDiagram
    actor Admin
    participant View as BarangKeluar View
    participant Ctrl as BarangKeluarController
    participant Model as TransaksiModel
    participant DB as MySQL

    Admin->>View: Submit / edit transaksi
    View->>Ctrl: POST /barang-keluar
    Ctrl->>Model: validateStok(items)
    alt stok cukup
        Model->>DB: INSERT/UPDATE barang_keluar
        Model->>DB: recalculate stok
        DB-->>Model: OK
        Model-->>Ctrl: success
    else stok tidak cukup
        Model-->>Ctrl: error
    end
    Ctrl-->>View: redirect / flash error
```

### Generate Laporan PDF

```mermaid
sequenceDiagram
    actor User
    participant View as Laporan View
    participant Ctrl as LaporanController
    participant Model as QueryModel
    participant PDF as Dompdf

    User->>View: Pilih jenis + periode
    View->>Ctrl: GET /laporan/generate
    Ctrl->>Model: query on-the-fly
    Model-->>Ctrl: dataset
    Ctrl->>PDF: render template
    PDF-->>User: stream PDF download
```
