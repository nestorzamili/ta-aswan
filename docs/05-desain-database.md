# Bab III — Desain Database

[← Kembali ke README](README.md) · [Aturan bisnis](02-kebutuhan-sistem.md#2-aturan-bisnis)

Database: **`db_inventory`** · MySQL 8.x · Migration CI4 (`php spark migrate`).

---

## 1. Entity Relationship Diagram

### Entitas

Kelas logis proposal: **Admin**, **Sparepart**, **Aksesoris**, **Barang Masuk**, **Barang Keluar**, **Laporan** (service/controller, bukan tabel).

**Implementasi fisik:** Sparepart & Aksesoris digabung ke tabel **`barang`** (`tipe_barang` ENUM). UI tetap menu terpisah.

Entitas tambahan: `supplier`, `detail_masuk`, `detail_keluar`, `password_reset_tokens`, `activity_logs` (audit trail).

Tabel `admin`, `barang`, dan `supplier` menggunakan **soft delete** (`deleted_at`).

```mermaid
erDiagram
    admin ||--o{ barang_masuk : "mencatat"
    admin ||--o{ barang_keluar : "mencatat"
    admin ||--o{ password_reset_tokens : "reset"
    admin ||--o{ activity_logs : "melakukan"
    supplier ||--o{ barang_masuk : "memasok"
    barang_masuk ||--|{ detail_masuk : "berisi"
    barang_keluar ||--|{ detail_keluar : "berisi"
    barang ||--o{ detail_masuk : "item"
    barang ||--o{ detail_keluar : "item"

    admin {
        int id_admin PK
        varchar username
        varchar password
        varchar nama
        varchar email UK
        varchar nomor_telepon
        varchar foto
        enum level
        enum status
        datetime deleted_at
    }
    activity_logs {
        int id PK
        int id_admin FK
        varchar nama_admin
        varchar action
        varchar entity
        int entity_id
        varchar description
        varchar ip_address
        datetime created_at
    }
    password_reset_tokens {
        int id PK
        int id_admin FK
        varchar token UK
        datetime expires_at
        datetime used_at
    }
    barang {
        int id_barang PK
        enum tipe_barang
        varchar kode_barang UK
        varchar kode_manual
        varchar nama_barang
        varchar kategori
        varchar merk
        varchar satuan
        decimal harga_beli
        decimal harga_jual
        int stok
        enum status_stok
    }
    supplier {
        int id_supplier PK
        varchar nama_supplier
        varchar alamat
        varchar telepon
        varchar email
    }
    barang_masuk {
        int id_masuk PK
        varchar no_faktur UK
        date tanggal_masuk
        int id_supplier FK
        int total_item
        int total_quantity
        decimal total_harga
        int id_admin FK
    }
    detail_masuk {
        int id_detail_masuk PK
        int id_masuk FK
        int id_barang FK
        int quantity
        decimal harga_satuan
        decimal subtotal
    }
    barang_keluar {
        int id_keluar PK
        varchar no_transaksi UK
        date tanggal_keluar
        varchar tujuan
        int total_item
        int total_quantity
        decimal total_harga
        int id_admin FK
    }
    detail_keluar {
        int id_detail_keluar PK
        int id_keluar FK
        int id_barang FK
        int quantity
        decimal harga_satuan
        decimal subtotal
    }
```

### Relasi Kunci

| Relasi                       | Kardinalitas | Keterangan                                                       |
| ---------------------------- | ------------ | ---------------------------------------------------------------- |
| admin → barang_masuk/keluar  | 1:N          | Satu admin mencatat banyak transaksi                             |
| supplier → barang_masuk      | 1:N          | Satu supplier memasok banyak transaksi                           |
| barang_masuk/keluar → detail | 1:N          | Satu transaksi berisi banyak item                                |
| barang → detail              | 1:N          | FK `id_barang` (sparepart/aksesoris via `tipe_barang` di master) |

### Aturan Stok Otomatis

| Event                  | Aksi                                             |
| ---------------------- | ------------------------------------------------ |
| Barang masuk disimpan  | `stok` bertambah sesuai `quantity`               |
| Barang keluar disimpan | `stok` berkurang; validasi tidak negatif         |
| Stok berubah           | `status_stok`: habis (0), rendah (<3), aman (≥3) |
| Transaksi dihapus      | Stok **dikembalikan** (rollback efek simpan)     |

---

## 2. Skema Tabel

### `admin`

| Kolom         | Tipe                     | Constraint                   | Keterangan     |
| ------------- | ------------------------ | ---------------------------- | -------------- |
| id_admin      | INT                      | PK, AUTO_INCREMENT           | —              |
| username      | VARCHAR(50)              | UNIQUE, NOT NULL             | Login          |
| password      | VARCHAR(255)             | NOT NULL                     | Hash (bcrypt)  |
| nama          | VARCHAR(100)             | NOT NULL                     | —              |
| email         | VARCHAR(100)             | UNIQUE, NOT NULL             | Reset password |
| nomor_telepon | VARCHAR(20)              | NULL                         | —              |
| foto          | VARCHAR(255)             | NULL                         | Nama file foto profil |
| level         | ENUM('admin','karyawan') | NOT NULL, DEFAULT 'karyawan' | —              |
| status        | ENUM('aktif','nonaktif') | DEFAULT 'aktif'              | —              |
| created_at    | DATETIME                 | NULL                         | —              |
| updated_at    | DATETIME                 | NULL                         | —              |
| deleted_at    | DATETIME                 | NULL                         | Soft delete    |

### `barang` (sparepart + aksesoris)

Tabel unifikasi. `tipe_barang`: `sparepart` | `aksesoris`. Kode: `SP-YYYY-NNNN` / `AK-YYYY-NNNN` di kolom `kode_barang` (UNIQUE). Kolom: `kode_manual`, `nama_barang`, `kategori`, `merk`, `satuan`, `harga_beli`, `harga_jual`, `stok`, `status_stok`, timestamps, soft-delete.

### `supplier`

| Kolom                  | Tipe         | Constraint         |
| ---------------------- | ------------ | ------------------ |
| id_supplier            | INT          | PK, AUTO_INCREMENT |
| nama_supplier          | VARCHAR(100) | NOT NULL           |
| alamat                 | TEXT         | NULL               |
| telepon                | VARCHAR(20)  | NULL               |
| email                  | VARCHAR(100) | NULL               |
| created_at, updated_at | DATETIME     | NULL               |
| deleted_at             | DATETIME     | NULL (soft delete) |

### `barang_masuk`

| Kolom                      | Tipe          | Constraint                | Keterangan   |
| -------------------------- | ------------- | ------------------------- | ------------ |
| id_masuk                   | INT           | PK                        | —            |
| no_faktur                  | VARCHAR(30)   | UNIQUE, NOT NULL          | FM-YYYY-NNNN |
| tanggal_masuk              | DATE          | NOT NULL                  | —            |
| id_supplier                | INT           | FK → supplier             | —            |
| total_item, total_quantity | INT           | DEFAULT 0                 | —            |
| total_harga                | DECIMAL(14,2) | DEFAULT 0                 | —            |
| id_admin                   | INT           | FK → admin                | Pencatat     |
| created_at                 | DATETIME      | DEFAULT CURRENT_TIMESTAMP | —            |

### `detail_masuk` / `detail_keluar`

| Kolom                | Tipe          | Constraint                        |
| -------------------- | ------------- | --------------------------------- |
| id_detail_*          | INT           | PK                                |
| id_masuk / id_keluar | INT           | FK, ON DELETE CASCADE             |
| id_barang            | INT           | FK → `barang.id_barang`, RESTRICT |
| quantity             | INT           | NOT NULL, CHECK > 0               |
| harga_satuan         | DECIMAL(12,2) | NOT NULL                          |
| subtotal             | DECIMAL(14,2) | NOT NULL                          |

### `barang_keluar`

| Kolom                      | Tipe          | Constraint                | Keterangan   |
| -------------------------- | ------------- | ------------------------- | ------------ |
| id_keluar                  | INT           | PK                        | —            |
| no_transaksi               | VARCHAR(30)   | UNIQUE, NOT NULL          | TK-YYYY-NNNN |
| tanggal_keluar             | DATE          | NOT NULL                  | —            |
| tujuan                     | VARCHAR(100)  | NOT NULL                  | —            |
| total_item, total_quantity | INT           | DEFAULT 0                 | —            |
| total_harga                | DECIMAL(14,2) | DEFAULT 0                 | —            |
| id_admin                   | INT           | FK → admin                | —            |
| created_at                 | DATETIME      | DEFAULT CURRENT_TIMESTAMP | —            |

### `password_reset_tokens`

| Kolom      | Tipe        | Constraint                |
| ---------- | ----------- | ------------------------- |
| id         | INT         | PK                        |
| id_admin   | INT         | FK, ON DELETE CASCADE     |
| token      | VARCHAR(64) | UNIQUE, NOT NULL          |
| expires_at | DATETIME    | NOT NULL (+60 menit)      |
| used_at    | DATETIME    | NULL                      |
| created_at | DATETIME    | DEFAULT CURRENT_TIMESTAMP |

### `activity_logs`

Audit trail aktivitas pengguna (create/update/delete entitas utama + login/logout).

| Kolom       | Tipe         | Constraint         | Keterangan                                  |
| ----------- | ------------ | ------------------ | ------------------------------------------- |
| id          | INT          | PK, AUTO_INCREMENT | —                                           |
| id_admin    | INT          | NULL               | Pelaku (null bila sesi tak dikenal)         |
| nama_admin  | VARCHAR(100) | NULL               | Snapshot nama pelaku                        |
| action      | VARCHAR(20)  | NOT NULL           | `create`/`update`/`delete`/`login`/`logout` |
| entity      | VARCHAR(50)  | NULL               | Entitas terkait (mis. `barang`, `supplier`) |
| entity_id   | INT          | NULL               | ID entitas terkait                          |
| description | VARCHAR(255) | NULL               | Deskripsi ringkas                           |
| ip_address  | VARCHAR(45)  | NULL               | IP pelaku                                   |
| created_at  | DATETIME     | NULL               | Waktu aktivitas                             |

### Index Rekomendasi

```sql
CREATE INDEX idx_barang_tipe ON barang(tipe_barang);
CREATE INDEX idx_barang_tipe_status ON barang(tipe_barang, status_stok);
CREATE INDEX idx_barang_kategori ON barang(kategori);
CREATE INDEX idx_barang_masuk_tanggal ON barang_masuk(tanggal_masuk);
CREATE INDEX idx_barang_keluar_tanggal ON barang_keluar(tanggal_keluar);
CREATE INDEX idx_detail_masuk_barang ON detail_masuk(id_barang);
CREATE INDEX idx_detail_keluar_barang ON detail_keluar(id_barang);
CREATE INDEX idx_activity_logs_created ON activity_logs(created_at);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);
CREATE INDEX idx_activity_logs_entity ON activity_logs(entity);
```

---

## 3. Seed Data

> Password seed hanya untuk development/demo. Wajib diganti setelah deploy.

**`AdminSeeder`** membuat satu akun admin awal. Password diambil dari `admin.defaultPassword` di `.env` (default `secret` bila tidak diset).

| Nama  | Level   | Username | Email             | Password         |
| ----- | ------- | -------- | ----------------- | ---------------- |
| Admin | `admin` | `admin`  | `<email_admin>`   | `<password>` (dari `.env`) |

### Urutan Seed

```bash
php spark db:seed AdminSeeder
# Seeder master/transaksi tambahan (opsional) menyusul
```
