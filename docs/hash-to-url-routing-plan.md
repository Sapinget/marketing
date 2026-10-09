# Plan: Migrasi Menu Hash (`#tab`) ke URL Sendiri

Status (diperbarui 2026-10-09): **selesai di kode dan sudah di-push** (branch `feat/hash-to-url-routing`). 27 dari 28 menu sidebar punya URL sendiri (`dashboard` sengaja tetap di `/`; menu Service dihapus); markup semua halaman terisolasi; script terisolasi untuk TikTok, katalog, meta-ig presentation, followers, img-repo, asset-vendor, dan market-intel (sisa script bersama sengaja tidak dipecah, lihat Fase 1.5); Fase 2 dan Fase 3 selesai sejauh aman. Diuji dengan 436 test PHPUnit dan Chrome headless (Vue build dev: 203/204 + rollback flag mati 12/12 + skenario non-admin). **Yang masih terbuka**: uji manual oleh manusia (klik tombol, isi form, simpan/hapus/upload), satu butir Fase 3 yang sengaja ditunda (hapus `$legacyMenus`), dan keputusan pemilik soal sisa menu Service dan tab tersembunyi (lihat "Sisa pekerjaan dan keputusan" di bagian 6). Dibuat 2026-10-08.
Melengkapi `docs/one-menu-one-blade-roadmap.md` (target arsitektur penuh). Dokumen ini mengatur **urutan batch dan resep per menu** untuk:

1. menghapus routing berbasis `#` (satu menu = satu URL), dan
2. memisahkan **satu menu = satu blade** sungguhan: halaman hanya memuat menu aktif beserta script dan state-nya sendiri (Fase 1.5), bukan seluruh dashboard di belakang URL baru.

Tanpa poin 2, hasilnya hanya URL yang berbeda; semua menu dan seluruh script tetap dirender di setiap halaman.

## 1. Kondisi Saat Ini

`switchTab(tab)` (`partials/shell/app-script-protected-user-settings.blade.php`) hanya mengganti `activeTab` dan menulis `#tab` ke URL. Bila dipanggil dari halaman selain `/`, ia redirect ke `/#tab`. Tab awal diambil dari `_serverTab || hash || localStorage('ppp_active_tab') || 'dashboard'` (`app-script-bootstrap-navigation.blade.php`).

Fakta arsitektur yang menentukan rencana ini:

- `layouts/dashboard.blade.php` hanya pembungkus tipis (`shell.head` + `shell.body`); tidak ada `@stack`/`@push` untuk script per halaman.
- `app-frame.blade.php` meng-include **semua** `partials/menus/*.blade.php` di setiap request. Menu non-aktif disembunyikan `v-show="activeTab === '...'"`. `dedicatedMenuView` hanya mencegah include ganda.
- `body-app-assembly.blade.php` meng-include ~45 file `app-script-*.blade.php` (total ~14 ribu baris) ke **satu** `setup()` Vue, dan `app-script-return-block.blade.php` (1122 baris) mengekspos semua state/fungsi sekaligus.
- Loader data per tab dipicu dari `app-script-runner-session-tail.blade.php` dan `tabDataKey` di `switchTab`.

Menu yang sudah punya URL sendiri (pola yang akan ditiru):

| Route | `activeTab` | `dedicatedMenuView` |
|---|---|---|
| `/promo-pamflet` | `promo_pamflet` | `menus.promo-pamflet` |
| `/ecommerce/tiktok-template` | `tiktok_template` | `menus.tiktok-template` |
| `/katalog/android`, `/katalog/apple`, `/katalog/template-background` | `pricelist_katalog`, `apple_katalog`, `template_background` | `menus.*` (sidebar disembunyikan) |
| `/repository-gambar`, `/inventory/asset-vendor` | `img_repo`, `asset_vendor_inventory` | `menus.*` (sidebar disembunyikan / tidak ada link) |
| `/unit_ditanya` | `unit_ditanya` | memakai `dashboard.index` |

### Menu sidebar dan status migrasi (28 tab; Service dihapus). Semua sudah punya URL kecuali `dashboard`

| Batch | Grup | Tab |
|---|---|---|
| A | Tools & Settings | `harga_kompetitor`, `laporan_event`, `settings`, `nama_stock`, `auth_users`, `activity_logs` |
| B | Customer Service | `orderan_online`, `unit_ditanya` (`/cs/unit-ditanya`; `/unit_ditanya` lama = redirect 301), `claim_garansi_asuransi`, `keep_barang` (`service` dihapus) |
| C | Complain Tracker | `input_claim`, `garansi_cermati`, `garansi_resmi` |
| D | Marketing | `program_promo`, `sell_out`, `ads_log`, `budgeting` |
| E | Analisa Konten | `meta_story`, `meta_feed`, `meta_followers` |
| F | Dashboard & Konten | `dashboard` (tetap di `/`), `master`, `unboxing`, `ideation`, `distribution`, `analytics`, `calendar`, `story` |

### Tab hash yang disembunyikan dari sidebar (tunda, jangan dikerjakan sekarang)

Performa (`bonus_report`, `talent_bonus`, `editor_performance`), Intelijen Pasar (`market_pasar`, `market_intelijen_harga`, `market_audit_harga`, `market_eksternal`, `market_ext_goodponsel`, `market_ext_devstore`, `market_ext_rumahgadget`), `top_content_platform`, `low_content_platform`, `analisa_insight`, `proses_claim`, `profile`.
Mereka tetap dirender di `/` (daftar `$legacyMenus` di `app-frame.blade.php`) dan hidup lewat `#hash` selama `switchTab` legacy dipertahankan (lihat Fase 3). Daftar tab tersembunyi ada di `_hiddenTabs` pada `app-script-bootstrap-navigation.blade.php`. Saat menu dimunculkan lagi, keluarkan dari daftar itu dan kerjakan sebagai batch baru.

## 2. Resep per Menu (berlaku untuk semua batch)

Satu menu = satu perubahan kecil yang bisa diuji dan di-rollback sendiri.

1. **Route** di `routes/web.php`, di blok publik dekat route katalog (bukan di grup `dashboard.auth`, karena shell yang menangani login):
   ```php
   $dashboardPage('/<grup>/<menu>', 'dashboard.<grup>.<menu>', 'dashboard.pages.<grup>.<menu>', '<tab_key>', 'dashboard.partials.menus.<menu>');
   ```
   Helper `$dashboardPage` (di `routes/web.php`) sudah membuat route GET, header `no-store`, dan default `_dashboard_tab` yang dipakai `DashboardPageRoutesTest`.
2. **Page blade** tipis `resources/views/dashboard/pages/<grup>/<menu>.blade.php` (`@extends('layouts.dashboard', ['activeTab' => ...])` + `@section('dashboard-menu')`), meniru `pages/ecommerce/tiktok-template.blade.php`.
3. **`app-frame.blade.php`**: pastikan menu tidak ter-include dua kali. Pola yang dipakai: `@if(($dedicatedMenuView ?? null) !== '<view>') @include('<view>') @endif`.
4. **Sidebar**: ganti `<div @click="switchTab('x')">` menjadi `<a href="/<grup>/<menu>">`, pertahankan class active (`activeTab === 'x'`) dan guard `v-if` yang ada (`canManageSettings`, dst.).
5. **Data loader**: loader yang dipicu per tab ada di `app-script-runner-session-tail.blade.php` (blok `if (tab === '...')` dan `TAB_DATA_MAP`) serta `tabDataKey` di `switchTab`. Pastikan loader tetap jalan saat halaman dibuka langsung dengan `_serverTab` terisi (cek `resumeActiveTabAfterBootstrap`).
6. **`tabConfig`** (breadcrumb) sudah ada per tab, tidak perlu diubah.
7. **Link internal**: cari `switchTab('<tab>')` dan `#<tab>` di seluruh `resources/` (tombol, kartu, notifikasi, chat). Ubah ke `window.location.assign('/<url>')` atau `<a href>`.
8. **Test**: tambah/ubah test route (200, `assertSee("activeTab === '<tab>'", false)`, redirect login bila perlu). Jalankan `php artisan test` lengkap.
9. **Isolasi blade (Fase 1.5, setelah fondasinya ada)**: menu hanya di-include di page-nya sendiri; script menu dipindah ke `@push('scripts')` page tersebut; state/fungsi menu dikeluarkan dari `return` block bersama. Lihat Fase 1.5.
10. **Verifikasi manual di browser**: buka URL langsung, refresh, back/forward, sidebar active state, data termuat, tidak ada error console. (Catatan: perubahan Blade tanpa verifikasi browser pernah menyebabkan bug, lihat Risiko.)

Definition of done per menu: URL langsung berfungsi, sidebar tidak lagi memakai `switchTab` untuk menu itu, tidak ada referensi `#<tab>` tersisa di sumber, test hijau. Setelah Fase 1.5: halaman menu itu tidak merender menu lain dan tidak memuat script menu lain.

## 3. Fase

### Urutan resmi
Fase -1 → Fase 0 → Fase 1.5 fondasi → Batch A (URL + isolasi sekaligus, percontohan) → evaluasi go/no-go → Batch B–F (tiap batch: Fase 1 lalu 1.5 per menu). Fase 2 aktif sejak Batch A. Fase 3 di akhir.

### Fase -1: Prasyarat (selesai 2026-10-09)
- [x] Working tree di-commit sebagai baseline di branch `feat/hash-to-url-routing` (commit `4ec6667`), supaya PR batch tidak bercampur dengan perubahan lain. `public/build/.gitignore` yang terhapus sengaja tidak ikut di-commit.
- [x] Suite test hijau (380 lulus). Dua test merah (`export buttons are icon only`, `menu table headers use shared classes`) disebabkan menu `tiktok-template`; diperbaiki di menunya, bukan di test.
- [x] Batasan role Teknisi dihapus (shell Blade, legacy source, test).

### Fase 0: Persiapan (selesai 2026-10-09)
- [x] Konvensi URL (tabel di bagian 4) disetujui 2026-10-09. Satu sumber kebenaran: tabel ini; bila `one-menu-one-blade-roadmap.md` berbeda, samakan dokumen itu ke tabel ini.
- [x] Buat helper agar route tidak ditulis berulang (`$dashboardPage($uri, $name, $view, $tab, $menuView, $backendUrl = null)` di `routes/web.php`; semua route halaman menu memakainya (7 route lama + semua batch), nama route tidak berubah; argumen ke-7 `$extraViews` untuk partial tambahan seperti modal konten generik; tiap route diberi default `_dashboard_tab` untuk test).
- [x] Test generik `tests/Feature/DashboardPageRoutesTest.php`: menemukan semua route ber-`_dashboard_tab` secara otomatis; tiap halaman 200, memuat `activeTab === '<tab>'`, header `no-store`. Route baru otomatis ikut teruji.
- [x] Keputusan guard: route halaman tetap publik (shell menangani login di sisi klien lewat `/api/auth/session`, server tidak tahu user pada request halaman). Pengamanan nyata ada di API (`dashboard.auth` + `assertUserManagementAccess`/`assertSettingsManagementAccess`/`assertSensitiveLogAccess` di `routes/web.php`). Halaman admin (`auth_users`, `settings`, `activity_logs`) hanya cangkang; guard klien (`canManageUsers`, `canManageSettings`) tetap. Tidak ada middleware session baru. Test wajib: endpoint API tiap menu admin menolak akun non-admin (sebagian sudah ada di `HighRiskDomainRouteAuthorizationTest`).
- [x] Test: API admin (`/api/auth/users`, `/api/activity-logs`, `/api/settings`) menolak tanpa login (401). Test halaman admin tanpa login = 200 ikut otomatis lewat test generik saat route-nya dibuat di Batch A.
- [x] (keputusan saja, tidak ada kode) Konvensi query string: filter menu disimpan sebagai query (`?q=`, `?status=`) hanya bila menu sudah punya state filter terpusat; selain itu tidak dipertahankan. Link lama `/?tab=...` tidak didukung (hanya `#tab`).
- [x] Perilaku URL tak dikenal: Laravel 404 bawaan. Trailing slash (mis. `/cs/order-online/`) sudah dilayani Laravel dengan halaman yang sama (dicek test), tidak perlu redirect. `/#tab_tak_dikenal` jatuh ke `dashboard` seperti sekarang.
- [x] Pencatatan kunjungan menu (`POST /api/menu-visits`) dijadikan helper `trackMenuVisit(tab)` (di `app-script-protected-user-settings.blade.php`, sebelumnya inline di `switchTab`). Dipanggil dari `switchTab` (klik sidebar di `/`) dan dari `resumeActiveTabAfterBootstrap` (sekali per muat halaman). Sebelumnya buka URL/refresh langsung tidak tercatat sama sekali. Catatan: setelah login, tab pendaratan belum tercatat; dibiarkan. Verifikasi manual di browser (tanpa dobel) masih perlu.
- [x] Anggaran performa dan go/no-go: lihat bagian 6. Baseline server-side (diukur 2026-10-09, `php` kernel lokal, bukan browser):

  | Halaman | HTML | gzip | `<script src>` | `<link href>` | marker `activeTab ===` |
  |---|---|---|---|---|---|
  | `/` | 2.269.299 B | 285.926 B | 10 | 23 | 169 |
  | `/katalog/android` | 2.123.140 B | 265.467 B | 10 | 23 | 164 |
  | `/ecommerce/tiktok-template` | 2.055.926 B | 255.155 B | 10 | 23 | 163 |
  | `/promo-pamflet` | 2.055.924 B | 255.526 B | 10 | 23 | 163 |

  Temuan: halaman "dedicated" yang sudah ada hanya ~9% lebih kecil dari `/` (semua menu dan script tetap dirender), jadi target go/no-go -30% memang butuh Fase 1.5. Yang belum diukur: jumlah request dan waktu muat di browser (DevTools/Lighthouse); ukur sebelum Batch A dimulai.
- [x] Rollback: flag `config('dashboard.url_routing')` / env `DASHBOARD_URL_ROUTING` (default aktif) sudah ada di `config/dashboard.php` dan `.env.example`. Dibaca `app-script-bootstrap-navigation` (`urlRouting`) dan `app-frame.blade.php` (`$migratedMenus` dirender lagi di `/` bila flag mati; sidebar kembali ke navigasi hash). Dites oleh `test_flag_off_restores_*`; belum dicoba di browser. Karena script menu belum diisolasi, rollback ini valid; setelah script diisolasi, rollback = revert.

### Fase 1: Migrasi per batch (satu PR per batch, urutan A → F)
Urutan dari risiko terendah ke tertinggi.
- [x] **Batch A** Tools & Settings (6 menu) selesai 2026-10-09 (URL, sidebar, markup terisolasi; script **tidak** diisolasi, lihat catatan di bawah; diuji Chrome headless, belum manual). Mayoritas CRUD sederhana, `activity_logs`/`auth_users`/`settings` punya guard role sendiri.
- [x] **Batch B** Customer Service (5) selesai 2026-10-09 (URL `/cs/*`, sidebar `<a href>`, markup terisolasi, modal order online / unit ditanya / claim garansi / keep barang dipindah dari budgeting ke menunya; service sudah punya modal sendiri; `/unit_ditanya` → 301 ke `/cs/unit-ditanya`; diuji Chrome headless, belum manual). `unit_ditanya` hanya perlu link sidebar di batch ini; rename URL ke `/cs/unit-ditanya` (+ redirect dari `/unit_ditanya`) dikerjakan sebagai langkah terpisah di akhir batch.
- [x] **Batch C** Complain Tracker (3) selesai 2026-10-09 (`/complain/*`, dikerjakan agent paralel di worktree, digabung `8ec382b`; diuji Chrome headless, belum manual).
- [x] **Batch D** Marketing (4) selesai 2026-10-09 (`/marketing/*`, `d015ec0`; `budgeting` tidak punya modal sendiri; diuji Chrome headless, belum manual). `program_promo` terkait route publik `/promo`, jangan bentrok.
- [x] **Batch E** Analisa Konten (3) selesai 2026-10-09 (`/analisa/story-ig`, `/analisa/feed-konten`, `/analisa/followers-ig`, `4f00f08`; diuji Chrome headless, belum manual). Bergantung pada importer Meta (`meta_story`, `meta_feed`, `meta_followers`).
- [x] **Batch F** Dashboard & Konten: 7 dari 8 menu selesai 2026-10-09 (`/konten/master-plan`, `unboxing`, `ideation`, `distribution`, `analytics`, `calendar`, `story`; F1 oleh agent `9bdeaad`, `master` oleh lead). **`dashboard` sengaja tetap di `/`** karena `/` juga menjadi host tab tersembunyi lewat `#hash` (`analisa_insight`, `top/low_content`, `bonus_report`, `talent_bonus`, `editor_performance`, `profile`, `market_*`). Diuji Chrome headless, belum manual.

### Fase 1.5: Isolasi per menu (inti "1 menu 1 blade")

**Isolasi script (dikerjakan 2026-10-09, sebagian).** Markup semua halaman menu sudah terisolasi. Script yang sekarang terisolasi (tidak dimuat di halaman lain): `tiktok-template`, katalog (`pricelist-katalog` + `apple-katalog`, ±225 KB, dimuat di 3 halaman `/katalog/*`), `meta-ig-analytics-presentation` (story-ig, feed-konten), `meta-followers`, `img-repo`, `asset-vendor-inventory`, serta `market-intelligence-operations` yang hanya dimuat di `/` (menu tersembunyi `market-*`). Hasil (server-side): halaman menu biasa ±0,66–0,71 MB (gzip ±105 KB), katalog ±0,92–0,94 MB, `/` 0,94 MB (gzip 130 KB); baseline awal 2,27 MB (gzip 286 KB), jadi -70% raw / -63% gzip.
**Tidak bisa diisolasi tanpa refactor lebih besar** (dipakai banyak script atau markup lain): `settings-cluster`, `calendar-helpers`, `content-list-computed`, `summary-computed-cluster`, `search-select-and-options`, `protected-user-settings`, `profile-user-mutations`, `meta-ig-analytics` (dipakai `nama-stock-actions`, `bonus`, dll.), `master-content-operations`, `distribution-analytics-operations` (dimuat saat bootstrap), `content-insight-trends` (dipakai `master-content-operations` dan export), `bonus-talent-cluster`, `domain-state*`, `customer-service-crud`, `claim-menus`, `price-competitor`/`lpjk`/`ads-log`/`budgeting-operations` (dipakai `summary-computed-cluster` dan export PDF), `nama-stock-actions`, `chat-state`, factory runner. Sisa ±0,6 MB script bersama inilah yang masih dimuat di setiap halaman.

Fondasi dikerjakan sekali **sebelum Batch A**. Isolasi per menu dikerjakan di PR batch yang sama dengan URL-nya (bukan sesudahnya), karena pola ini sudah dipilih sebagai percontohan di bagian 6.

Fondasi (sekali, 1-2 PR, sebelum Batch A dimulai):
- [x] `@stack('menu-scripts')` ditambahkan di `body-app-assembly.blade.php` tepat sebelum `app-script-return-block` (di dalam `setup()` yang sama). Page blade memakai `@push('menu-scripts') @include('...app-script-<menu>') @endpush`.
- [x] Mekanisme state per menu: `const menuExports = {}` di `app-script-open.blade.php`, `...menuExports` di akhir `return`. Script menu mengakhiri dirinya dengan `Object.assign(menuExports, { ...nama })`. Return block tidak perlu diedit lagi untuk menu terisolasi (47 nama `tt*` sudah dipindah).
- [x] `app-frame.blade.php`: halaman ber-`$dedicatedMenuView` hanya merender menu itu; halaman lain merender `$legacyMenus` (daftar eksplisit; sisa 16 entri setelah Fase 3: `dashboard`, `analisa-insight`, `top-content`, `low-content`, `profile`, `bonus-report`, `talent-bonus`, `editor-performance`, `content-modal`, dan 7 `market-*`).
- [x] (sebagian) Pisahkan script **bersama** (dipakai banyak menu: `date-helpers`, `search-select-and-options`, `calendar-helpers`, `summary-computed-cluster`, `auth-session`, `chat-state`, `notification-error-utils`, `shell-interaction-helpers`) dari script **milik satu menu**. Yang bersama tetap dimuat di semua halaman; yang milik satu menu pindah ke halaman menu itu.
- [x] Ketergantungan silang dipetakan otomatis (skrip analisis + dua penjaga test: `test_every_page_declares_everything_its_setup_returns*` dan `test_shared_scripts_never_reference_names_declared_only_in_isolated_scripts`). Petakan: fungsi/state menu A yang dipakai menu B (mis. `master-content-operations` dipakai `ideation`, `top_content_platform`, `distribution`). Catat di tabel pemetaan (lihat bawah) sebelum memindahkan.

Per menu (mengikuti batch Fase 1):
- [x] (untuk script yang bisa) Pindahkan script ke `@push('menu-scripts')` lewat wrapper `shell/menu-scripts-<nama>.blade.php`; `/` memuatnya hanya bila flag mati (blok `@if` di `body-app-assembly`). Script menu tersembunyi yang hanya hidup di `/` memakai `@unless($dedicatedMenuView)`.
- [x] (untuk script terisolasi) Nama dipindah dari `return` block bersama ke `Object.assign(menuExports, {...})` oleh alat bantu; return block turun dari 1078 ke ±760 baris. `app-script-domain-state*` belum disentuh.
- [x] (markup) Menu yang dimigrasi keluar dari `$legacyMenus` di `app-frame` (dikerjakan tiap batch). Tersisa 16 entri (setelah Fase 3), lihat catatan fondasi di atas.
- [x] (untuk script terisolasi) Loader per tab dipindah ke `menuLoaders.<tab>` di script menu (`runActiveTabProtectedLoaders` memanggil `menuLoaders[tab]`). `TAB_DATA_MAP`/`tabDataKey` untuk menu lain belum dipindah.
- [x] Verifikasi: Chrome headless dengan Vue **build dev** (agar peringatan properti tak terdefinisi muncul): 204 pengecekan, 203 lolos; satu peringatan lama di `master-plan` (`<style>` di dalam template Vue, sudah ada sebelum migrasi). Jalur rollback flag mati (`/#tab` untuk 12 tab) lolos 12/12.
- [x] (markup) Test isolasi per batch: `DashboardPageRoutesTest` + `DashboardBatch{C,D,E,F1,F2}Test` memeriksa marker menu ada di halamannya, tidak ada di `/`, dan flag-off mengembalikannya. Memakai pengecekan boolean (`assertPageHas/Lacks`), bukan `assertSee` pada halaman penuh, karena 1-2 MB bisa membuat PHPUnit macet saat gagal. Isolasi script belum punya test.

Percontohan isolasi (2026-10-09): `tiktok_template`. State-nya self-contained (semua nama `tt*`, tidak dipakai file lain). Script dipindah dari `body-app-assembly` ke `@push('menu-scripts')` di `pages/ecommerce/tiktok-template.blade.php`; menu keluar dari `$legacyMenus`, jadi `/` tidak lagi memuat markup maupun script TikTok. Sintaks JS semua halaman diperiksa dengan `node --check`, semua nama ekspor terdeklarasi, dan semua `tt*` di markup terekspor. **Belum diuji di browser** (ekstensi Chrome tidak terhubung): buka `/ecommerce/tiktok-template` setelah login, upload template, edit sel, ekspor.

Temuan ukuran (rendered, server-side): markup menu 1,26 MB + script shell 0,84 MB dari total 2,27 MB. Isolasi markup saja sudah menurunkan halaman dedicated dari ~2,06 MB ke ~0,97 MB (gzip 255 → 157 KB), yaitu 57% lebih kecil dari `/`. Satu file `menus/budgeting.blade.php` saja 311 KB. Isolasi script (state) tambahan nanti memangkas sisa ~0,84 MB, tapi paling berisiko (ketergantungan silang), jadi dikerjakan per menu setelah markup.

Catatan Batch A (2026-10-09):
- **`budgeting.blade.php` ternyata "penampung modal"**: 20 `<teleport>` modal milik banyak menu (harga kompetitor, ads, LPJK x2, sell out, nama stock, story, order online, unit ditanya, claim garansi, keep barang, promo, unboxing, distribution, analytics, calendar day, modal konten generik `modalOpen`, colab) plus dua overlay **global**: `confirmModal` (konfirmasi hapus, dipakai `showConfirm` di banyak script) dan popover kalender (`calendarOpen`). Percontohan sebelumnya (`cc94277`) membuat halaman dedicated kehilangan dua overlay global itu (konfirmasi hapus di Settings/pricelist/asset vendor, date picker di asset vendor). Diperbaiki di Batch A: keduanya dipindah ke `shell/app-frame-global-overlays.blade.php`, selalu dirender di semua halaman, dijaga `test_every_dedicated_page_keeps_global_overlays`.
- Modal milik Batch A dipindah dari budgeting ke menu pemiliknya: harga kompetitor → `harga-kompetitor`, LPJK (2) → `laporan-event`, nama stock → `nama-stock`. Modal menu lain **masih di budgeting** dan dipindah saat menu pemiliknya dimigrasi (Batch B–F). `modalOpen` generik (master/ideation/dst) pindah di Batch F.
- **Script Batch A tidak diisolasi**: `price-competitor`/`lpjk` dipakai `summary-computed-cluster`, `export-*-pdf`, dan markup budgeting; `nama-stock-actions` dipakai `meta-ig-analytics`; `settings-cluster` (`settings`, `jsonApi`, `loadSettings`) dipakai hampir semua script dan dropdown. Dengan begitu flag rollback juga tetap berlaku (flag mati → `$migratedMenus` dirender lagi di `/`). Isolasi script menunggu Batch D (budgeting) / modul bersama.
- Fase 2 (sebagian): `config('dashboard.tab_urls')` = satu sumber kebenaran (dicocokkan dengan route oleh `test_tab_url_map_matches_routes_exactly`); `/#tab` lama, tab tersimpan, dan `switchTab()` untuk tab yang punya URL dialihkan ke URL-nya (`goToMigratedTab`, `_migratedUrl` di bootstrap); sidebar Batch A memakai `<a href>` + `navigateTab()` (flag mati → hash lama). Pesan akses ditolak untuk non-admin sudah bertahan melewati redirect.
- Ukuran (server-side, `php` kernel lokal): `/` 2,27 → 2,05 MB; `/settings` 0,98 MB; `/settings/users` 0,97; `/tools/harga-kompetitor` 0,98; `/tools/laporan-event` 0,98 (gzip ±152 KB vs 286 KB di baseline). Semua halaman Batch A ±52% lebih kecil dari baseline `/`.

Catatan Batch B (2026-10-09): `/` turun ke 1,83 MB (gzip 241 KB); tiap halaman `/cs/*` 0,96–1,00 MB (gzip ±153 KB). Script `customer-service-crud` dan `claim-menus` tetap bersama (dipakai export PDF dan budgeting). `proses_claim` (tab tersembunyi, memakai data service) tetap di `$legacyMenus`.

Catatan Batch C–F (2026-10-09, dikerjakan 4 agent paralel di git worktree lalu digabung; satu konflik trivial di daftar `$legacyMenus`):
- `budgeting.blade.php` dikosongkan dari modal host (311 → 25 KB); semua modal ada di menu pemilik. Modal konten generik (`modalOpen`) dan daftar colab ada di `menus/content-modal.blade.php` (tetap di `$legacyMenus`; halaman yang membutuhkannya memuatnya lewat argumen ke-7 `$extraViews` pada `$dashboardPage`: ideation, calendar, master-plan). Modal story diekstrak ke `menus/story-modal.blade.php` (dipakai `story` dan `calendar`).
- `calendar` dan `analytics` sekarang memuat data master plan saat dibuka langsung (`runActiveTabProtectedLoaders`); sebelumnya hanya `master`/`ideation`.
- Ukuran akhir (server-side): `/` 1,46 MB; semua 36 halaman menu 0,95–1,02 MB (gzip ±150–159 KB), semuanya 200 dan lolos `node --check`. Test: 428 lulus. Anggaran ukuran halaman dedicated di test: 1,15 MB.
- Sisa di `$legacyMenus` (dirender di `/`): `dashboard`, `analisa-insight`, `top-content`, `low-content`, `profile`, `bonus-report`, `talent-bonus`, `editor-performance`, `market-*` (7), `content-modal`, plus menu yang sudah punya URL (`pricelist-katalog`, `template-background`, `apple-katalog`, `img-repo`, `asset-vendor-inventory`, `promo-pamflet`), yang bisa dikeluarkan di Fase 3 karena `switchTab`/`#hash` sudah dialihkan lewat `tab_urls`.

Perbaikan kecil (2026-10-09): blok `<style>` di `menus/master-plan.blade.php` dihapus (Vue mengabaikan tag itu di template; aturannya sudah ada di `resources/css/dashboard-shell.css`) dan dijaga `test_menu_templates_have_no_inline_style_or_script_tags`.

Catatan (2026-10-09): menu **Service dihapus** atas permintaan (route `/cs/service`, page, menu blade, sidebar, `tab_urls`, `tabConfig`, loader tab). Yang sengaja **tidak** dihapus: state/fungsi service di script bersama (`serviceData`, `loadServiceData`, `app-script-customer-service-crud`), API `/api/service*`, tabel `services`/`service_claims` beserta migrasinya, dan menu tersembunyi `proses_claim` yang memakai data service. Saat diuji di browser, modal Service memang tidak pernah tersambung (`serviceModalOpen`, `serviceForm`, `saveService` tidak ada di `return`), kondisi yang sudah ada sebelum migrasi.

Pemetaan script ke menu (isi saat mengerjakan, contoh awal dari `one-menu-one-blade-roadmap.md`):

| Menu (tab) | Script milik menu | Bergantung pada |
|---|---|---|
| `dashboard` | `summary-computed-cluster` | semua data domain; kerjakan terakhir |
| `master`, `ideation`, `top/low_content_platform` | `master-content-operations`, `content-list-computed` | dipakai bersama 4 menu, pecah dulu jadi modul bersama |
| `harga_kompetitor` | `price-competitor-operations` | `search-select-and-options` |
| `laporan_event` | `lpjk-operations` | `date-helpers` |
| `ads_log` | `ads-log-operations` | `date-helpers` |
| `budgeting` | `budgeting-operations`, `reporting-and-budgeting` | `reporting-export-bridge` |
| `meta_story`, `meta_feed` | `meta-ig-analytics`, `-presentation` | satu modul bersama |
| `meta_followers` | `meta-followers` | - |
| `orderan_online`, `keep_barang`, `claim_garansi_asuransi` | `customer-service-crud`, `claim-menus` | `date-helpers` |
| `bonus_report`, `talent_bonus`, `editor_performance` | `bonus-talent-cluster` | tersembunyi; tunda |

Kriteria selesai fase ini: tidak ada `@include` menu non-aktif di `app-frame`, tidak ada script menu di `body-app-assembly` selain yang bersama, `return` block hanya berisi state bersama + hasil gabungan otomatis.

### Fase 2: Kompatibilitas link lama
Berjalan paralel dengan Fase 1, aktif sejak batch pertama.
- [x] Pemetaan `tab → URL` di satu tempat: `config('dashboard.tab_urls')`, dibagi ke JS lewat `@json` (`tabUrls` di `app-script-bootstrap-navigation.blade.php`); `test_tab_url_map_matches_routes_exactly` menjaga kesamaannya dengan route.
- [x] Saat `/` dibuka dengan `#tab` yang sudah punya URL, `location.replace` ke URL baru (`_migratedUrl`). Terbukti di Chrome headless untuk 7 tab + `/unit_ditanya`.
- [x] (di kode, belum diuji langsung) `localStorage('ppp_active_tab')` ikut jalur yang sama karena `_savedTab` = server tab, hash, atau tab tersimpan; `_hiddenTabs` mencegah tab tersembunyi dipulihkan. Pesan "Akses manajemen user hanya untuk Super Admin" sekarang bertahan melewati redirect (`redirectWithNotice` + `showFlashNotice`, lewat `sessionStorage`); terbukti di Chrome headless dengan akun non-admin.
- [x] `switchTab(tab)`: bila `tab` ada di pemetaan, `goToMigratedTab` membuka URL-nya; bila tidak (tab tersembunyi), perilaku hash lama. Sidebar memakai `<a href>` + `navigateTab()`.

### Fase 3: Pembersihan (dikerjakan 2026-10-09 sejauh aman; sisa butir bergantung Fase 1.5/4)
- [x] (sebagian, sengaja) Dengan flag `url_routing` aktif, tab yang punya URL tidak lagi melewati cabang hash di `switchTab` (`goToMigratedTab` lebih dulu). Cabang hash **tidak dibuang** karena masih dipakai tab tersembunyi dan jalur rollback flag mati; buang bersama flag setelah rilis stabil.
- [x] (dinilai, tidak dihapus) `_hiddenTabs` berisi 4 tab ber-URL (`pricelist_katalog`, `apple_katalog`, `template_background`, `img_repo`) yang memang tidak ada di sidebar; daftarnya hanya mencegah tab itu dipulihkan dari `localStorage` sebagai halaman pendaratan `/`. Tanpa daftar itu, membuka `/` setelah mengunjungi katalog akan selalu dialihkan ke katalog. Tetap dipakai.
- [x] Include ganda sudah hilang (`@if($dedicatedMenuView !== ...)` diganti satu mekanisme `$dedicatedMenuView` / `$legacyMenus`). Enam menu yang sudah ber-URL sebelum migrasi (`pricelist-katalog`, `template-background`, `apple-katalog`, `img-repo`, `asset-vendor-inventory`, `promo-pamflet`) keluar dari `$legacyMenus` dan hanya dirender di `/` bila flag mati (`$migratedMenus`). `/` turun dari 1,46 MB ke 1,22 MB (HTML terkompres belum diukur ulang). `v-show="activeTab === ..."` di menu tidak dihapus (masih dibutuhkan di `/` oleh menu tersembunyi dan dicek test).
- [ ] Hapus `$legacyMenus` (sisa 16 entri: `dashboard`, `analisa-insight`, `top-content`, `low-content`, `profile`, `bonus-report`, `talent-bonus`, `editor-performance`, `content-modal`, 7 `market-*`), daftar manual di `return` block, dan `app-script-domain-state*`. Menunggu Fase 1.5 (script) dan keputusan soal tab tersembunyi (Fase 4).
- [x] `docs/one-menu-one-blade-roadmap.md` diberi banner status dan peta URL (kotak per menu di sana sengaja tidak dicentang karena definisinya termasuk isolasi script). `docs/component-catalog.md` tidak berubah: tidak ada komponen UI baru.

### Fase 4 (opsional): Tab tersembunyi
Kerjakan hanya bila menunya diaktifkan lagi: Performa, Intelijen Pasar, Top/Low Konten, Insight & Tren, Proses Claim (memakai data service; menu Service dihapus), Profile. Gunakan resep bagian 2.

## 4. Peta URL (disetujui 2026-10-09; sumber kebenaran runtime: `config/dashboard.php` `tab_urls`)

| Tab | URL |
|---|---|
| `harga_kompetitor` | `/tools/harga-kompetitor` |
| `laporan_event` | `/tools/laporan-event` |
| `settings` | `/settings` |
| `nama_stock` | `/settings/nama-stock` |
| `auth_users` | `/settings/users` |
| `activity_logs` | `/settings/activity-logs` |
| `orderan_online` | `/cs/order-online` |
| `unit_ditanya` | `/cs/unit-ditanya` (`/unit_ditanya` lama = redirect 301) |
| ~~`service`~~ | dihapus (route `/cs/service` tidak ada lagi) |
| `claim_garansi_asuransi` | `/cs/claim-garansi` |
| `keep_barang` | `/cs/keep-barang` |
| `input_claim` | `/complain/input-claim` |
| `garansi_cermati` | `/complain/garansi-cermati` |
| `garansi_resmi` | `/complain/garansi-resmi` |
| `program_promo` | `/marketing/program-promo` |
| `sell_out` | `/marketing/sell-out` |
| `ads_log` | `/marketing/ads-log` |
| `budgeting` | `/marketing/budgeting` |
| `meta_story` | `/analisa/story-ig` |
| `meta_feed` | `/analisa/feed-konten` |
| `meta_followers` | `/analisa/followers-ig` |
| `dashboard` | `/` (tetap) |
| `master` | `/konten/master-plan` |
| `unboxing` | `/konten/unboxing` |
| `ideation` | `/konten/ideation` |
| `distribution` | `/konten/distribution` |
| `analytics` | `/konten/analytics` |
| `calendar` | `/konten/calendar` |
| `story` | `/konten/story` |

Tabel ini sumber kebenaran dokumen; `docs/one-menu-one-blade-roadmap.md` masih perlu disamakan (lihat Fase 3).

## 5. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Satu Vue app dengan state bersama; pindah halaman = full reload, state (draft form, filter, chat terbuka) hilang | Terima sebagai konsekuensi; simpan filter penting ke `localStorage`/query string bila perlu. Chat panel di-bootstrap ulang tiap halaman, uji. |
| Blade di dalam `@verbatim` bocor ke Vue (komentar `{{-- --}}` pernah menyebabkan `SyntaxError: missing ) after argument list`) | Di partial sidebar/menu pakai komentar HTML `<!-- -->`; jangan pakai sintaks Blade di dalam `@verbatim`. Kompilasi template dengan `@vue/compiler-dom` sebagai cek cepat. |
| Test `MarketingDashboardShellTest` memeriksa string tertentu (jumlah `<select`, class header tabel, `activeTab === '...'`) | Jalankan suite penuh tiap PR; ikuti konvensi: tanpa `<select>` native, `<th class="table-header-cell ...">`. |
| Isolasi script memutus ketergantungan silang antar menu (fungsi/state dipakai menu lain, tanpa tipe/penanda) | Tabel pemetaan di Fase 1.5 diisi dulu; modul bersama diekstrak sebelum menu dipindah; satu menu per PR; uji buka URL langsung tiap menu. |
| `setup()` Vue tunggal: script yang disisipkan lewat `@push` harus berada di scope `setup()`, bukan setelah `createApp` | Tempatkan `@stack('menu-scripts')` di dalam `setup()`; verifikasi dengan compile + buka halaman. |
| Loader data tidak jalan saat buka URL langsung | Langkah 5 resep; uji "buka URL langsung + refresh" untuk tiap menu. |
| Guard role hanya di sisi sidebar | Fase 0: guard sisi server untuk menu admin (Teknisi di luar lingkup). |
| Tab tersimpan di `localStorage` mengarahkan ke menu tersembunyi/usang | Pemetaan Fase 2 + `_hiddenTabs` (sudah diterapkan). |
| Worker proxy memblokir path baru | Dicek 2026-10-09: `worker-proxy/src/index.js` meneruskan semua path ke origin (hanya `/__health` yang ditangani sendiri), tidak ada allowlist. Aman; hindari memakai path `/__health`. |
| Navigasi = reload penuh; tiap halaman memanggil ulang `/api/auth/session`, chat bootstrap, data global (master plan untuk dropdown) | Ukur di baseline (Fase 0); target go/no-go di bagian 6. Cache data global di `sessionStorage` bila perlu. |
| Kunjungan menu dobel atau hilang setelah pindah ke URL | Lihat bullet menu-visits di Fase 0. |
| Cache lama di browser/worker (`workers.dev`) | Header `no-store` pada route halaman (sudah di resep); beri tahu hard refresh setelah deploy. |
| Migration/DB berbeda antara lokal dan production | Jalankan `php artisan migrate --force` di production tiap rilis (contoh: index `activity_logs`). |

## 6. Estimasi

| Fase | Ukuran |
|---|---|
| 0 | kecil (1 PR) |
| 1 batch A–E | sedang, 1 PR per batch (3–6 menu) |
| 1 batch F | besar, pecah menjadi 2 PR (`dashboard`+`master`, sisanya) |
| 1.5 fondasi | sedang-besar (stack, mekanisme ekspor state, pemisahan script bersama vs milik menu) |
| 1.5 per menu | kecil-sedang, digabung dalam PR batch yang sama |
| 2 | kecil, ikut batch A |
| 3 | kecil |

Rekomendasi (urutan resmi): Fase 0, lalu fondasi Fase 1.5, lalu Batch A (URL + isolasi sekaligus) sebagai percontohan.

**Go/no-go setelah Batch A** (angka dibandingkan dengan baseline Fase 0):
- HTML halaman menu Batch A turun minimal 30% dibanding `/`.
- Request awal tidak bertambah lebih dari 1; waktu muat tidak lebih lambat dari baseline.
- `php artisan test` hijau; tidak ada error console; tiap menu Batch A lolos verifikasi browser (buka langsung, refresh, back/forward).
- Satu menu terisolasi menyentuh paling banyak: route, page blade, menu blade, script menunya, sidebar, test (tanpa mengedit `return` block 1122 baris).
Bila salah satu gagal: berhenti, selesaikan hanya Fase 1 (URL) untuk batch lain dan jadwalkan 1.5 terpisah.

Konsekuensi jalur URL-saja: halaman tetap berat karena semua menu dan script tetap dirender.

### Sisa pekerjaan dan keputusan (per 2026-10-09)

Kode: tidak ada butir wajib yang tersisa. Yang masih terbuka:

| Item | Jenis | Catatan |
|---|---|---|
| Uji manual: tambah/edit/hapus data, upload gambar, impor followers, edit+ekspor TikTok, dialog konfirmasi hapus | Wajib sebelum merge | Chrome headless tidak mengisi form, menyimpan, atau mengunggah berkas sungguhan |
| Buat/perbarui PR | Wajib | `gh` yang terpasang hanya punya izin READ; buat lewat `https://github.com/Sapinget/marketing/pull/new/feat/hash-to-url-routing` |
| `php artisan migrate --force` + hard refresh setelah deploy | Saat deploy | Lihat risiko "Migration/DB" dan "Cache lama" |
| Fase 3: hapus `$legacyMenus`, daftar manual `return`, `app-script-domain-state*`, cabang hash `switchTab`, flag `url_routing` | Ditunda | Menunggu rilis stabil dan keputusan tab tersembunyi (Fase 4) |
| Fase 4: migrasikan tab tersembunyi (Performa, Intelijen Pasar, Top/Low Konten, Insight, Proses Claim, Profile) | Keputusan pemilik | Hanya bila menunya dimunculkan lagi |
| Sisa menu Service: state/API/tabel (`services`, `service_claims`) | Keputusan pemilik | Tabel sengaja tidak dihapus (data hilang bila di-drop) |
| Isolasi sisa ±0,6 MB script bersama | Opsional | Risiko tinggi, hemat ±0,1–0,2 MB per halaman; sebaiknya ditunda sampai ada bukti perlu |
| ±135 baris `sessions` anonim dari server uji awal di MySQL asli | Housekeeping | Tidak berbahaya; bisa dihapus berdasarkan user agent HeadlessChrome/curl dan `user_id` kosong |

