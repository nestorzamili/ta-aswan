# Dokumentasi — Sistem Inventory Toko Android Service

Requirement pengembangan web berdasarkan **PROPOSAL ASWANDI BAB 1-3.docx**.

| Item      | Nilai                                                                                                                             |
| --------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Judul     | Rancang Bangun Sistem Informasi Inventory Sparepart dan Aksesoris Berbasis Web Pada Toko Android Service Dengan Model Prototyping |
| Peneliti  | Aswandi Zamili (NIM 0425720122)                                                                                                   |
| Institusi | Program Studi Sistem Informasi, Universitas Nias Raya                                                                             |
| Tahun     | 2026                                                                                                                              |

## Stack Ringkas

PHP 8.3+ (host) · CodeIgniter 4.7.3 · MySQL 8.4 Docker (`inventory_android`) · `php spark serve` · Dompdf · Bootstrap 5.3 + Chart.js 4 · Resend (menyusul)

Detail lengkap: [06-implementasi-pengujian.md](06-implementasi-pengujian.md)

## Lingkungan Development

| Komponen       | Lokasi                             | Perintah                      |
| -------------- | ---------------------------------- | ----------------------------- |
| MySQL 8.4      | Container `mysql` (Docker Compose) | `docker compose up -d`        |
| PHP + Composer | Host (CachyOS)                     | `php spark serve --port 8080` |
| Email          | Belum (Resend nanti)               | —                             |

| Setting         | Nilai dev                                                  |
| --------------- | ---------------------------------------------------------- |
| Host / port     | `127.0.0.1:3306` (bind localhost saja, sama pola postgres) |
| Database        | `inventory_android`                                        |
| User / password | `aswan` / `Samunu123`                                      |
| Root password   | `Samunu123`                                                |

Salin `.env.example` → `.env` untuk Docker Compose.

## Daftar Dokumen

Struktur disusun selaras bab penelitian — **6 dokumen inti**, tanpa duplikasi konten.

| No  | Dokumen                                                   | Isi                                               | Padanan Skripsi       |
| --- | --------------------------------------------------------- | ------------------------------------------------- | --------------------- |
| 01  | [konteks-penelitian.md](01-konteks-penelitian.md)         | Latar belakang, tujuan, scope, metode prototyping | Bab I–II              |
| 02  | [kebutuhan-sistem.md](02-kebutuhan-sistem.md)             | Aktor, aturan bisnis, 10 modul fitur, dashboard   | Bab III (kebutuhan)   |
| 03  | [alur-antarmuka.md](03-alur-antarmuka.md)                 | Navigasi, layout, activity diagram                | Bab III (desain UI)   |
| 04  | [diagram-uml.md](04-diagram-uml.md)                       | Use case, class, sequence diagram                 | Bab III (UML)         |
| 05  | [desain-database.md](05-desain-database.md)               | ERD, skema tabel, seed data                       | Bab III (desain data) |
| 06  | [implementasi-pengujian.md](06-implementasi-pengujian.md) | Stack, NFR, black box testing, roadmap            | Bab III–IV            |

## Konfigurasi Menyusul

| Item           | Lokasi                                                                |
| -------------- | --------------------------------------------------------------------- |
| Domain Resend  | [06-implementasi-pengujian.md](06-implementasi-pengujian.md) → `.env` |
| API key Resend | [06-implementasi-pengujian.md](06-implementasi-pengujian.md) → `.env` |

## Referensi

`PROPOSAL ASWANDI BAB 1-3.docx` — Bab I, II, III.
