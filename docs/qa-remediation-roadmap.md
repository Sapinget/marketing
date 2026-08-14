# Roadmap Perbaikan QA Marketing Dashboard

Tanggal QA: 2026-08-14

## Status Automated Check

- [x] `npm run build` sukses.
- [x] `composer test` sukses: 289 passed, 7494 assertions.
- [x] `php vendor/bin/pint --test` sukses.

## Checklist Prioritas Tinggi

- [x] Perbaiki marker Vue app script yang hilang dari root HTML.
  - Test terkait: `root_keeps_runner_export_and_mount_clusters_inside_main_app_script_in_order`
  - Test terkait: `dashboard_shell_uses_blade_vue_app_script_wrapper_partials_instead_of_raw_legacy_script_wrapper`
  - File diperbaiki: `resources/views/dashboard/partials/shell/app-script-open.blade.php`
  - File terkait: `resources/views/dashboard/partials/shell/body-app-assembly.blade.php`
  - Expected marker dipulihkan: `const { createApp, ref, computed, watch, onMounted, onBeforeUnmount, nextTick } = Vue;`

- [x] Perbaiki tombol aksi tabel agar tap-safe dan aksesibel.
  - Test terkait: `table_row_actions_use_labeled_tap_safe_buttons`
  - Pola lama `>Hapus</span>` sudah tidak muncul.
  - Tombol delete tetap memakai `aria-label="Hapus"`.

- [x] Batasi semua summary cards maksimal 5 item.
  - Test terkait: `summary_cards_are_limited_to_five_items`
  - File diperbaiki: `resources/views/dashboard/partials/menus/garansi-cermati.blade.php`
  - Summary cards sekarang memakai `.slice(0, 5)`.

## Checklist Prioritas Medium

- [x] Bersihkan karakter Unicode dekoratif dari source dashboard.
  - Test terkait: `dashboard_source_files_avoid_decorative_unicode_and_external_logo_assets`
  - File diperbaiki: `routes/web.php`
  - Karakter `─`, `═`, `■` sudah dibersihkan dari source yang diuji.

- [x] Standarkan class header tabel menu.
  - Test terkait: `menu_table_headers_use_shared_classes`
  - File diperbaiki: `resources/views/dashboard/partials/menus/apple-katalog.blade.php`
  - Setiap `<thead>` sudah memakai class `table-header-row`.
  - Setiap `<th>` sudah diawali class `table-header-cell`.

- [x] Pulihkan color picker pricelist catalog.
  - Test terkait: `dashboard_shell_includes_pricelist_catalog_menu_and_runner`
  - HTML shell sudah mengandung `type="color"`.
  - Binding berikut tersedia:
    - `catalogTemplateForm.layout_config[colorField.key]`
    - `catalogApplyColor(colorField.key, $event)`
    - `const catalogColorFields = [`

## Checklist Code Style

- [x] Jalankan auto-format: `php vendor/bin/pint`.
- [x] Jalankan ulang `php vendor/bin/pint --test` sampai hijau.
- [x] Review diff Pint sebelum commit.

File yang sudah diformat Pint:

- [x] `database/migrations/2026_07_07_000001_add_username_to_users_table.php`
- [x] `bootstrap/app.php`
- [x] `bootstrap/dev/CollisionConfigureIoPatch.php`
- [x] `app/Models/User.php`
- [x] `app/Exceptions/Handler.php`
- [x] `app/Support/DashboardAuth.php`
- [x] `app/Support/PricelistSheetImporter.php`
- [x] `app/Support/AppleSheetImporter.php`
- [x] `app/Support/XlsxSheetReader.php`
- [x] `app/Http/Middleware/ApplyPublicPathPrefix.php`
- [x] `app/Http/Kernel.php`
- [x] `config/app.php`
- [x] `config/database.php`
- [x] `tests/Unit/MetaIgImportNormalizerTest.php`
- [x] `tests/Feature/DashboardAuthenticationTest.php`
- [x] `tests/Feature/SettingsImportTest.php`
- [x] `tests/Feature/DashboardUserManagementTest.php`
- [x] `tests/Feature/RemainingWorkbookImportTest.php`
- [x] `tests/Feature/ChatMessageTest.php`
- [x] `tests/Feature/MasterPlanImportTest.php`
- [x] `tests/Feature/MarketingDashboardShellTest.php`
- [x] `tests/TestCase.php`
- [x] `routes/web.php`

## Final Verification

- [x] Jalankan `composer test`.
- [x] Jalankan `php vendor/bin/pint --test`.
- [x] Jalankan `npm run build`.
- [x] Buka manual `/#laporan_event` dan pastikan tidak ada error CSP font.
- [x] QA manual menu utama: login/session, navigasi sidebar, tabel CRUD, export PDF/Excel, dan upload catalog background.
- [x] QA manual color picker pricelist.

## Catatan Agent

- Agent 1 memperbaiki marker script Vue dan validasi urutan mount.
- Agent 2 memperbaiki UI partials: tombol aksi, summary limit, header tabel.
- Agent 3 memulihkan color picker pricelist catalog.
- Agent 4 membersihkan Unicode dekoratif di `routes/web.php`.
