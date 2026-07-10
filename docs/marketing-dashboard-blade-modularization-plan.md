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

Status implementasi per 10 Juli 2026:

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
- logo dan favicon utama dashboard sekarang dilayani dari asset lokal Laravel:
  - `public/asset/images/logo.png`
  - `public/asset/images/favicon.ico`
- helper Excel export browser sekarang dimuat dari asset lokal origin dashboard:
  - `public/vendor/dashboard/xlsx/xlsx.full.min.js`
- dependency npm `xlsx` sudah dikeluarkan dari dependency tree build karena advisory tidak memiliki patch upstream yang tersedia
- inventaris asset vendor lokal dashboard sekarang didokumentasikan eksplisit di:
  - `resources/vendor/dashboard/manifest.json`
  dan script sinkronisasi publik membacanya dari:
  - `scripts/sync-dashboard-vendor-assets.mjs`
- compatibility layer print di Blade sekarang sudah ditipiskan menjadi adapter dependency saja, sementara flow browser print tetap hidup di `resources/js/dashboard/export/print-browser.js`
- snapshot legacy utama sekarang disimpan di `resources/legacy/marketing-dashboard-source.html` hanya sebagai arsip referensi dan guardrail test, bukan lagi sebagai source assembly runtime
- `public/marketing-dashboard.html` sekarang diturunkan menjadi pointer arsip statis ke route Laravel `/`, sehingga tidak lagi ambigu sebagai surface runtime dashboard
- design system internal sekarang punya route Laravel aktif di `/design-system`, sementara `public/design-system.html` diturunkan menjadi snapshot arsip
- chrome shell utama dashboard sekarang dirender dari partial Blade `resources/views/dashboard/partials/shell/app-frame.blade.php`, sehingga loading screen, login screen, sidebar, header, dan breadcrumb tidak lagi diecho sebagai fragmen HTML mentah dari snapshot legacy saat runtime
- wrapper bootstrap script Vue sekarang dirender dari partial Blade:
  - `resources/views/dashboard/partials/shell/app-script-open.blade.php`
  - `resources/views/dashboard/partials/shell/app-script-close.blade.php`
  sehingga pembuka `createApp({ setup() {` dan penutup `mount("#app")` tidak lagi menjadi wrapper raw dari snapshot legacy saat runtime
- cluster helper tanggal dasar di awal `setup()` Vue sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-date-helpers.blade.php`, sehingga util `fmtLocalDate` sampai `isDateInRange` tidak lagi menjadi source runtime langsung dari snapshot legacy
- cluster bootstrap navigasi dasar di awal `setup()` Vue sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-bootstrap-navigation.blade.php`, sehingga state `appLoading`, `activeTab`, `tabConfig`, breadcrumb, accordion sidebar, dan helper `toggleMenuGroup` tidak lagi diambil runtime langsung dari snapshot legacy
- cluster auth/session bootstrap sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-auth-session.blade.php`, sehingga state login dasar seperti `submitting`, `runtimeError`, `notification`, `currentUser`, `isTeknisi`, dan `loginForm` tidak lagi diambil runtime langsung dari snapshot legacy
- cluster protected user bootstrap sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-protected-user-settings.blade.php`, sehingga state profile, auth-users, dan activity-logs dasar tidak lagi diambil runtime langsung dari snapshot legacy
- cluster settings bootstrap sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-settings-cluster.blade.php`, sehingga state/settings loader, draft sync, bulk add, dan save backend tidak lagi diambil runtime langsung dari snapshot legacy
- handler nama stock sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-nama-stock-actions.blade.php`, sehingga modal create/edit, submit, delete, dan save nama stock tidak lagi diambil runtime langsung dari snapshot legacy
- cluster meta IG analytics sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-meta-ig-analytics.blade.php`, sehingga loader CSV, parser, summary, insight, dan render ApexCharts tidak lagi diambil runtime langsung dari snapshot legacy
- cluster profile/user mutation handler sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-profile-user-mutations.blade.php`, sehingga handler mutasi untuk update profil, ganti PIN, dan pembuatan user dashboard tidak lagi diambil runtime langsung dari snapshot legacy
- cluster notification/error utility sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-notification-error-utils.blade.php`, sehingga inferensi tipe notifikasi, toast feedback, dan formatter error ramah pengguna tidak lagi diambil runtime langsung dari snapshot legacy
- cluster helper interaksi shell sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-shell-interaction-helpers.blade.php`, sehingga toggle sidebar, state popover trigger, dan interaksi menu profil tidak lagi diambil runtime langsung dari snapshot legacy
- cluster runner runtime sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-runner-factories.blade.php`, sehingga factory `createMockRunner`, `createWebRunner`, `ensureRunApi`, dan helper resume bootstrap tidak lagi diambil runtime langsung dari snapshot legacy
- cluster reporting/budgeting operasional sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-reporting-and-budgeting.blade.php`, sehingga save bonus config, CRUD ads log, harga kompetitor, LPJK, serta kalkulasi/export budgeting tidak lagi bercampur di tail watcher utama runtime
- cluster lifecycle/watcher runtime sekarang juga dirender dari partial Blade `resources/views/dashboard/partials/shell/app-script-lifecycle-watchers.blade.php`, sehingga bootstrap mount, cleanup listener, scroll-lock observer, dan watcher navigasi tab tidak lagi bercampur dengan utility dropdown/calendar di tail setup utama
- shared runtime helper lintas-domain sekarang mulai dipusatkan di `resources/js/dashboard/shared/runtime-helpers.js`, mencakup helper tanggal, notifikasi/error, admin/settings, dan nama stock
- shell sekarang memuat `resources/views/dashboard/partials/shell/body-app-assembly.blade.php` sebagai pusat perakitan cluster `app-script-*`, sehingga boundary assembly script lebih jelas dibanding fragmen legacy lama
- sidebar shell sekarang juga dipecah ke partial terpisah:
  - `resources/views/dashboard/partials/shell/app-frame-sidebar.blade.php`
  - `resources/views/dashboard/partials/shell/app-frame-sidebar-nav.blade.php`
  - `resources/views/dashboard/partials/shell/app-frame-sidebar-nav-*.blade.php`
- fallback bootstrap helper runtime sudah ditambahkan pada partial Blade yang bergantung pada helper bersama, supaya shell tidak langsung patah jika urutan load bundle/helper belum ideal
- coverage CRUD activity log sudah meluas ke beberapa aksi write backend dan diverifikasi lewat feature test

- `dashboard-shell.css` sekarang sepenuhnya dipisah dari Blade shell dan dimuat via Vite, sehingga blok CSS inline besar tidak lagi ada di head response
- tokenisasi `dashboard-shell.css` sudah berjalan: 0 → 147+ `var(--ppp-*)` usages — semua solid color utama sudah pakai token (`--ppp-bg`, `--ppp-card`, `--ppp-line`, `--ppp-text`, `--ppp-muted`, `--ppp-nav-text`, `--ppp-accent`, `--ppp-accent-dark`, `--ppp-danger`, `--ppp-danger-fill`)
- `@theme` block di `app.css` sudah disinkronkan: semua token `:root` sudah ada di `@theme` — Tailwind utility class seperti `border-ppp-line`, `bg-ppp-card`, `bg-ppp-danger-fill` sekarang valid
- WCAG AA compliance sudah ditegakkan pada komponen utama `dashboard-shell.css`:
  - `--ppp-danger` diubah dari `#dc2626` ke `#b91c1c` (text contrast 5.91:1)
  - `--ppp-danger-fill: #dc2626` ditambah sebagai token terpisah untuk filled button background (white text 4.62:1 ✓)
  - `entity-badge--info` diubah ke blue-600/blue-50 `#2563eb`/`#eff6ff` (4.75:1 ✓)
  - `entity-badge--success` diubah ke `#047857` (5.21:1)
  - `modal-primary-button--success` diubah ke `#047857` (5.48:1)
  - `modal-primary-button--info` diubah ke blue-600 `#2563eb` (5.09:1)
  - `status-pill--warn` diubah ke `#b45309` (4.84:1)
  - semua danger hover color distandarisasi ke `--ppp-danger`
- icon header menu sudah diseragamkan ke satu warna di 26 file menu partial: `bg-indigo-50 text-indigo-600 border border-indigo-100` — tidak ada lagi varian emerald/rose/amber/teal/cyan/yellow/blue/gradient per menu
- `modal-header-icon` shade sudah distandarisasi: semua varian soft `text-{color}-500` diubah ke `text-{color}-600` untuk konsistensi
- color palette design system sudah direfactor dan didokumentasikan di `docs/color-palette.md`:
  - dual token system (`@theme` vs `:root`) kini terdokumentasi jelas
  - Semantic Palette Danger resolved: satu BG (`#fef2f2`), teks badge `#b91c1c`, teks body `#b91c1c`
  - kolom Contrast ratio ditambahkan untuk semua status color

Masalah yang masih tersisa:

- shell HTML masih besar
- state Vue masih global dan padat
- masih ada artefak snapshot/static legacy yang belum sepenuhnya dipindahkan ke surface Laravel-native
- sebagian logic print masih mempertahankan alias inline tipis untuk backward compatibility, walau source of truth sudah berada di modul JS
- vendor asset browser tertentu masih disinkronkan manual ke `public/vendor/dashboard`, belum semuanya diimport langsung dari bundle aplikasi
- runtime helper masih sensitif terhadap urutan inisialisasi dan fallback yang tumpang tindih
- promosi helper dari partial ke shared runtime masih menyisakan risiko duplicate declaration bila boundary variabel lokal belum dibersihkan penuh
- masih ada warning accessibility pada form ganti PIN karena browser mengharapkan field username pada password form

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

Kondisi dinyatakan “100% Laravel fundamental” hanya jika tambahan berikut juga tercapai:

- source of truth render utama tidak lagi bergantung pada snapshot HTML monolitik di `public/`
- surface demo atau preview internal yang masih dipakai tim ikut berada di jalur route/view Laravel, atau secara eksplisit ditandai sebagai arsip non-runtime
- state Vue domain tidak lagi bergantung pada closure global besar yang sulit diuji
- compatibility shim inline hanya tersisa jika benar-benar tidak bisa dihapus, dan jejaknya terdokumentasi jelas
- vendor asset yang masih harus lokal punya asal file yang tersimpan di repo atau dibundel lewat pipeline build yang stabil

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

### 3. ~~Dependency CDN masih ada di head~~ — RESOLVED

Semua dependency runtime sebelumnya yang bergantung CDN sudah dipindahkan ke asset lokal origin. CSP sudah tidak lagi mengizinkan CDN eksternal. Deploy Laravel sudah self-contained untuk runtime utama.

Sisa pekerjaan: klasifikasi tegas surface demo/static (pindah ke Laravel route atau arsip non-runtime).

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
- branding asset utama dashboard sudah berpindah ke asset lokal origin aplikasi
- bridge export per domain sudah dipisah:
  - analytics
  - customer service
  - reporting
  - sales
- print helper sudah dipisah ke modul browser/core
- compatibility layer print di Blade sudah direduksi menjadi adapter tipis ke modul browser/core
- menu yang sudah diekstrak ke Blade partial mencakup domain utama dashboard
- hardening Laravel dasar sudah ditambahkan di middleware response
- print popup URL sudah dibersihkan dari parameter bypass proxy

### Masih tersisa

- eliminasi artefak runtime yang masih memakai arsip HTML lama sebagai referensi operasional
- pemecahan state/action handler per menu
- pengurangan inline compatibility layer yang masih diperlukan
- audit ukuran shell dan pemecahan blok script besar

## Gap Ke 100% Laravel Fundamental

Per 10 Juli 2026, dashboard ini sudah Laravel-native pada jalur runtime utama, tetapi belum 100% Laravel fundamental.

Gap yang masih tersisa:

1. boundary terhadap arsip HTML referensi masih belum ramping penuh, walau runtime aktif sudah pindah ke Blade partial Laravel dan file `resources/legacy/marketing-dashboard-source.html` sekarang hanya dipakai sebagai arsip referensi/test guardrail
2. state Vue utama masih global, sehingga boundary domain belum tegas
3. compatibility layer print inline masih ada walau sudah tipis
4. beberapa surface non-utama seperti demo/static masih hidup sebagai file pointer/arsip statis, bukan response Laravel
5. sebagian vendor asset browser masih dikelola lewat sinkronisasi file lokal, bukan import aplikasi yang lebih terstandardisasi

Konsekuensi praktis:

- perubahan kecil masih bisa menyentuh terlalu banyak area sekaligus
- review dan test regresi tetap mahal
- proses menuju cleanup final belum bisa dilakukan dengan aman dalam satu langkah besar

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

- fase ini sudah selesai untuk dependency runtime yang sebelumnya masih bergantung pada CDN
- CSP sudah tidak lagi mengizinkan `cdn.jsdelivr.net`, `fonts.googleapis.com`, atau `fonts.gstatic.com`
- branding asset utama dashboard juga sudah selesai dipindah ke asset lokal
- helper Excel export browser juga sudah beralih ke asset lokal origin sendiri
- dependency npm `xlsx` yang rentan sudah dikeluarkan dari dependency tree, sementara browser tetap memakai bundle vendor lokal yang disimpan di repo
- sisa pekerjaan fase ini tinggal klasifikasi tegas file demo/static: apakah dipindah ke Laravel route/view atau dinyatakan arsip non-runtime

Prioritas:

1. pastikan tidak ada surface runtime yang masih menarik asset dari CDN
2. rapikan surface demo/static yang masih berdiri sendiri
3. evaluasi vendor asset mana yang masih perlu tetap disinkronkan manual

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

Status repo saat ini:

- pemisahan Blade partial untuk shell dan banyak cluster script sudah berjalan
- tetapi runtime Vue masih dominan berbentuk closure besar yang dirakit dari banyak partial `app-script-*`
- area `auth/profile/settings`, `activity logs`, `nama stock`, `customer service`, dan `LPJK` sudah cukup stabil untuk dijadikan batch modularisasi berikutnya

Urutan implementasi yang direkomendasikan:

1. ekstrak helper shared yang lintas-domain lebih dulu
2. ekstrak domain admin/settings yang coupling-nya rendah
3. ekstrak domain CRUD operasional yang sudah punya boundary tabel jelas
4. ekstrak domain kompleks yang masih banyak dependensi silang terakhir

Checklist operasional:

- `shared`:
  - [ ] pindahkan formatter umum ke `resources/js/dashboard/shared/formatters.js`
  - [x] mulai pusatkan helper notifikasi/error ke runtime shared
  - [ ] pindahkan helper notifikasi/error final ke `resources/js/dashboard/shared/notifications.js`
  - [ ] pindahkan helper URL/origin/backend resolver ke `resources/js/dashboard/shared/url.js`
  - [ ] pindahkan helper runner umum yang tidak bergantung pada Vue state ke `resources/js/dashboard/shared/run-api.js`
- `admin/settings`:
  - [x] pecah state dan aksi awal dari `app-script-protected-user-settings.blade.php`
  - [x] pecah mutasi profil/user awal dari `app-script-profile-user-mutations.blade.php`
  - [ ] pindahkan domain admin/settings penuh ke modul JS terpisah
  - target modul awal:
    - `resources/js/dashboard/menu/admin-users.js`
    - `resources/js/dashboard/menu/activity-logs.js`
    - `resources/js/dashboard/menu/profile.js`
    - `resources/js/dashboard/menu/settings.js`
- `nama stock`:
  - [x] ekstrak helper dan action awal dari `app-script-nama-stock-actions.blade.php`
  - [ ] pindahkan loader, form state, submit, delete, dan opsi dropdown penuh ke modul JS terpisah
  - target modul awal:
    - `resources/js/dashboard/menu/nama-stock.js`
- `customer service`:
  - pecah alur `orderan-online`, `unit-ditanya`, `claim-garansi`, `keep-barang`
  - ekstrak helper pencarian dropdown dan validasi form yang masih berbagi closure global
  - target modul awal:
    - `resources/js/dashboard/menu/customer-service.js`
- `LPJK dan reporting operasional`:
  - pecah handler CRUD LPJK, LPJK detail, ads log, harga kompetitor, bonus/budget config
  - audit dependensi terhadap export/reporting bridge sebelum modul dipisah
  - target modul awal:
    - `resources/js/dashboard/menu/lpjk.js`
    - `resources/js/dashboard/menu/reporting.js`

File sumber yang paling layak dijadikan titik potong berikutnya:

- `resources/views/dashboard/partials/shell/app-script-protected-user-settings.blade.php`
- `resources/views/dashboard/partials/shell/app-script-profile-user-mutations.blade.php`
- `resources/views/dashboard/partials/shell/app-script-nama-stock-actions.blade.php`
- `resources/views/dashboard/partials/shell/app-script-customer-service-crud.blade.php`
- `resources/views/dashboard/partials/shell/app-script-lpjk-operations.blade.php`
- `resources/views/dashboard/partials/shell/app-script-reporting-and-budgeting.blade.php`
- `resources/views/dashboard/partials/shell/app-script-runner-factories.blade.php`

Definition of done fase 3:

- [x] minimal satu domain utama tidak lagi mendefinisikan state dan action secara inline penuh di Blade
- [x] helper shared yang dipindahkan mulai berhenti diduplikasi antar cluster script
- [x] feature test shell tetap hijau
- [ ] perubahan domain tidak menambah dependency global baru di closure root
- [~] file Blade cluster yang disentuh menyusut jelas dan tanggung jawabnya lebih sempit

Status fase 3 saat ini: `in progress`

### Fase 4: Sederhanakan compatibility layer print

Target:

- bridge tetap ada, tapi logic inline lama makin tipis
- source of truth print tinggal modul JS

Guardrail:

- jangan ubah kontrak endpoint
- jangan ubah payload print
- jangan ubah perilaku popup print tanpa test regresi

Status terbaru:

- adapter inline di Blade sudah tidak lagi mereplikasi seluruh flow popup print
- `window.openPrintWindow` dari `print-browser.js` kembali menjadi source of truth
- layer inline tersisa hanya untuk menyuntikkan dependency closure Vue yang memang belum dipisah penuh

Pekerjaan lanjutan yang konkret:

- inventaris fungsi print mana yang masih di-alias dari partial Blade ke runtime JS
- tandai alias yang masih dibutuhkan karena closure Vue belum diekstrak
- setelah fase 3 berjalan, pindahkan dependency closure tersebut ke modul JS agar alias inline bisa dipangkas lagi
- pastikan semua pemanggilan print tetap berujung ke:
  - `resources/js/dashboard/export/print-browser.js`
  - `resources/js/dashboard/export/print-core.js`

Checklist operasional:

- audit partial:
  - `resources/views/dashboard/partials/scripts/print-helpers.blade.php`
  - `resources/views/dashboard/partials/shell/app-script-reporting-and-budgeting.blade.php`
  - `resources/views/dashboard/partials/shell/app-script-runner-factories.blade.php`
- dokumentasikan alias yang tersisa:
  - nama fungsi global
  - consumer yang masih memanggilnya
  - alasan alias belum bisa dihapus
- targetkan hanya dependency injection tipis yang tersisa di Blade, tanpa business logic print di inline script

Definition of done fase 4:

- tidak ada business logic print besar yang tinggal di partial Blade
- alias inline yang tersisa terdokumentasi eksplisit
- semua flow print tetap lolos regresi endpoint `/print-job`
- source of truth print tidak ambigu dan tetap berada di modul JS

### Fase 5: Evaluasi multi-page hanya jika benar-benar perlu

Route per menu baru masuk akal jika:

- state domain sudah terisolasi
- auth dan permission stabil
- export tidak lagi tergantung app root global
- ada alasan performa atau hak akses yang nyata

Sebelum itu, satu route dashboard tetap pilihan paling aman.

### Fase 6: Tutup gap ke 100% Laravel fundamental

Fase ini khusus untuk menutup sisa gap arsitektur, bukan menambah fitur.

Target:

- hentikan ketergantungan aktif terhadap `public/marketing-dashboard.html` sebagai sumber kebenaran runtime
- tegaskan status file static/demo:
  - dipindah ke Laravel view/route jika masih dipakai
  - atau dipindah ke folder arsip/reference jika hanya dokumentasi visual
- perkecil atau hapus compatibility shim print inline yang tersisa
- dokumentasikan vendor asset lokal yang memang sengaja dipertahankan di luar bundle aplikasi

Checklist:

- buat inventaris semua test atau util yang masih membaca arsip HTML referensi
- tandai bagian mana yang masih memakai fragment extraction dari HTML lama
- pindahkan surface demo penting ke Blade jika masih dipakai operasional
- definisikan daftar vendor asset yang:
  - wajib dibundle lewat Vite
  - boleh tetap disinkronkan ke `public/vendor/dashboard`
  - harus dianggap temporary dan dijadwalkan dihapus
- rawat inventaris vendor lokal di `resources/vendor/dashboard/manifest.json` sebagai source of truth sinkronisasi asset browser non-Vite
- pastikan test feature membedakan dengan jelas antara runtime production surface dan file arsip/reference

Status aktual yang perlu ditutup berikutnya:

- file arsip sudah tidak menjadi source runtime aktif, tetapi masih dipakai sebagai reference/guardrail test
- sebagian surface demo/static sudah turun menjadi pointer/arsip, tetapi klasifikasinya belum dirapikan penuh di dokumen dan struktur folder
- vendor asset lokal sudah punya manifest, tetapi belum ada klasifikasi tajam mana yang permanen dan mana yang target migrasi ke bundle

Checklist operasional:

- arsip dan surface referensi:
  - inventaris pembacaan `resources/legacy/marketing-dashboard-source.html`
  - inventaris pembacaan `resources/legacy/design-system-archive.html`
  - pastikan semua pembacaan itu hanya untuk guardrail test atau referensi dokumentasi
- surface publik:
  - pastikan `public/marketing-dashboard.html` tetap hanya pointer/arsip
  - pastikan `public/design-system.html` tetap hanya pointer/arsip atau snapshot referensi
  - tandai jelas di docs jika ada surface publik lain yang bukan runtime Laravel
- vendor asset:
  - kelompokkan asset di `resources/vendor/dashboard/manifest.json` menjadi:
    - permanen lokal
    - kandidat bundle Vite
    - temporary compatibility asset
  - pastikan script `scripts/sync-dashboard-vendor-assets.mjs` tetap sinkron dengan klasifikasi tersebut
- boundary test:
  - tambah/rapikan assertion yang membedakan runtime Blade dari file arsip
  - hindari test baru yang kembali membuat file publik statis menjadi source of truth implisit

Definition of done fase 6:

- tidak ada kebingungan antara runtime Laravel, arsip referensi, dan pointer publik
- semua asset penting punya status yang terdokumentasi
- arsip legacy hanya dipakai sebagai guardrail yang eksplisit
- tim dapat menjelaskan source of truth runtime tanpa menyebut file publik statis lama

## Backlog Eksekusi Disarankan

Urutan kerja yang paling aman dari kondisi repo saat ini:

1. ekstrak helper shared:
   - formatter
   - notifications/error
   - URL/backend resolver
2. ekstrak domain admin/settings:
   - profile
   - auth users
   - activity logs
3. ekstrak domain `nama stock`
4. ekstrak domain customer service
5. ekstrak domain LPJK dan reporting operasional
6. pangkas shim print inline yang tersisa
7. rapikan klasifikasi arsip/static/demo dan inventaris vendor asset

Status backlog saat ini:

- [x] langkah 1 sudah mulai berjalan, tetapi belum selesai penuh
- [x] langkah 2 sudah mulai berjalan, tetapi belum selesai penuh
- [x] langkah 3 sudah mulai berjalan, tetapi belum selesai penuh
- [ ] langkah 4 belum dimulai sebagai batch modularisasi terpisah
- [ ] langkah 5 belum dimulai sebagai batch modularisasi terpisah
- [ ] langkah 6 masih tersisa
- [ ] langkah 7 masih tersisa

Urutan ini dipilih karena:

- admin/settings sudah baru disentuh dan boundary fungsinya jelas
- `nama stock` punya surface CRUD yang relatif terisolasi
- customer service dan LPJK lebih besar, jadi lebih aman dikerjakan setelah helper shared stabil
- cleanup print dan gap 100% Laravel fundamental akan jauh lebih aman setelah closure global mulai mengecil

Definition of done fase ini:

- runtime utama tidak lagi memerlukan snapshot legacy publik sebagai sumber assembly aktif
- tidak ada ambiguity apakah sebuah surface termasuk runtime Laravel atau hanya arsip
- snapshot publik lama hanya boleh menjadi pointer arsip yang jelas ke route Laravel aktif
- compatibility shim inline tersisa minimum dan terdokumentasi
- tim bisa menyebut dashboard ini “100% Laravel fundamental” tanpa caveat besar

## Known Issues Aktual

- [ ] bersihkan duplicate declaration pada helper runtime yang dipromosikan dari partial ke shared bundle
- [ ] rapikan boundary helper nama stock agar tidak ada redeklarasi variabel lokal saat fallback aktif
- [ ] tambahkan field username yang sesuai pada form ganti PIN untuk menutup warning accessibility browser
- [ ] audit lagi urutan include partial helper/runtime setelah ekstraksi batch admin/settings dan nama stock
- [x] ~~`entity-badge--info` di `dashboard-shell.css` masih WCAG fail: `rgb(2 132 199)` (sky-600) pada `rgb(240 249 255)` = 3.70:1~~ — RESOLVED: diubah ke `rgb(37 99 235)` pada `rgb(239 246 255)` (blue-600/blue-50, 4.75:1 ✓), konsisten dengan baris Info di Semantic Palette
- [ ] `rgb(... / alpha)` expressions di `dashboard-shell.css` tidak bisa ditokenisasi dengan `var()` langsung (shadow, focus ring, backdrop) — butuh `color-mix(in srgb, var(--ppp-nav-text) X%, transparent)` atau definisi token RGB komponen terpisah; 48 expressions masih hardcoded
- [x] ~~`#dc2626` dipakai sebagai warna background filled danger button default~~ — RESOLVED: token `--ppp-danger-fill: #dc2626` ditambah ke `app.css` (@theme + :root) dan `dashboard-shell.css` diupdate pakai `var(--ppp-danger-fill)`

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

Dashboard dianggap mencapai “100% Laravel fundamental” jika tambahan berikut terpenuhi:

- runtime utama tidak lagi bertumpu pada snapshot HTML legacy sebagai source of truth aktif
- surface yang dipakai pengguna atau tim internal dirender dari route/view Laravel, bukan file statis liar
- asset penting punya jalur asal yang jelas melalui repo, Vite, atau sinkronisasi vendor yang terdokumentasi
- state dan action domain utama sudah cukup terisolasi untuk diuji tanpa closure global besar
- compatibility layer inline tinggal minimum yang benar-benar tak terhindarkan
