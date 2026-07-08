# MySQL Migration Playbook

## Tujuan

Dokumen ini adalah langkah pindah dari SQLite ke MySQL setelah struktur database internal sudah siap.

## Prasyarat

- Semua migrasi Laravel sudah hijau di SQLite
- Audit readiness sudah dijalankan dengan:
  - `php artisan marketing:db-readiness`
- Nilai blocker relasi sebaiknya `0`
- Backup file SQLite sudah ada:
  - `database/database.sqlite`

## Langkah Implementasi

### 1. Siapkan database MySQL

Buat database kosong, misalnya:

- nama database: `marketing_dashboard`
- charset: `utf8mb4`
- collation: `utf8mb4_unicode_ci`

### 2. Ubah `.env`

Ganti:

```env
DB_CONNECTION=sqlite
```

Menjadi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketing_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Jalankan migrasi struktur

```bash
php artisan migrate
```

### 4. Migrasikan data

Urutan aman:

1. `users`
2. `master_plans`
3. `distributions`
4. `analytics`
5. `lpjk`
6. `lpjk_detail`
7. `activity_logs`
8. tabel domain lain
9. `marketing_settings`
10. tabel staging seperti `marketing_excel_rows`

## Aturan Migrasi Data

- Pertahankan nilai `id` yang sudah ada
- Pertahankan `source_id`
- Pastikan foreign key internal tidak berubah
- Jangan migrasikan data kotor yang masih orphan

## Validasi Setelah Pindah

Jalankan:

```bash
php artisan marketing:db-readiness
php artisan test tests/Feature/CrudApiTest.php
php artisan test tests/Feature/MasterPlanConversionTest.php
php artisan test tests/Feature/DashboardAuthenticationTest.php
php artisan test tests/Feature/DashboardUserManagementTest.php
php artisan test tests/Feature/GasProxySecurityTest.php
```

## Catatan

- Selama belum ada MySQL, SQLite tetap menjadi sumber data utama.
- Migrasi ke MySQL sebaiknya dilakukan setelah backup SQLite dibuat.
- Setelah MySQL stabil, baru evaluasi penghapusan kolom legacy string seperti `master_id`.
