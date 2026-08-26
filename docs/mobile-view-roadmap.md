# Roadmap Mobile View: Katalog Android, Katalog Apple, Repo Gambar

Lead crew: Developers  
Supporting crews: Design, QA

## Tujuan

Membuat tampilan mobile yang nyaman untuk 3 menu dashboard:

- Katalog Android (`pricelist_katalog`)
- Katalog Apple (`apple_katalog`)
- Repo Gambar (`img_repo`)

## Prinsip UX Mobile

- Layout 1 kolom di mobile, split panel hanya di desktop besar.
- Toolbar/search/filter stack di mobile, horizontal di tablet/desktop.
- Data padat tidak dipaksa jadi tabel di mobile.
- Tombol aksi mudah ditekan, minimal tinggi kontrol mengikuti pattern existing.
- Area scroll jelas dan tidak membuat nested overflow yang membingungkan.
- Preview/modal memakai full-screen atau bottom-sheet friendly di mobile.

## Agent Breakdown

### Agent 1 — Code Locator

- [x] Cari lokasi Blade untuk 3 menu.
- [x] Cari operasi Vue/data-loading terkait.
- [x] Cari route/API terkait.
- [x] Catat file yang perlu diedit.

Output utama:

- `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`
- `resources/views/dashboard/partials/menus/apple-katalog.blade.php`
- `resources/views/dashboard/partials/menus/img-repo.blade.php`
- `resources/css/dashboard-shell.css`

### Agent 2 — Design Pattern Research

- [x] Cari pola responsive existing di dashboard.
- [x] Identifikasi pattern mobile card/table.
- [x] Identifikasi pattern catalog grid.
- [x] Identifikasi pattern mobile sheet/modal.

Pattern yang dipakai:

- Catalog shell: `grid-cols-1` lalu split di `xl`.
- Product grid: `grid-cols-2 sm:grid-cols-3 lg:grid-cols-4`.
- Toolbar: `grid-cols-1` atau `flex-col` mobile, `md:flex-row`/`sm:grid` desktop.
- Modal: `items-end md:items-center`, `mobile-sheet`, `radius-sheet`.

### Agent 3 — QA / Verification

- [x] Cari command build/test/lint resmi.
- [x] Jalankan verifikasi setelah implementasi.

Command verifikasi:

- [x] `npm run build`
- [x] `composer test`
- [x] `php vendor/bin/pint --test`

## Implementation Checklist

### 1. Katalog Android

- [x] Header mobile: judul dan tombol sync stack/friendly.
- [x] Panel data: kurangi `min-h` mobile agar tidak memaksa viewport.
- [x] Toolbar search/filter: tombol view dan filter full-width/rapi di mobile.
- [x] Table view: tambahkan horizontal scroll aman dan minimum width.
- [x] Card view: pastikan kartu tidak overflow di layar kecil.
- [x] Panel template/output: spacing lebih kecil di mobile.
- [x] Preview modal: header/control wrap dan padding mobile friendly.
- [x] Template modal: ubah jadi bottom-sheet/full-height friendly di mobile.

### 2. Katalog Apple

- [x] Header mobile: judul dan tombol sync stack/friendly.
- [x] Product/control panel: mobile vertical, desktop tetap seperti sekarang.
- [x] Table view: pastikan scroll horizontal aman di mobile.
- [x] Card view: grid 2 kolom mobile tetap nyaman.
- [x] Config panel: di mobile jangan terkunci overflow desktop.
- [x] Layout settings: grid input tetap 2 kolom bila cukup, fallback aman di layar kecil.
- [x] Preview modal: control wrap dan padding mobile friendly.
- [x] Color picker: pastikan tidak keluar viewport mobile.

### 3. Repo Gambar

- [x] Shell mobile: ubah layout dari sidebar + content horizontal menjadi vertical.
- [x] Sidebar mobile: jadikan top action bar horizontal/scrollable.
- [x] Tombol Baru mobile: tetap mudah dipakai dan menu dropdown tidak kepotong.
- [x] Topbar mobile: breadcrumb dan action wrap/compact.
- [x] Grid folder/file: ubah jumlah kolom mobile menjadi 2 kolom.
- [x] List view: tambahkan horizontal scroll atau mobile-safe layout.
- [x] Detail panel: ubah menjadi bottom sheet di mobile, side panel tetap di desktop.
- [x] Empty/loading state: kurangi padding vertikal mobile.
- [x] Context menu: pastikan z-index dan posisi aman di mobile.

## File Edit Plan

- [x] Edit `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`
- [x] Edit `resources/views/dashboard/partials/menus/apple-katalog.blade.php`
- [x] Edit `resources/views/dashboard/partials/menus/img-repo.blade.php`
- [x] Edit `resources/css/dashboard-shell.css`

## Acceptance Criteria

- [ ] Manual visual QA di device width 360–430px.
- [x] Tidak ada horizontal overflow global yang terdeteksi dari struktur responsive.
- [x] Semua action utama tetap terlihat di mobile.
- [x] Tabel/list padat bisa discroll tanpa merusak layout.
- [x] Detail/preview/modal bisa ditutup dengan mudah di mobile.
- [x] Build frontend berhasil: `npm run build`.
- [x] Test Laravel berhasil: `composer test` — 298 passed.
- [x] Pint check berhasil: `php vendor/bin/pint --test`.
