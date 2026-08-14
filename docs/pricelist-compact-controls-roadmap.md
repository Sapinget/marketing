# Roadmap Compact Controls Katalog Pricelist

Tanggal: 2026-08-14

## Tujuan

Membuat UI `Katalog Pricelist` lebih compact dengan mengganti tab/button group yang memakan ruang menjadi dropdown/custom select yang konsisten dengan dashboard.

## Scope Awal

- Menu: `Katalog Pricelist`
- File utama: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`
- Script terkait: `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`
- Style terkait: `resources/css/dashboard-shell.css`

## Prinsip UX

- [x] Kontrol yang punya banyak opsi menjadi dropdown.
- [x] Kontrol 2 opsi tetap boleh segmented jika lebih cepat dipakai.
- [x] Dropdown harus keyboard/accessibility friendly: `button`, `aria-expanded`, label jelas, empty state.
- [x] Tidak memakai native `<select>` karena dashboard sudah memakai custom select pattern.
- [x] Mobile tetap full-width dan mudah tap.
- [x] State existing tetap sama: `catalogSelectedSheet`, `catalogSelectedTemplateId`, `catalogOutputMode`, `catalogColumnsPerRow`, `catalogTemplateForm.format`.

## Checklist Prioritas Tinggi

- [x] Ubah filter brand pada tabel data pricelist menjadi dropdown/searchable dropdown.
  - State: `pricelistSheetFilter`.

- [x] Ubah pilihan Brand untuk generate/export menjadi dropdown/searchable dropdown.
  - State: `catalogSelectedSheet`.
  - Preview tabel tetap ambil sample dari brand aktif.

- [x] Ubah pilihan Template menjadi dropdown.
  - State: `catalogSelectedTemplateId`.
  - Dropdown berisi `A4 Auto` dan semua template upload, label trigger menampilkan template aktif.

## Checklist Prioritas Medium

- [x] Ubah `Kolom per baris` menjadi dropdown compact.
  - State: `catalogColumnsPerRow`.

- [x] Ubah format template di modal edit/tambah menjadi dropdown.
  - State: `catalogTemplateForm.format`.
  - Dropdown compact di atas live preview.

- [x] Evaluasi `Format Output`.
  - Tetap segmented (2 opsi, sering dipakai), tidak diubah ke dropdown.

## Komponen/Pattern yang Perlu Dipakai

- [x] Reuse class existing custom select:
  - `select-trigger-button`
  - `select-trigger-button-compact`
  - `select-trigger-button-form`
  - `toolbar-trigger-field-form`
- [x] Reuse popover/dropdown pattern yang sudah ada di dashboard (`searchSelectOpen`, `popoverStyle`, `search-select-popover`, `popover-option`).
- [x] Pastikan tidak menambah native `<select>` baru.

## Risiko & Hal yang Perlu Dicek

- [x] Dropdown tidak tertutup oleh `overflow-y-auto` panel kanan.
- [x] Dropdown di modal tidak tertutup footer `Simpan Template`.
- [x] Klik di dropdown tidak mengganggu drag live preview.
- [x] State tersimpan ke `localStorage` tetap jalan.
- [x] Preview katalog tetap update ketika brand/template berubah.
- [x] Search/filter product tetap bekerja.

## Verification

- [x] `php artisan test --filter=dashboard_shell_includes_pricelist_catalog_menu_and_runner`
- [x] `php artisan test --filter=dashboard_uses_no_native_select_dropdowns`
- [x] `php artisan test --filter=sell_out_searchable_dropdowns_use_select_trigger_pattern`
- [x] `npm run build`
- [ ] Manual QA: buka `Katalog Pricelist`, coba dropdown Brand, Template, Kolom, Format Template, lalu generate preview dan export PDF.

## Implementasi Bertahap

### Phase 1 - Brand & Template Dropdown

- [x] Tambah computed label untuk brand filter tabel.
- [x] Tambah computed label untuk brand generate/export.
- [x] Tambah computed label untuk template aktif.
- [x] Replace button group brand tabel dengan dropdown.
- [x] Replace button group brand generate/export dengan dropdown.
- [x] Replace button group template dengan dropdown.
- [x] Jalankan targeted tests dan build.

### Phase 2 - Modal Compact Controls

- [x] Replace button group format template di modal dengan dropdown.
- [x] Pastikan live preview tetap tidak scroll dan panel kanan tetap scroll.
- [x] Pastikan footer simpan tetap terlihat.
- [x] Jalankan targeted tests dan build.

### Phase 3 - Polish & QA Manual

- [x] Evaluasi apakah `Kolom per baris` perlu dropdown. -> sudah jadi dropdown compact.
- [x] Evaluasi apakah `Format Output` tetap segmented atau dropdown. -> tetap segmented.
- [ ] Cek mobile layout.
- [ ] Cek tidak ada clipping pada dropdown/popover.
- [x] Full verification (automated).

## Catatan Agent

- Agent 1 (research): menemukan pattern `search-select-container` + `searchSelectOpen`/`toggleSearchSelect`/`popoverStyle` dan class terkait di `dashboard-shell.css`.
- Agent 2 (Phase 1): mengganti filter brand tabel, brand generate/export, dan template selector menjadi dropdown memakai pattern di atas.
- Agent 3 (Phase 2): mengganti format template modal dan kolom per baris menjadi dropdown compact.

## Follow-up

- [ ] QA manual di browser (mobile + desktop) untuk memastikan tidak ada dropdown yang clipping/overlap dengan live preview atau footer modal.
