# Database Refactor Plan

## Tujuan

Sebelum migrasi dari SQLite ke MySQL, struktur database perlu dirapikan dulu supaya:

- relasi antar data jelas
- constraint bisa ditegakkan di level database
- query lebih stabil dan mudah dipelihara
- proses migrasi ke MySQL tidak membawa legacy schema yang lemah
- setiap record operasional punya identitas internal yang konsisten
- setiap aksi CRUD bisa ditelusuri ke user yang melakukannya

## Aturan Wajib

### 1. Semua data wajib punya `id`

Aturan ini berlaku untuk semua tabel domain dan master data.

Standar:

- `id` = primary key internal
- tipe target untuk MySQL: `bigint unsigned` auto increment
- `source_id` hanya pelengkap untuk import, bukan pengganti `id`

Implikasi:

- semua insert/update/delete di aplikasi harus berbasis `id` internal
- semua relasi antar tabel harus mengacu ke `id`
- semua tabel baru yang dibuat setelah ini wajib mengikuti pola yang sama

### 2. User yang melakukan CRUD wajib tercatat di database

Aturan ini berlaku minimal untuk semua tabel operasional yang bisa diubah dari dashboard.

Standar minimum:

- `created_by_user_id`
- `updated_by_user_id`
- opsional tahap lanjut: `deleted_by_user_id`

Jika soft delete nanti dipakai, tambahkan:

- `deleted_at`
- `deleted_by_user_id`

Jika butuh jejak yang lebih lengkap, tambahkan audit log terpisah.

## Kondisi Saat Ini

Database aktif: `database/database.sqlite`

### Kelompok tabel

Framework / Laravel:

- `users`
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `migrations`

Domain marketing / operasional:

- `master_plans`
- `distributions`
- `analytics`
- `marketing_settings`
- `marketing_excel_rows`
- `unboxing`
- `story_schedules`
- `calendar_events`
- `ideation`
- `program_promo`
- `sell_out_targets`
- `ads_performance`
- `harga_kompetitor`
- `orderan_online`
- `unit_ditanya`
- `claim_garansi`
- `keep_barang`
- `lpjk`
- `lpjk_detail`
- `meta_ig_posts`
- `stock_names`

## Masalah Struktur Saat Ini

### 1. Relasi belum ditegakkan dengan foreign key

Contoh:

- `distributions.master_id` masih `varchar`
- `analytics.master_id` masih `varchar`
- `lpjk_detail.master_id` masih `varchar`
- `sessions.user_id` tidak dideklarasikan sebagai foreign key ke `users.id`

Dampak:

- rawan orphan data
- join lebih berat
- validasi relasi pindah ke level aplikasi

### 2. Banyak tabel memakai `source_id` sebagai identitas bisnis sekaligus lookup utama

Hampir semua tabel domain memakai pola:

- `id` auto increment
- `source_id` unique

Ini masih bisa dipakai, tapi perlu keputusan jelas:

- `id` sebagai primary relational key internal
- `source_id` sebagai external/import key

Saat ini pemakaiannya belum konsisten.

### 3. Banyak kolom `raw_payload` dan data hasil normalisasi hidup berdampingan

Contoh umum:

- data inti disimpan terpecah ke kolom
- payload asli tetap disimpan di `raw_payload`

Ini bagus untuk audit/import, tapi perlu batas:

- tabel operasional jangan bergantung pada `raw_payload`
- payload mentah lebih baik diposisikan sebagai audit/import log

### 4. `marketing_settings` masih model key-value JSON

Struktur sekarang:

- `key`
- `values` JSON text

Masalah:

- sulit divalidasi
- sulit di-join
- sulit diindex
- schema setting tidak eksplisit

Ini cocok untuk konfigurasi ringan, tapi tidak ideal untuk master data yang dipakai lintas modul.

### 5. Penamaan dan domain boundary masih campur

Contoh:

- `master_plans` menjadi pusat konten, tapi distribusi dan analytics belum benar-benar relational
- `stock_names` dipakai sebagai master referensi produk, tetapi tabel lain masih simpan teks bebas seperti `brand`, `seri`, `type_hp`
- `claim_garansi`, `unit_ditanya`, `keep_barang`, `orderan_online` berada dalam domain customer service, tapi belum punya referensi master produk yang kuat

### 6. Belum ada tabel master user operasional selain `users`

Saat ini login sudah pindah ke `users`, tetapi:

- role masih hardcoded di aplikasi
- belum ada tabel role / permission
- belum ada audit siapa mengubah data di level foreign key ke user

`created_by` dan `updated_by` masih `varchar`, bukan `user_id`.

## Prinsip Refactor

### A. Bedakan key internal dan key import

Untuk semua tabel domain:

- `id` = primary key relasional internal
- `source_id` = nullable/unique business-import key

### B. Pakai foreign key nyata

Target minimal:

- `distributions.master_plan_id -> master_plans.id`
- `analytics.master_plan_id -> master_plans.id`
- `lpjk_detail.lpjk_id -> lpjk.id`
- `sessions.user_id -> users.id`
- tabel domain yang menyimpan actor sebaiknya pindah ke `created_by_user_id` / `updated_by_user_id`

### C. Pisahkan tabel transaksi, master, dan staging

Tiga lapisan yang diinginkan:

1. Master data
2. Transaction / operational data
3. Import staging / audit

### D. JSON hanya untuk audit atau metadata fleksibel

`raw_payload` tetap boleh ada, tapi:

- tidak menjadi sumber utama query aplikasi
- tidak menggantikan kolom relasional yang seharusnya eksplisit

## Target Struktur Tingkat Tinggi

## ERD Target Sederhana

```text
users
  id PK
  username UQ
  email UQ
  ...

master_plans
  id PK
  source_id UQ
  ...
  created_by_user_id FK -> users.id
  updated_by_user_id FK -> users.id

distributions
  id PK
  source_id UQ? (opsional kalau masih dibutuhkan)
  master_plan_id FK -> master_plans.id
  platform
  tanggal_publish
  ...

analytics
  id PK
  source_id UQ? (opsional kalau masih dibutuhkan)
  master_plan_id FK -> master_plans.id
  platform
  tanggal_publish
  views
  likes
  comments
  shares
  ...

lpjk
  id PK
  source_id UQ
  ...
  created_by_user_id FK -> users.id
  updated_by_user_id FK -> users.id

lpjk_detail
  id PK
  source_id UQ
  lpjk_id FK -> lpjk.id
  ...

stock_names
  id PK
  source_id UQ
  kategori
  brand
  seri

orderan_online / unit_ditanya / claim_garansi / keep_barang
  ...
  stock_name_id FK -> stock_names.id   (target bertahap, tidak harus phase 1)
  created_by_user_id FK -> users.id
  updated_by_user_id FK -> users.id

marketing_excel_rows
  id PK
  sheet_name
  row_number
  payload
  ...
```

## 3 Relasi Pertama yang Akan Dibenahi

### 1. `master_plans -> distributions`

Kondisi sekarang:

- `distributions.master_id` menyimpan `master_plans.source_id`
- tidak ada FK

Target:

- tambah `distributions.master_plan_id`
- relasi resmi ke `master_plans.id`
- `master_id` lama dipertahankan sementara hanya untuk transisi

Alasan prioritas:

- ini relasi inti distribusi konten
- dipakai langsung oleh dashboard dan logic publish

### 2. `master_plans -> analytics`

Kondisi sekarang:

- `analytics.master_id` menyimpan `master_plans.source_id`
- tidak ada FK

Target:

- tambah `analytics.master_plan_id`
- relasi resmi ke `master_plans.id`
- `master_id` lama dipertahankan sementara hanya untuk transisi

Alasan prioritas:

- ini relasi inti performa konten
- akan sangat penting saat agregasi dan query makin berat di MySQL

### 3. `lpjk -> lpjk_detail`

Kondisi sekarang:

- `lpjk_detail.master_id` menyimpan referensi string ke parent
- belum ada FK

Target:

- tambah `lpjk_detail.lpjk_id`
- relasi resmi ke `lpjk.id`
- `master_id` lama dipertahankan sementara untuk backfill dan rollback aman

Alasan prioritas:

- child table finansial paling jelas dan paling mudah dinormalisasi lebih dulu
- efek samping ke modul lain relatif kecil

### 1. Auth & akses

Tetap:

- `users`
- `sessions`

Rencana lanjutan:

- tambah `roles`
- tambah `user_roles` atau minimal kolom `role`

### 2. Content planning

Inti:

- `master_plans`
- `master_plan_platforms` jika platform ingin dinormalisasi
- `distributions`
- `analytics`

Perbaikan:

- `platforms` di `master_plans` jangan text comma-separated dalam jangka panjang
- `distribution_meta` jangan jadi sumber utama relasi publish

### 3. Content support

- `story_schedules`
- `calendar_events`
- `unboxing`
- `ideation`

Perlu diputuskan mana yang benar-benar entity terpisah dan mana yang hanya variasi konten dari `master_plans`.

### 4. Product master

Jadikan `stock_names` sebagai basis master produk:

- `stock_names`

Rencana lanjutan:

- pertimbangkan pecah menjadi:
  - `product_categories`
  - `product_brands`
  - `product_series`
  - `products`

Kalau belum ingin terlalu jauh, minimal semua tabel customer service harus refer ke `stock_names.id`.

### 5. Customer service domain

- `orderan_online`
- `unit_ditanya`
- `claim_garansi`
- `keep_barang`

Perbaikan:

- referensi ke master produk
- normalisasi status
- audit user penginput

### 6. Marketing performance

- `program_promo`
- `sell_out_targets`
- `ads_performance`
- `harga_kompetitor`
- `meta_ig_posts`

Perbaikan:

- standardisasi dimensi waktu
- master platform terpisah jika perlu
- pisahkan metrik agregat vs event/import rows jika volume tumbuh

### 7. Event reporting

- `lpjk`
- `lpjk_detail`

Perbaikan:

- `lpjk_detail.master_id` ganti ke FK integer
- kategori pengeluaran bisa dipindah ke master tersendiri

### 8. Import / staging

- `marketing_excel_rows`

Fungsi tabel ini harus dipertegas:

- hanya sebagai staging/audit import
- bukan sumber operasional utama

## Rencana Eksekusi

### Phase 1 - Audit & desain

Output:

- peta relasi final
- daftar tabel yang tetap
- daftar tabel yang dipecah / diganti
- daftar field yang deprecated

Langkah:

- definisikan master data inti
- definisikan foreign key target
- definisikan status enums/domain values yang akan distandardisasi

### Phase 2 - Stabilkan key & relasi

Fokus:

- tambah FK integer baru tanpa langsung hapus kolom lama

Contoh:

- tambah `distributions.master_plan_id`
- backfill dari `master_id -> master_plans.source_id`
- ubah aplikasi baca kolom baru
- baru setelah stabil, hapus kolom lama

Lakukan pola yang sama untuk:

- `analytics`
- `lpjk_detail`
- audit actor columns

### Phase 3 - Rapikan master data

Fokus:

- kurangi teks bebas
- tambah referensi ke master produk dan master status bila perlu

Contoh:

- normalisasi product reference untuk `keep_barang`, `unit_ditanya`, `claim_garansi`
- evaluasi `marketing_settings` apakah tetap JSON atau dipecah menjadi tabel master per domain

### Phase 4 - Pisahkan staging dari operational

Fokus:

- pertahankan `marketing_excel_rows` hanya untuk import log
- pastikan UI dan API tidak tergantung pada payload mentah

### Phase 5 - Migrasi ke MySQL

Baru dilakukan setelah schema logis stabil.

Langkah:

- siapkan migration schema final
- migrasi data SQLite -> MySQL
- verifikasi row counts
- verifikasi relasi dan constraint
- pindahkan `.env` ke `DB_CONNECTION=mysql`

## Urutan Refactor yang Disarankan

Prioritas tinggi:

1. `master_plans` <-> `distributions`
2. `master_plans` <-> `analytics`
3. `lpjk` <-> `lpjk_detail`
4. `users` sebagai actor FK untuk perubahan data
5. evaluasi `marketing_settings`

Prioritas menengah:

6. normalisasi relasi ke `stock_names`
7. standardisasi status dan platform
8. review tabel import/staging

Prioritas lanjutan:

9. role/permission model
10. master platform / master status / master category

## Risiko

### Risiko data

- backfill relasi gagal jika `source_id` tidak konsisten
- data lama bisa punya nilai text yang tidak cocok dengan master baru

### Risiko aplikasi

- API lama mungkin masih membaca kolom string lama
- import script lama bisa rusak jika schema berubah

### Risiko migrasi

- MySQL lebih ketat untuk type, collation, dan constraint
- query yang “kebetulan lolos” di SQLite bisa gagal di MySQL

## Checklist Sebelum Masuk MySQL

- semua relasi utama sudah punya FK integer
- actor columns tidak lagi string bebas
- staging dan operational sudah dipisah secara konsep
- validasi aplikasi tidak bergantung pada kelemahan SQLite
- script import sudah tidak hardcode `sqlite3`
- test CRUD utama lulus pada schema baru

## Deliverable Teknis Berikutnya

Setelah plan ini disetujui, langkah implementasi yang paling tepat:

1. buat ERD target sederhana
2. pilih 3 relasi pertama yang akan dibenahi
3. buat migration incremental tanpa breaking change
4. backfill data
5. ubah route/query ke kolom FK baru
6. baru lanjut ke MySQL

## Breakdown Implementasi untuk Phase 1

### Workstream A - `distributions.master_plan_id`

Migration:

- tambah kolom nullable `master_plan_id`
- tambah index
- belum tambah FK hard constraint jika ingin rollout lebih aman

Backfill:

- isi `master_plan_id` dengan join `distributions.master_id = master_plans.source_id`

App change:

- query write baru mengisi `master_plan_id`
- query read join memakai `master_plan_id`
- `master_id` lama tetap diisi selama masa transisi

Cleanup nanti:

- jadikan `master_plan_id` not null
- tambah foreign key
- evaluasi apakah `master_id` lama masih perlu

### Workstream B - `analytics.master_plan_id`

Migration:

- tambah kolom nullable `master_plan_id`
- tambah index

Backfill:

- isi dari join `analytics.master_id = master_plans.source_id`

App change:

- update import/convert analytics
- update endpoint analytics

Cleanup nanti:

- not null
- foreign key
- deprecate `master_id` string

### Workstream C - `lpjk_detail.lpjk_id`

Migration:

- tambah kolom nullable `lpjk_id`
- tambah index

Backfill:

- isi dari join `lpjk_detail.master_id = lpjk.source_id`

App change:

- write baru isi `lpjk_id`
- detail fetch berbasis `lpjk_id`

Cleanup nanti:

- not null
- foreign key
- deprecate `master_id`

## Catatan Desain Penting

### Tentang `source_id`

Keputusan yang disarankan:

- tetap pertahankan `source_id` pada tabel yang datang dari import/manual spreadsheet
- jangan pakai `source_id` sebagai FK relasional utama

### Tentang actor columns

Refactor `created_by` / `updated_by` ke bentuk ini:

- `created_by_user_id`
- `updated_by_user_id`

Jika string display name masih diperlukan untuk histori, simpan sebagai snapshot terpisah:

- `created_by_name_snapshot`
- `updated_by_name_snapshot`

### Tentang audit CRUD

Target minimal phase awal:

- setiap tabel yang bisa diubah dari dashboard menyimpan `created_by_user_id` dan `updated_by_user_id`

Target lanjutan:

- tambah tabel audit log, misalnya `activity_logs`

Contoh struktur:

```text
activity_logs
  id PK
  user_id FK -> users.id
  action            // create, update, delete
  entity_type       // master_plan, distribution, analytics, dll
  entity_id         // id internal record
  entity_source_id  // opsional untuk jejak import key
  changes_json      // before/after atau diff ringkas
  created_at
```

Dengan pendekatan ini:

- data utama tetap ringkas
- histori CRUD tetap bisa ditelusuri
- siap untuk audit saat sudah pindah ke MySQL

### Tentang `marketing_settings`

Jangan dibongkar dulu di phase 1.

Saran:

- freeze dulu sebagai config store
- setelah 3 relasi prioritas stabil, baru audit key mana yang pantas jadi tabel master sungguhan
