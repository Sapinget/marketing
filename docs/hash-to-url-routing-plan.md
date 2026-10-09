# Plan: Migrasi Menu Hash (`#tab`) ke URL Sendiri

Status: draft, belum dikerjakan. Dibuat 2026-10-08.
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

### Menu yang masih hash dan tampil di sidebar (26 tab)

| Batch | Grup | Tab |
|---|---|---|
| A | Tools & Settings | `harga_kompetitor`, `laporan_event`, `settings`, `nama_stock`, `auth_users`, `activity_logs` |
| B | Customer Service | `orderan_online`, `unit_ditanya` (route ada, sidebar belum link), `service`, `claim_garansi_asuransi`, `keep_barang` |
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
   Route::get('/<grup>/<menu>', function (MarketingDashboardShell $dashboardShell) {
       return response()->view('dashboard.pages.<grup>.<menu>', array_merge(
           $dashboardShell->build(rtrim(url('/'), '/')),
           ['activeTab' => '<tab_key>', 'dedicatedMenuView' => 'dashboard.partials.menus.<menu>']
       ))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
   })->name('dashboard.<grup>.<menu>');
   ```
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

### Fase 0: Persiapan (1 PR)
- [ ] Tetapkan konvensi URL (tabel di bagian 4) dan minta persetujuan sebelum batch A.
- [ ] Buat helper agar route tidak ditulis berulang: closure `$dashboardPage(string $view, string $tab, string $menuView)` di `routes/web.php`, dipakai route baru dan bisa dipakai ulang oleh route lama.
- [ ] Tambah test generik: untuk semua route halaman, `GET` mengembalikan 200 dan memuat `activeTab === '<tab>'`.
- [ ] Batasan role Teknisi dihapus dari lingkup: route baru tidak membedakan Teknisi, dan `TEKNISI_TABS` tidak dijaga di sisi server. Guard `v-if` lain (mis. `canManageSettings`) tetap seperti sekarang.

### Fase 1: Migrasi per batch (satu PR per batch, urutan A → F)
Urutan dari risiko terendah ke tertinggi.
- [ ] **Batch A** Tools & Settings (6 menu). Mayoritas CRUD sederhana, `activity_logs`/`auth_users`/`settings` punya guard role sendiri.
- [ ] **Batch B** Customer Service (5). `unit_ditanya` hanya perlu link sidebar.
- [ ] **Batch C** Complain Tracker (3).
- [ ] **Batch D** Marketing (4). `program_promo` terkait route publik `/promo`, jangan bentrok.
- [ ] **Batch E** Analisa Konten (3). Bergantung pada importer Meta (`meta_story`, `meta_feed`, `meta_followers`).
- [ ] **Batch F** Dashboard & Konten (8). Paling berat: `dashboard` dan `master` memuat banyak state bersama (`app-script-summary-computed-cluster`, `master-content-operations`). Tab `dashboard` dipakai sebagai default `/`, jadi `/` harus tetap menampilkannya (jangan redirect).

### Fase 1.5: Isolasi per menu (inti "1 menu 1 blade")

Dikerjakan **setelah** tiap batch Fase 1 hijau (per menu, bukan sekaligus), kecuali langkah fondasi yang dikerjakan sekali di awal.

Fondasi (sekali, 1-2 PR, sebelum batch A selesai dipakai):
- [ ] Tambah `@stack('menu-scripts')` ke `partials/shell/body-app-assembly.blade.php` tepat sebelum `app-script-return-block` (script menu disisipkan di dalam `setup()` yang sama).
- [ ] Mekanisme state per menu: tiap menu mengekspor state/fungsinya lewat satu objek (mis. `menuExports.<tab> = { ... }`) yang digabung otomatis ke `return`, menggantikan daftar manual di `app-script-return-block.blade.php`. Tanpa ini setiap menu yang dipisah tetap harus mengedit file 1122 baris itu.
- [ ] `app-frame.blade.php`: ganti daftar `@include('...menus.*')` menjadi: include hanya `$dedicatedMenuView`; sisanya hanya untuk tab yang belum dimigrasi (daftar eksplisit `$legacyMenus`, menyusut tiap batch). Halaman `/` (tab `dashboard`) memuat dirinya sendiri.
- [ ] Pisahkan script **bersama** (dipakai banyak menu: `date-helpers`, `search-select-and-options`, `calendar-helpers`, `summary-computed-cluster`, `auth-session`, `chat-state`, `notification-error-utils`, `shell-interaction-helpers`) dari script **milik satu menu**. Yang bersama tetap dimuat di semua halaman; yang milik satu menu pindah ke halaman menu itu.
- [ ] Petakan ketergantungan silang: fungsi/state menu A yang dipakai menu B (mis. `master-content-operations` dipakai `ideation`, `top_content_platform`, `distribution`). Catat di tabel pemetaan (lihat bawah) sebelum memindahkan.

Per menu (mengikuti batch Fase 1):
- [ ] Pindahkan `app-script-<menu>-operations.blade.php` ke `@push('menu-scripts')` di page blade menu, bukan di `body-app-assembly`.
- [ ] Hapus state/fungsi menu dari `return` block bersama dan dari `app-script-domain-state*.blade.php`; pindahkan ke script menu.
- [ ] Hapus `@include` menu dari daftar `$legacyMenus` di `app-frame`.
- [ ] Pindahkan blok `if (tab === '<tab>')` dan `tabDataKey` menu itu dari `runner-session-tail`/`switchTab` ke script menu (dipanggil saat `onMounted`).
- [ ] Verifikasi: `view-source` halaman menu tidak memuat markup/script menu lain; error console bersih; ukuran HTML turun.

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
| `unit_ditanya` | `/unit_ditanya` (sudah ada; samakan ke `/cs/unit-ditanya` + redirect) |
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

Rekomendasi: Fase 0, lalu fondasi Fase 1.5, lalu Batch A (URL + isolasi sekaligus) sebagai percontohan. Nilai hasilnya (ukuran HTML, waktu muat, jumlah file yang diubah per menu) sebelum lanjut ke batch lain. Bila fondasi 1.5 terlalu mahal, alternatif: selesaikan Fase 1 saja (URL) lebih dulu dan jadwalkan 1.5 terpisah; konsekuensinya halaman tetap berat.
