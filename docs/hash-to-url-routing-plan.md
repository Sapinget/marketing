# Plan: Migrasi Menu Hash (`#tab`) ke URL Sendiri

Status: direvisi 2026-10-09; Fase -1, Fase 0, dan Batch A (URL + isolasi markup) selesai di kode; fondasi Fase 1.5 sebagian; Fase 2 sebagian. **Belum ada verifikasi browser** untuk Batch A dan percontohan (ekstensi Chrome tidak terhubung). Dibuat 2026-10-08.
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

### Menu yang masih hash dan tampil di sidebar (29 tab)

| Batch | Grup | Tab |
|---|---|---|
| A | Tools & Settings | `harga_kompetitor`, `laporan_event`, `settings`, `nama_stock`, `auth_users`, `activity_logs` |
| B | Customer Service | `orderan_online`, `unit_ditanya` (route `/unit_ditanya` sudah ada; sidebar belum link; rename ke `/cs/unit-ditanya` = tugas terpisah), `service`, `claim_garansi_asuransi`, `keep_barang` |
| C | Complain Tracker | `input_claim`, `garansi_cermati`, `garansi_resmi` |
| D | Marketing | `program_promo`, `sell_out`, `ads_log`, `budgeting` |
| E | Analisa Konten | `meta_story`, `meta_feed`, `meta_followers` |
| F | Dashboard & Konten | `dashboard`, `master`, `unboxing`, `ideation`, `distribution`, `analytics`, `calendar`, `story` |

### Tab hash yang disembunyikan dari sidebar (tunda, jangan dikerjakan sekarang)

Performa (`bonus_report`, `talent_bonus`, `editor_performance`), Intelijen Pasar (`market_pasar`, `market_intelijen_harga`, `market_audit_harga`, `market_eksternal`, `market_ext_goodponsel`, `market_ext_devstore`, `market_ext_rumahgadget`), `top_content_platform`, `low_content_platform`, `analisa_insight`, `proses_claim`, `profile`.
Mereka tetap hidup lewat `#hash` selama `switchTab` legacy dipertahankan (lihat Fase 3). Daftar tab tersembunyi ada di `_hiddenTabs` pada `app-script-bootstrap-navigation.blade.php`. Saat menu dimunculkan lagi, keluarkan dari daftar itu dan kerjakan sebagai batch baru.

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
- [x] Buat helper agar route tidak ditulis berulang (`$dashboardPage($uri, $name, $view, $tab, $menuView, $backendUrl = null)` di `routes/web.php`; 7 route lama sudah memakainya, nama route tidak berubah; tiap route diberi default `_dashboard_tab` untuk test).
- [x] Test generik `tests/Feature/DashboardPageRoutesTest.php`: menemukan semua route ber-`_dashboard_tab` secara otomatis; tiap halaman 200, memuat `activeTab === '<tab>'`, header `no-store`. Route baru otomatis ikut teruji.
- [x] Keputusan guard: route halaman tetap publik (shell menangani login di sisi klien lewat `/api/auth/session`, server tidak tahu user pada request halaman). Pengamanan nyata ada di API (`dashboard.auth` + `assertUserManagementAccess`/`assertSettingsManagementAccess`/`assertSensitiveLogAccess` di `routes/web.php`). Halaman admin (`auth_users`, `settings`, `activity_logs`) hanya cangkang; guard klien (`canManageUsers`, `canManageSettings`) tetap. Tidak ada middleware session baru. Test wajib: endpoint API tiap menu admin menolak akun non-admin (sebagian sudah ada di `HighRiskDomainRouteAuthorizationTest`).
- [x] Test: API admin (`/api/auth/users`, `/api/activity-logs`, `/api/settings`) menolak tanpa login (401). Test halaman admin tanpa login = 200 ikut otomatis lewat test generik saat route-nya dibuat di Batch A.
- [ ] Konvensi query string: filter menu disimpan sebagai query (`?q=`, `?status=`) hanya bila menu sudah punya state filter terpusat; selain itu tidak dipertahankan. Link lama `/?tab=...` tidak didukung (hanya `#tab`).
- [x] Perilaku URL tak dikenal: Laravel 404 bawaan. Trailing slash (`/cs/service/`) sudah dilayani Laravel dengan halaman yang sama (dicek test), tidak perlu redirect. `/#tab_tak_dikenal` jatuh ke `dashboard` seperti sekarang.
- [x] Pencatatan kunjungan menu (`POST /api/menu-visits`) dijadikan helper `trackMenuVisit(tab)` (di `app-script-protected-user-settings.blade.php`, sebelumnya inline di `switchTab`). Dipanggil dari `switchTab` (klik sidebar di `/`) dan dari `resumeActiveTabAfterBootstrap` (sekali per muat halaman). Sebelumnya buka URL/refresh langsung tidak tercatat sama sekali. Catatan: setelah login, tab pendaratan belum tercatat; dibiarkan. Verifikasi manual di browser (tanpa dobel) masih perlu.
- [x] Anggaran performa dan go/no-go: lihat bagian 6. Baseline server-side (diukur 2026-10-09, `php` kernel lokal, bukan browser):

  | Halaman | HTML | gzip | `<script src>` | `<link href>` | marker `activeTab ===` |
  |---|---|---|---|---|---|
  | `/` | 2.269.299 B | 285.926 B | 10 | 23 | 169 |
  | `/katalog/android` | 2.123.140 B | 265.467 B | 10 | 23 | 164 |
  | `/ecommerce/tiktok-template` | 2.055.926 B | 255.155 B | 10 | 23 | 163 |
  | `/promo-pamflet` | 2.055.924 B | 255.526 B | 10 | 23 | 163 |

  Temuan: halaman "dedicated" yang sudah ada hanya ~9% lebih kecil dari `/` (semua menu dan script tetap dirender), jadi target go/no-go -30% memang butuh Fase 1.5. Yang belum diukur: jumlah request dan waktu muat di browser (DevTools/Lighthouse); ukur sebelum Batch A dimulai.
- [x] Rollback: flag `config('dashboard.url_routing')` / env `DASHBOARD_URL_ROUTING` (default aktif) sudah ada di `config/dashboard.php` dan `.env.example`. Belum dibaca siapa pun; sidebar mulai memakainya di Batch A (flag mati = sidebar kembali ke `switchTab` hash, route baru tetap ada).

### Fase 1: Migrasi per batch (satu PR per batch, urutan A → F)
Urutan dari risiko terendah ke tertinggi.
- [x] **Batch A** Tools & Settings (6 menu) selesai 2026-10-09 (URL, sidebar, markup terisolasi; script **tidak** diisolasi, lihat catatan di bawah; belum diuji browser). Mayoritas CRUD sederhana, `activity_logs`/`auth_users`/`settings` punya guard role sendiri.
- [ ] **Batch B** Customer Service (5). `unit_ditanya` hanya perlu link sidebar di batch ini; rename URL ke `/cs/unit-ditanya` (+ redirect dari `/unit_ditanya`) dikerjakan sebagai langkah terpisah di akhir batch.
- [ ] **Batch C** Complain Tracker (3).
- [ ] **Batch D** Marketing (4). `program_promo` terkait route publik `/promo`, jangan bentrok.
- [ ] **Batch E** Analisa Konten (3). Bergantung pada importer Meta (`meta_story`, `meta_feed`, `meta_followers`).
- [ ] **Batch F** Dashboard & Konten (8). Paling berat: `dashboard` dan `master` memuat banyak state bersama (`app-script-summary-computed-cluster`, `master-content-operations`). Tab `dashboard` dipakai sebagai default `/`, jadi `/` harus tetap menampilkannya (jangan redirect).

### Fase 1.5: Isolasi per menu (inti "1 menu 1 blade")

Fondasi dikerjakan sekali **sebelum Batch A**. Isolasi per menu dikerjakan di PR batch yang sama dengan URL-nya (bukan sesudahnya), karena pola ini sudah dipilih sebagai percontohan di bagian 6.

Fondasi (sekali, 1-2 PR, sebelum Batch A dimulai):
- [x] `@stack('menu-scripts')` ditambahkan di `body-app-assembly.blade.php` tepat sebelum `app-script-return-block` (di dalam `setup()` yang sama). Page blade memakai `@push('menu-scripts') @include('...app-script-<menu>') @endpush`.
- [x] Mekanisme state per menu: `const menuExports = {}` di `app-script-open.blade.php`, `...menuExports` di akhir `return`. Script menu mengakhiri dirinya dengan `Object.assign(menuExports, { ...nama })`. Return block tidak perlu diedit lagi untuk menu terisolasi (47 nama `tt*` sudah dipindah).
- [x] `app-frame.blade.php`: halaman ber-`$dedicatedMenuView` hanya merender menu itu; halaman lain merender `$legacyMenus` (daftar eksplisit 49 menu, menyusut tiap batch; `tiktok-template` sudah keluar).
- [ ] (belum) Pisahkan script **bersama** (dipakai banyak menu: `date-helpers`, `search-select-and-options`, `calendar-helpers`, `summary-computed-cluster`, `auth-session`, `chat-state`, `notification-error-utils`, `shell-interaction-helpers`) dari script **milik satu menu**. Yang bersama tetap dimuat di semua halaman; yang milik satu menu pindah ke halaman menu itu.
- [ ] (belum, kerjakan per batch) Petakan ketergantungan silang: fungsi/state menu A yang dipakai menu B (mis. `master-content-operations` dipakai `ideation`, `top_content_platform`, `distribution`). Catat di tabel pemetaan (lihat bawah) sebelum memindahkan.

Per menu (mengikuti batch Fase 1):
- [ ] Pindahkan `app-script-<menu>-operations.blade.php` ke `@push('menu-scripts')` di page blade menu, bukan di `body-app-assembly`.
- [ ] Hapus state/fungsi menu dari `return` block bersama dan dari `app-script-domain-state*.blade.php`; pindahkan ke script menu.
- [ ] Hapus `@include` menu dari daftar `$legacyMenus` di `app-frame`.
- [ ] (belum) Pindahkan blok `if (tab === '<tab>')` dan `tabDataKey` menu itu dari `runner-session-tail`/`switchTab` ke script menu (dipanggil saat `onMounted`).
- [ ] Verifikasi: `view-source` halaman menu tidak memuat markup/script menu lain; error console bersih; ukuran HTML turun.
- [ ] Test otomatis per menu: `assertDontSee` marker menu lain (mis. `activeTab === '<tab_lain>'`) pada halaman menu itu. Tambah ke `DedicatedMenuBladeViewsTest`.

Percontohan isolasi (2026-10-09): `tiktok_template`. State-nya self-contained (semua nama `tt*`, tidak dipakai file lain). Script dipindah dari `body-app-assembly` ke `@push('menu-scripts')` di `pages/ecommerce/tiktok-template.blade.php`; menu keluar dari `$legacyMenus`, jadi `/` tidak lagi memuat markup maupun script TikTok. Sintaks JS semua halaman diperiksa dengan `node --check`, semua nama ekspor terdeklarasi, dan semua `tt*` di markup terekspor. **Belum diuji di browser** (ekstensi Chrome tidak terhubung): buka `/ecommerce/tiktok-template` setelah login, upload template, edit sel, ekspor.

Temuan ukuran (rendered, server-side): markup menu 1,26 MB + script shell 0,84 MB dari total 2,27 MB. Isolasi markup saja sudah menurunkan halaman dedicated dari ~2,06 MB ke ~0,97 MB (gzip 255 → 157 KB), yaitu 57% lebih kecil dari `/`. Satu file `menus/budgeting.blade.php` saja 311 KB. Isolasi script (state) tambahan nanti memangkas sisa ~0,84 MB, tapi paling berisiko (ketergantungan silang), jadi dikerjakan per menu setelah markup.

Catatan Batch A (2026-10-09):
- **`budgeting.blade.php` ternyata "penampung modal"**: 20 `<teleport>` modal milik banyak menu (harga kompetitor, ads, LPJK x2, sell out, nama stock, story, order online, unit ditanya, claim garansi, keep barang, promo, unboxing, distribution, analytics, calendar day, modal konten generik `modalOpen`, colab) plus dua overlay **global**: `confirmModal` (konfirmasi hapus, dipakai `showConfirm` di banyak script) dan popover kalender (`calendarOpen`). Percontohan sebelumnya (`cc94277`) membuat halaman dedicated kehilangan dua overlay global itu (konfirmasi hapus di Settings/pricelist/asset vendor, date picker di asset vendor). Diperbaiki di Batch A: keduanya dipindah ke `shell/app-frame-global-overlays.blade.php`, selalu dirender di semua halaman, dijaga `test_every_dedicated_page_keeps_global_overlays`.
- Modal milik Batch A dipindah dari budgeting ke menu pemiliknya: harga kompetitor → `harga-kompetitor`, LPJK (2) → `laporan-event`, nama stock → `nama-stock`. Modal menu lain **masih di budgeting** dan dipindah saat menu pemiliknya dimigrasi (Batch B–F). `modalOpen` generik (master/ideation/dst) pindah di Batch F.
- **Script Batch A tidak diisolasi**: `price-competitor`/`lpjk` dipakai `summary-computed-cluster`, `export-*-pdf`, dan markup budgeting; `nama-stock-actions` dipakai `meta-ig-analytics`; `settings-cluster` (`settings`, `jsonApi`, `loadSettings`) dipakai hampir semua script dan dropdown. Dengan begitu flag rollback juga tetap berlaku (flag mati → `$migratedMenus` dirender lagi di `/`). Isolasi script menunggu Batch D (budgeting) / modul bersama.
- Fase 2 (sebagian): `config('dashboard.tab_urls')` = satu sumber kebenaran (dicocokkan dengan route oleh `test_tab_url_map_matches_routes_exactly`); `/#tab` lama, tab tersimpan, dan `switchTab()` untuk tab yang punya URL dialihkan ke URL-nya (`goToMigratedTab`, `_migratedUrl` di bootstrap); sidebar Batch A memakai `<a href>` + `navigateTab()` (flag mati → hash lama). Belum: pemeliharaan `ppp_active_tab` untuk hidden tabs, dan pesan "Akses manajemen user hanya untuk Super Admin" hilang saat redirect ke `/settings` (reload).
- Ukuran (server-side, `php` kernel lokal): `/` 2,27 → 2,05 MB; `/settings` 0,98 MB; `/settings/users` 0,97; `/tools/harga-kompetitor` 0,98; `/tools/laporan-event` 0,98 (gzip ±152 KB vs 286 KB di baseline). Semua halaman Batch A ±52% lebih kecil dari baseline `/`.

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
| `orderan_online`, `service`, `keep_barang`, `claim_garansi_asuransi` | `customer-service-crud`, `claim-menus` | `date-helpers` |
| `bonus_report`, `talent_bonus`, `editor_performance` | `bonus-talent-cluster` | tersembunyi; tunda |

Kriteria selesai fase ini: tidak ada `@include` menu non-aktif di `app-frame`, tidak ada script menu di `body-app-assembly` selain yang bersama, `return` block hanya berisi state bersama + hasil gabungan otomatis.

### Fase 2: Kompatibilitas link lama
Berjalan paralel dengan Fase 1, aktif sejak batch pertama.
- [ ] Pemetaan `tab → URL` di satu tempat (JS object di `app-script-bootstrap-navigation.blade.php`, dibagi dengan server lewat `@json`).
- [ ] Saat halaman `/` dibuka dengan `#tab` yang sudah punya URL, `replaceState`/`location.replace` ke URL baru. Bookmark dan link chat/notifikasi lama tetap bekerja.
- [ ] `localStorage('ppp_active_tab')`: pulihkan ke URL baru (bukan hash) untuk tab yang sudah dimigrasi.
- [ ] `switchTab(tab)`: bila `tab` ada di pemetaan, `location.assign(url)`; bila tidak (tab tersembunyi), pakai perilaku hash lama.

### Fase 3: Pembersihan (setelah semua batch hijau)
- [ ] Hapus cabang hash di `switchTab` untuk tab yang sudah dimigrasi; sisakan hanya jalur tab tersembunyi.
- [ ] Hapus `_hiddenTabs` workaround untuk tab yang kini punya URL.
- [ ] Hapus include menu ganda dan sisa `v-show="activeTab === ..."` yang tidak lagi perlu (hati-hati: test `MarketingDashboardShellTest` memeriksa string ini).
- [ ] Hapus `$legacyMenus`, daftar manual di `return` block, dan `app-script-domain-state*` yang sudah kosong.
- [ ] Update `docs/one-menu-one-blade-roadmap.md` (centang fase yang selesai) dan `docs/component-catalog.md` bila ada pola baru.

### Fase 4 (opsional): Tab tersembunyi
Kerjakan hanya bila menunya diaktifkan lagi: Performa, Intelijen Pasar, Top/Low Konten, Insight & Tren, Proses Claim, Profile. Gunakan resep bagian 2.

## 4. Usulan URL (perlu persetujuan)

| Tab | URL |
|---|---|
| `harga_kompetitor` | `/tools/harga-kompetitor` |
| `laporan_event` | `/tools/laporan-event` |
| `settings` | `/settings` |
| `nama_stock` | `/settings/nama-stock` |
| `auth_users` | `/settings/users` |
| `activity_logs` | `/settings/activity-logs` |
| `orderan_online` | `/cs/order-online` |
| `unit_ditanya` | `/unit_ditanya` sekarang; target `/cs/unit-ditanya` + redirect (langkah terpisah, akhir Batch B) |
| `service` | `/cs/service` |
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

Nama mengikuti `one-menu-one-blade-roadmap.md` bila berbeda; satu sumber kebenaran harus dipilih di Fase 0.

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
| Tab tersimpan di `localStorage` mengarahkan ke menu tersembunyi/usang | Pemetaan Fase 2 + `_hiddenTabs`. |
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
