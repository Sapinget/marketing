# Marketing Dashboard Blade Modularization Plan

## Ringkasan

Dokumen ini menjadi acuan modularisasi `public/marketing-dashboard.html` ke struktur Laravel Blade yang lebih kecil, lebih aman, dan lebih mudah diuji.

Prinsip utamanya sekarang tegas:

- dashboard berjalan sebagai aplikasi Laravel tunggal
- Blade adalah surface render utama
- Vue tetap dipakai untuk interaksi client-side
- semua API, autentikasi, dan print flow berada di origin Laravel yang sama
- tidak ada roadmap bridge eksternal
- tidak ada roadmap proxy publik

Refactor ini bukan rewrite total. Tujuannya adalah memindahkan fondasi aplikasi ke jalur Laravel yang sehat tanpa memutus perilaku dashboard yang sudah dipakai tim.

## Kondisi Saat Ini

Status implementasi per 8 Juli 2026:

- route `/` sudah dirender dari Blade wrapper
- shell dokumen utama hidup di `resources/views/dashboard/index.blade.php`
- head dan body assembly dipecah ke partial Blade
- mayoritas menu utama sudah diekstrak ke partial Blade per domain
- export PDF sudah dipisah ke bridge/module per domain
- print flow tetap lewat endpoint Laravel:
  - `POST /print-job`
  - `GET /print-job/{token}`
- response dashboard sudah memakai hardening header dasar:
  - `Content-Security-Policy`
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
- stylesheet aplikasi utama sekarang dimuat lewat `@vite('resources/css/app.css')` pada shell dashboard
- dependency `cdn.tailwindcss.com` sudah dihapus dari production shell
- audit live `http://127.0.0.1:8090/#dashboard` menunjukkan akar masalah CSS sebelumnya adalah entry CSS Vite belum ikut dimuat di head response
- library head dashboard utama sekarang dimuat dari asset lokal origin sendiri:
  - `vendor/dashboard/vue/vue.global.prod.js`
  - `vendor/dashboard/papaparse/papaparse.min.js`
  - `vendor/dashboard/apexcharts/apexcharts.min.js`
  - `vendor/dashboard/fontawesome/css/all.min.css`
- helper print shared sudah berhenti memuat Font Awesome dari CDN dan memakai asset lokal origin dashboard
- blok CSS inline besar di head sudah dipindah ke `resources/css/dashboard-shell.css` dan dibundel lewat Vite

Masalah yang masih tersisa:

- shell HTML masih besar
- state Vue masih global dan padat
- masih ada sebagian asset gambar yang menunjuk domain publik, belum seluruhnya dipusatkan ke helper asset lokal
- sebagian logic print masih dijembatani oleh inline compatibility layer untuk menjaga backward compatibility

## Tujuan

Target modularisasi:

- memecah UI per menu menjadi partial Blade yang fokus
- mengurangi coupling antara markup dashboard dan helper print/export
- memindahkan ketergantungan penting ke surface Laravel dan Vite
- menjaga regresi tetap rendah dengan feature test yang ketat
- membuat dashboard siap dikembangkan sebagai aplikasi Laravel jangka panjang

Target non-prioritas:

- tidak mengubah dashboard menjadi multi-page penuh dalam satu langkah
- tidak mengganti Vue global state sekaligus
- tidak memindahkan seluruh renderer PDF ke server-side HTML builder baru

## Prinsip Arsitektur

### 1. Laravel menjadi source of truth aplikasi

Yang dimaksud “fundamental Laravel” untuk dashboard ini:

- request masuk ke route Laravel
- response dibentuk oleh Blade/Laravel response object
- security headers dipasang di middleware Laravel
- autentikasi diputuskan oleh middleware Laravel
- API CRUD dibaca dan ditulis ke database Laravel
- print flow memakai endpoint Laravel yang sama, bukan bridge eksternal

### 2. Hybrid refactor, bukan rewrite

Pendekatan aman:

- Blade merakit dokumen dan partial menu
- Vue tetap mengelola interaksi yang sudah ada
- route `/` tetap menjadi single entrypoint

Ini jauh lebih aman dibanding memecah semua menu menjadi page baru sekaligus.

### 3. Pisahkan tiga lapisan

Refactor harus menjaga pemisahan ini:

1. shell/layout
2. menu markup per domain
3. print/export subsystem

Struktur target minimum:

```text
resources/views/dashboard/
  index.blade.php
  partials/
    shell/
    menus/
      dashboard.blade.php
      master-plan.blade.php
      ideation.blade.php
      distribution.blade.php
      analytics.blade.php
      ...

resources/js/dashboard/
  export/
    print-core.js
    print-browser.js
    analytics-export-bridge.js
    customer-service-bridge.js
    reporting-export-bridge.js
    sales-export-bridge.js
```

### 4. Print/export diperlakukan sebagai subsystem Laravel

Print/export bukan sekadar tombol PDF.

Ia mencakup:

- builder HTML print
- asset wait logic
- popup handling
- tokenized print document
- browser print bootstrap

Subsystem ini harus tetap stabil selama modularisasi menu berjalan.

## Risiko Utama

### 1. State Vue global masih lebar

Banyak formatter, helper, filter, dan handler masih berada dalam scope yang sama.

Risikonya:

- dependency kecil mudah terlewat saat modularisasi
- perubahan satu menu bisa memukul menu lain
- review menjadi mahal

### 2. Shell masih berat

Dokumen root masih besar dan masih mengandung banyak CSS/script inline.

Dampaknya:

- first render lebih mahal
- parse/execute cost tinggi
- debugging perubahan head/body lebih sulit

### 3. Dependency CDN masih ada di head

Saat ini shell masih memuat beberapa library langsung dari CDN.

Dampaknya:

- surface supply-chain lebih lebar
- CSP harus lebih permisif dari ideal
- deploy Laravel belum sepenuhnya self-contained

### 4. Print flow sangat sensitif terhadap urutan eksekusi

Beberapa fungsi print masih perlu tersedia tepat waktu bagi handler inline yang lama.

Dampaknya:

- perubahan urutan include bisa memutus export
- perubahan kecil di shell assembly bisa memicu bug yang terlihat jauh dari akar masalah

## Guardrail Teknis

Hal-hal ini tidak boleh berubah tanpa alasan yang jelas:

- endpoint `POST /print-job`
- endpoint `GET /print-job/{token}`
- payload print tetap `{ html }`
- popup print tetap membuka dokumen HTTP nyata dari Laravel
- route root tetap mengirim response no-store
- middleware keamanan tetap aktif pada response HTML

Hal-hal yang wajib dijaga:

- viewport tetap aksesibel
- link `target="_blank"` wajib `rel="noopener noreferrer"`
- query string khusus bypass proxy eksternal tidak boleh kembali
- komentar dan roadmap teknis harus menyebut arsitektur Laravel, bukan proxy eksternal

## Status Implementasi

### Sudah selesai

- shell root pindah ke Blade
- parser shell tidak lagi mengandalkan parse head/body regex yang rapuh
- CSS aplikasi utama sudah dirender dari Vite pada response dashboard, sehingga utility Tailwind tidak lagi bergantung pada CDN runtime
- vendor script/style inti dashboard sudah disajikan dari origin Laravel sendiri, bukan CDN
- head shell turun drastis karena CSS inline besar dipindahkan ke stylesheet terpisah
- bridge export per domain sudah dipisah:
  - analytics
  - customer service
  - reporting
  - sales
- print helper sudah dipisah ke modul browser/core
- menu yang sudah diekstrak ke Blade partial mencakup domain utama dashboard
- hardening Laravel dasar sudah ditambahkan di middleware response
- print popup URL sudah dibersihkan dari parameter bypass proxy

### Masih tersisa

- migrasi sisa dependency CDN penting ke asset lokal Vite
- pemecahan state/action handler per menu
- pengurangan inline compatibility layer yang masih diperlukan
- audit ukuran shell dan pemecahan blok script besar

## Rencana Lanjutan

### Fase 1: Rapikan fondasi Laravel

Prioritas:

- pertahankan semua response dashboard di jalur middleware Laravel
- pertahankan security header dan CSP yang konsisten
- minimalkan dependency environment khusus di route root
- lanjutkan pengurangan komentar/artefak yang mengacu ke arsitektur lama

Checklist:

- root memakai `url('/')` atau helper Laravel sejenis untuk origin sendiri
- route komentar dan docs memakai istilah Laravel-native
- tidak ada parameter bypass eksternal di print flow
- shell dashboard wajib memuat asset CSS utama dari Vite pada response HTML, bukan mengandalkan injeksi runtime eksternal
- vendor library legacy yang tetap dibutuhkan sinkron ke `public/vendor/dashboard` melalui script build, bukan diambil saat request runtime

### Fase 2: Kurangi dependency CDN

Target:

- pindahkan dependency head yang masih CDN ke asset yang dibangun lewat Vite sejauh aman
- kurangi kebutuhan CSP yang terlalu permisif

Status terbaru:

- fase ini secara praktis sudah selesai untuk dependency head utama dashboard
- CSP sudah tidak lagi mengizinkan `cdn.jsdelivr.net`, `fonts.googleapis.com`, atau `fonts.gstatic.com`
- sisa pekerjaan fase ini tinggal asset gambar publik yang belum dipindah ke helper lokal

Prioritas:

1. library yang bisa dibundel tanpa memukul runtime
2. stylesheet yang belum berada di bundle Vite
3. evaluasi library CDN mana yang masih layak dipertahankan sementara

Catatan:

- ini harus dikerjakan hati-hati karena dashboard legacy memakai banyak utility class
- setiap pemindahan dependency wajib disertai build verification dan render test
- sinkronisasi asset vendor lokal sekarang ditangani oleh `scripts/sync-dashboard-vendor-assets.mjs`

### Fase 3: Pecah state dan action handler per domain

Target:

- state umum tetap di root app
- state spesifik domain pindah ke module kecil
- formatter/util tetap dibagi secara eksplisit

Contoh target struktur:

```text
resources/js/dashboard/
  menu/
    analytics.js
    distribution.js
    master-plan.js
    customer-service.js
  shared/
    formatters.js
    notifications.js
    url.js
```

Manfaat:

- mengurangi coupling antar menu
- mempermudah pengujian unit
- mempermudah migrasi dari inline closure besar

### Fase 4: Sederhanakan compatibility layer print

Target:

- bridge tetap ada, tapi logic inline lama makin tipis
- source of truth print tinggal modul JS

Guardrail:

- jangan ubah kontrak endpoint
- jangan ubah payload print
- jangan ubah perilaku popup print tanpa test regresi

### Fase 5: Evaluasi multi-page hanya jika benar-benar perlu

Route per menu baru masuk akal jika:

- state domain sudah terisolasi
- auth dan permission stabil
- export tidak lagi tergantung app root global
- ada alasan performa atau hak akses yang nyata

Sebelum itu, satu route dashboard tetap pilihan paling aman.

## Strategi Testing

Semua perubahan modularisasi harus diuji minimal lewat:

1. feature test shell builder
2. feature test root render
3. build verification `npm run build`
4. regresi penuh `php artisan test`

Area test wajib:

- marker split menu tidak salah boundary
- bridge export tetap aktif
- helper print tetap tersedia
- response header keamanan tetap benar
- viewport dan hardening link tidak regresi

## Aturan Editing

Saat melanjutkan modularisasi:

- jangan gabungkan refactor markup dengan redesign visual besar
- jangan gabungkan refactor menu dengan perubahan besar pada print subsystem
- jangan menambah dependency eksternal baru tanpa alasan kuat
- setiap partial Blade baru harus fokus ke satu menu/domain
- setiap perubahan route/head/middleware harus diverifikasi lewat feature test

## Keputusan Arsitektur

Keputusan yang sekarang dianggap final:

- dashboard adalah aplikasi Laravel
- Blade adalah assembly layer resmi
- PDF print dibuka dari dokumen Laravel yang disajikan lewat `/print-job/{token}`
- origin backend diturunkan dari aplikasi Laravel sendiri
- roadmap eksternal proxy dan hosted bridge tidak lagi dipakai sebagai arah pengembangan

## Definisi Selesai

Modularisasi dianggap sehat jika:

- seluruh menu utama hidup di partial Blade yang jelas
- root route hanya merakit shell, bukan menanggung file HTML monolitik mentah
- print/export bridge per domain stabil
- dependency CDN kritikal berkurang
- state domain mulai terpisah dari closure global
- test suite tetap hijau
- dashboard tetap bekerja penuh sebagai aplikasi Laravel tunggal
