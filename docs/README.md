# Dokumentasi — Sistem Inventory Toko Android Service

Requirement pengembangan web berdasarkan **PROPOSAL ASWANDI BAB 1-3.docx**.

| Item      | Nilai                                                                                                                             |
| --------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Judul     | Rancang Bangun Sistem Informasi Inventory Sparepart dan Aksesoris Berbasis Web Pada Toko Android Service Dengan Model Prototyping |
| Peneliti  | Aswandi Zamili (NIM 0425720122)                                                                                                   |
| Institusi | Program Studi Sistem Informasi, Universitas Nias Raya                                                                             |
| Tahun     | 2026                                                                                                                              |

## Stack Ringkas

PHP 8.2+ (host, Windows) · CodeIgniter 4.7 · MySQL 8.x (`db_inventory`) · `php spark serve` · Dompdf · Bootstrap 5.3 + Chart.js 4 · PHP-CS-Fixer + PHPStan (CI)

Detail lengkap: [06-implementasi-pengujian.md](06-implementasi-pengujian.md)

## Lingkungan Development

Aplikasi dijalankan langsung di host (tanpa Docker). MySQL memakai instance/kontainer yang sudah tersedia dengan port `3306` ter-expose ke `localhost`.

| Komponen       | Lokasi                              | Perintah                      |
| -------------- | ----------------------------------- | ----------------------------- |
| PHP + Composer | Host (Windows — Laragon/XAMPP)      | `php spark serve --port 8080` |
| MySQL 8.x      | Server/kontainer existing di `3306` | —                             |
| Email          | Opsional (reset password)           | —                             |

| Setting         | Nilai dev                                                     |
| --------------- | ------------------------------------------------------------- |
| Host / port     | `127.0.0.1:3306` (gunakan TCP, bukan socket)                  |
| Database        | `db_inventory`                                                |
| User / password | `<user>` / `<password>` (isi di `.env`, jangan commit)        |

Salin `env` → `.env` lalu sesuaikan koneksi database.

## Daftar Dokumen

Struktur disusun selaras bab penelitian — **6 dokumen inti**, tanpa duplikasi konten.

| No  | Dokumen                                                   | Isi                                               | Padanan Skripsi       |
| --- | --------------------------------------------------------- | ------------------------------------------------- | --------------------- |
| 01  | [konteks-penelitian.md](01-konteks-penelitian.md)         | Latar belakang, tujuan, scope, metode prototyping | Bab I–II              |
| 02  | [kebutuhan-sistem.md](02-kebutuhan-sistem.md)             | Aktor, aturan bisnis, modul fitur, dashboard      | Bab III (kebutuhan)   |
| 03  | [alur-antarmuka.md](03-alur-antarmuka.md)                 | Navigasi, layout, activity diagram                | Bab III (desain UI)   |
| 04  | [diagram-uml.md](04-diagram-uml.md)                       | Use case, class, sequence diagram                 | Bab III (UML)         |
| 05  | [desain-database.md](05-desain-database.md)               | ERD, skema tabel, seed data                       | Bab III (desain data) |
| 06  | [implementasi-pengujian.md](06-implementasi-pengujian.md) | Stack, NFR, black box testing, roadmap            | Bab III–IV            |
| 07  | [lampiran-kode.md](07-lampiran-kode.md)                   | Kode program inti per fitur                        | Lampiran              |

## Referensi

`PROPOSAL ASWANDI BAB 1-3.docx` — Bab I, II, III.
