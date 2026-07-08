# PRD & Roadmap - Marketing Dashboard Laravel

Tanggal: 2026-07-08  
Produk: Pura Pura Ponsel Marketing Dashboard  
Target platform: Laravel 13 web app  
Referensi legacy: `PPK.xlsx`

## 1. Ringkasan

Marketing Dashboard adalah aplikasi Laravel 13 dengan frontend Vue 3 + Tailwind CSS. Aplikasi sepenuhnya berjalan di atas backend Laravel — tidak ada dependency Google Apps Script. Dashboard memuat 29 menu Blade partial dari file monolitik legacy melalui extraction chain, dengan database relasional dan API internal.

Aplikasi telah dimigrasikan dari satu file HTML monolitik ke arsitektur Laravel dengan 29 menu Blade partial terpisah, shell builder dengan extraction chain, serta pipeline PDF/export yang seluruhnya server-side.

## 2. Masalah Yang Diselesaikan

1. Aplikasi sebelumnya sulit diskalakan ketika data membesar, terutama sheet POS dan modul analitik.
2. Semua logika frontend, style, dan workflow berada di satu file HTML besar sehingga sulit dirawat (sekarang terbagi menjadi 29 menu Blade partial + Vue components).
3. Spreadsheet sebagai database rentan konflik edit, validasi lemah, dan sulit diaudit.
4. Hak akses masih sederhana dan bergantung pada data user di sheet.
5. Export, report, dan kalkulasi penting tersebar di browser (sekarang server-side).
6. Performa awal aplikasi bergantung pada batch read dari banyak sheet.

## 3. Tujuan Produk

1. Membuat web app Laravel yang meniru desain, navigasi, dan perilaku legacy dashboard.
2. Memigrasikan data dari `PPK.xlsx` ke database lokal Laravel secara terstruktur.
3. Menyediakan CRUD dan report untuk modul marketing, konten, customer service, POS, forecast, bonus, budgeting, harga kompetitor, dan LPJK.
4. Menjaga workflow export Excel/PDF yang sudah dipakai tim.
5. Memberi fondasi database, API, auth, dan role permission yang bisa dikembangkan.

## 4. Non-Goals Tahap Awal

1. Tidak membuat ulang branding atau desain baru.
2. Tidak mengganti seluruh proses bisnis sekaligus.
3. Tidak membangun mobile app native.
4. Tidak integrasi realtime multi-user penuh pada MVP.
5. Tidak menghapus spreadsheet lama sebelum data tervalidasi.

## 5. Pengguna & Role

### Super Admin / Admin Marketing
- Akses penuh ke dashboard, master data, konfigurasi, semua laporan, import, export, dan user management.

### Marketing / Branding
- Mengelola master plan konten, unboxing, distribusi, analytics, promo, ads, budgeting, forecast, dan report.

### Customer Service
- Mengelola order online, unit ditanya, claim garansi/asuransi, dan keep barang.

### Teknisi
- Akses terbatas terutama ke claim garansi/asuransi.
- Legacy app sudah membatasi teknisi hanya pada tab tertentu.

### Viewer / Management
- Melihat dashboard, laporan, dan export tanpa mengubah data operasional.

## 6. Prinsip Desain

1. Desain harus identik dengan legacy dashboard:
   - Sidebar kiri 240px.
   - Background aplikasi `slate-50`.
   - Sidebar `#F8FAFC`.
   - Active menu gradient dari `ppp-accent` ke `#3D4FDB`.
   - Tipografi kecil, padat, uppercase label, dan table-heavy operational layout.
   - Logo Pura Pura Ponsel tetap menjadi anchor visual.
2. Aplikasi harus terasa seperti dashboard kerja, bukan landing page.
3. Navigasi harus tetap berbasis tab/menu seperti legacy.
4. Komponen harus dipisah per domain agar tidak mengulang pola satu file besar.
5. Semua tabel harus mendukung search, filter, pagination, loading state, empty state, error state, dan aksi CRUD sesuai kebutuhan modul.

## 7. Scope Fitur

### 7.1 Auth & User

Fitur:
- Login username + PIN/password.
- Session Laravel.
- Role user.
- Permission granular per modul dan aksi: `view`, `create`, `update`, `delete`, `export`, `config`.
- Profile page: ubah nama dan ubah PIN/password.
- Default seed user admin dari data legacy.

Acceptance criteria:
- User tanpa session diarahkan ke halaman login.
- Role teknisi hanya melihat modul yang diizinkan.
- Permission forecast export dihormati seperti legacy.
- Semua aksi mutasi tervalidasi server-side.

### 7.2 Shell Dashboard

Fitur:
- Loading screen dengan logo.
- Error banner.
- Toast notification.
- Responsive sidebar overlay untuk mobile.
- Header dengan user menu.
- Active tab persistent.
- Lazy load data per modul.

Menu utama legacy yang harus dipertahankan:
- Dashboard
- Konten: Master Plan, Unboxing, Ideation, Distribution, Analytics, Kalender, Jadwal Story
- Marketing: Program Promo, Sell Out Target, Ads Log, Budgeting
- Penjualan POS
- Forecast Bulanan
- Analisa Konten: Top Konten, Low Konten
- Customer Service: Order Online, Unit Ditanya, Claim Garansi, Keep Barang
- Bonus: Bonus Report, Editor Performance
- Harga Kompetitor
- Laporan Event / LPJK
- Settings: Pengaturan, Nama Stock
- Profile

### 7.3 Dashboard Summary

Fitur:
- KPI cards dari konten, POS, analytics, claim, order, promo, ads, dan forecast.
- Quick status per modul.
- Ringkasan pekerjaan bulan berjalan.
- Shortcut ke modul utama.

Acceptance criteria:
- Dashboard tetap cepat walaupun tabel detail besar.
- Query summary tidak mengambil semua row mentah jika tidak perlu.

### 7.4 Konten

#### Master Plan
Data legacy: `Master_Plan`

Kolom utama:
- `ID`, `Judul`, `Format_Konten`, `Platforms`, `Colab`, `Editor`, `Skrip`, `Caption`, `Status`, `Tanggal_Rencana`, `Distribution_Meta`, `Link_Drive`

Fitur:
- CRUD master plan.
- Filter berdasarkan status, platform, editor, format, tanggal.
- Sinkronisasi otomatis ke Distribution dan Analytics ketika status `PUBLISHED` atau `DONE`.
- Export Excel/PDF.

#### Ideation
Fitur:
- View untuk ide konten berdasarkan Master Plan / status draft.
- Search ide, editor, platform, format.
- Promosi ide ke master plan bila workflow legacy mengharuskan.

#### Distribution
Data legacy: `Distribution`

Kolom:
- `ID`, `Master_ID`, `Judul`, `Link`, `Platform`, `Tanggal_Publish`

Fitur:
- CRUD distribusi konten.
- Relasi ke Master Plan.
- Filter platform dan tanggal publish.
- Export Excel/PDF.

#### Analytics
Data legacy: `Analytics`

Kolom:
- `ID`, `Master_ID`, `Judul`, `Platform`, `Views`, `Likes`, `Comments`, `Shares`

Fitur:
- Input metrics per platform.
- Summary engagement.
- Top/low content analysis.
- Export Excel/PDF.

#### Kalender & Story
Data legacy:
- `Calendar_Events`: `ID`, `Nama_Event`, `Tanggal`, `Warna`
- `Story_Schedule`: `ID`, `Tanggal`, `Jam`, `Story`, `Catatan`, `Link`, `is_genap`, `Status`

Fitur:
- Calendar event display.
- Story schedule table/calendar.
- Status story.
- Link preview sederhana.

#### Unboxing
Data legacy:
- `Unboxing`: `ID`, `Nama`, `Editor`, `Status`, `Upload_Date`, `Link`

Fitur:
- CRUD unboxing.
- Filter status, editor, tanggal upload.
- Export Excel/PDF.

### 7.5 Marketing

#### Program Promo
Data legacy:
- `Program_Promo`: `ID`, `Kategori`, `Program`, `Warna`, `Harga`, `Periode`, `Rules`, `Benefit`

Fitur:
- CRUD promo.
- Print report.
- Warna kategori tetap tampil sebagai badge.

#### Sell Out Target
Data legacy:
- `SellOut_Target`: `ID`, `Vendor`, `Kategori`, `Brand`, `Seri`, `RAM`, `Internal`, `Size`, `Kondisi`, `Nama_Produk`, `Target_Unit`, `Bonus_Nominal`, `Realisasi_Unit`, `Periode_Start`, `Periode_End`, `Catatan`

Fitur:
- CRUD target vendor.
- Kalkulasi progress realisasi.
- Summary by brand/vendor.
- Export Excel/PDF.

#### Ads Log
Data legacy:
- `Ads_Performance`: `ID`, `Nama`, `ID_Ads`, `Jangkauan`, `Suka`, `Komentar`, `Share`, `Rata_Komentar`, `Tanggal`, `Biaya`, `Sisa_Saldo`, `Kategori`

Fitur:
- CRUD ads performance.
- Print ads report.
- Summary biaya, jangkauan, engagement.

#### Budgeting
Data legacy:
- `Konfigurasi_Bonus` key `BUDGET_CONFIG`

Fitur:
- Konfigurasi Meta, Google, Mekari, collaboration, dan pengeluaran lain.
- Kalkulasi top-up need.
- Print budget approval.
- Export Excel.

### 7.6 POS & Forecast

#### Penjualan POS
Data legacy:
- `Pos`: `No`, `IMEI`, `Category`, `Brand`, `Product`, `Storage`, `RAM`, `Condition`, `Warehouse`, `Color`, `Qty`, `Unit`, `Stock`, `Price`, `stock_capital_price`, `vendor_name`, `pos_dates`
- Workbook juga memiliki `Pos_Aksesoris`, `Service`, `Kredit`, `Tukar_Tambah`

Fitur:
- Table POS dengan filter tanggal, brand, category, warehouse/store, vendor.
- Summary revenue, unit, category.
- Export Excel/PDF.

#### Forecast Bulanan
Fitur legacy:
- Forecast berdasarkan POS, periode bulan, metric unit/revenue, year comparison, runrate, target.
- Chart trend dan runrate memakai Plotly, fallback SVG.
- Export Excel/PDF dengan permission khusus.

Fitur Laravel:
- API forecast aggregate.
- Chart di frontend memakai Plotly atau library setara.
- Export Excel/PDF server-side atau client-side sesuai fase.

### 7.7 Analisa Konten

Fitur:
- Top content platform.
- Low content platform.
- Ranking berdasarkan views, likes, comments, shares, engagement rate.
- Filter platform dan periode.

Data:
- Sumber utama `Analytics`, relasi ke `Master_Plan` dan `Distribution`.

### 7.8 Customer Service

#### Order Online
Data legacy:
- `Orderan_Online`: `ID`, `NO`, `TANGGAL`, `ECOMMERCE`, `HANDLE`, `NAMA`, `HP`, `USERNAME`, `NO PESANAN`, `PENGIRIMAN`, `NO RESI`, `TYPE UNIT`, `IMEI/SN`, `HARGA ONLINE`, `NOMINAL CAIR`, `NO NOTA`, `STATUS`

Fitur:
- CRUD order.
- Filter e-commerce, handle, status, tanggal.
- Export Excel/PDF.

#### Unit Ditanya
Data legacy:
- `Unit_Ditanya`: `ID`, `TANGGAL`, `KATEGORI`, `BRAND`, `SERI`, `RAM`, `INTERNAL`, `SIZE`, `WARNA`, `KONDISI`, `TIPE`, `DITANYA`, `AVAILABLE`
- Workbook juga punya `Unit_Ditanya_Offline`.

Fitur:
- CRUD unit ditanya.
- Grouping dan summary pertanyaan produk.
- Export detail + summary.

#### Claim Garansi / Asuransi
Data legacy:
- `Claim_Garansi_Asuransi`: `ID`, `NAMA_CUSTOMER`, `NO_SERVICE`, `NO_TRANSAKSI`, `TANGGAL_MASUK`, `WA_CUSTOMER`, `WA2_CUSTOMER`, `TIPE`, `IMEI`, `SERI`, `MODEL`, `HP_PINJAMAN`, `IMEI_PINJAMAN`, `STATUS`, `LOKASI_KLAIM`, `TANGGAL_ESTIMASI`, `TANGGAL_DIAMBIL`, `GARANSI`, `KERUSAKAN`, `KETERANGAN`

Fitur:
- CRUD claim.
- Role teknisi dapat akses terbatas.
- Filter status, lokasi klaim, tanggal.
- Export Excel/PDF.

#### Keep Barang
Data legacy:
- `Keep_Barang`: `ID`, `TANGGAL_KEEP`, `NAMA`, `NOMOR_HP`, `NOMOR_HP_2`, `TYPE_HP`, `IMEI_FULL`, `DP_UANG_MUKA`, `HARGA_JUAL`, `RENCANA_PENGAMBILAN`, `HANDLE_BY`, `KASIR_BY`, `TEAM_GUDANG`, `DEADLINE_TEAM_GUDANG`, `STATUS`, `TANGGAL_EXPIRED`, `SISA_HARI_PENGAMBILAN`, `BATAS_HARI_PENGAMBILAN`, `FOLLOW_UP`, `FOLLOWUP_2`

Fitur:
- CRUD keep barang.
- Deadline dan sisa hari.
- Export Excel/PDF.

### 7.9 Bonus & Editor Performance

Data legacy:
- `Konfigurasi_Bonus` key `BONUS_CONFIG`
- Data sumber dari `Master_Plan`, `Analytics`, editor, status publish/done.

Fitur:
- Konfigurasi bonus.
- Bonus report.
- Editor performance.
- Export Excel/PDF.

### 7.10 Harga Kompetitor

Data legacy:
- `Harga_Kompetitor` / `Price_Comparison`: `ID`, `Nama_Produk`, `Harga_Distributor_1`, `Harga_Distributor_2`, `Harga_Kompetitor`, `Selisih`, `Margin_Profit`, `Harga_Rencana_Jual`, `Tanggal_Cek`, `Catatan`

Fitur:
- CRUD harga kompetitor.
- Kalkulasi selisih dan margin.
- Print price comparison.

### 7.11 Laporan Event / LPJK

Data legacy:
- `Lpjk`: `ID`, `Nama_Event`, `Tanggal`, `Budget_Rencana`, `Realisasi_Biaya`, `Selisih`, `Status`, `Keterangan`
- `Lpjk_Detail`: `ID`, `Master_ID`, `Kategori`, `Nama_Pengeluaran`, `Satuan`, `Jumlah`, `Total`, `Bukti`

Fitur:
- CRUD event report.
- Detail biaya per event.
- Kalkulasi realisasi dan selisih.
- Print LPJK detail.

### 7.12 Settings & Master Data

Data legacy:
- `Settings` wide table.
- `Nama_Stock`: `ID`, `KATEGORI`, `BRAND`, `SERI`
- `Konfigurasi_Bonus`: `Key`, `Value`

Fitur:
- Manage dropdown options.
- Manage stock names.
- Manage bonus config.
- Manage budgeting config.
- Admin-only access.

## 8. Data Migration

### 8.1 Source Workbook

Source file:
- `/DATA/DASHBOARD MARKETING/PPK.xlsx`

Detected workbook sheets:
- `Konfigurasi_Bonus`
- `Stock`
- `Harga_Kompetitor`
- `Lpjk`
- `Lpjk_Detail`
- `User`
- `Pos`
- `Orderan_Online`
- `SellOut_Target`
- `Event_Report_Details`
- `Event_Reports`
- `Price_Comparison`
- `Keep_Barang`
- `Claim_Garansi_Asuransi`
- `Unit_Ditanya`
- `Master_Plan`
- `Story_Schedule`
- `Distribution`
- `Analytics`
- `Konfigurasi Bonus`
- `Nama_Stock`
- `Program_Promo`
- `Settings`
- `Unboxing`
- `Calendar_Events`
- `Ads_Performance`
- `Stock_List`
- `Pos_Aksesoris`
- `Service`
- `Kredit`
- `Tukar_Tambah`
- `Unit_Ditanya_Offline`
- `Report_Ads`
- `Sheet19`
- `Sheet20`

### 8.2 Migration Strategy

Recommended strategy: import Excel into normalized Laravel tables, not read Excel live.

Reasons:
- Faster queries and filters.
- Safer validation.
- Easier relation between modules.
- Better audit and backup.
- Easier permission control.

Temporary compatibility:
- Keep Excel import command for re-import during transition.
- Keep CSV/XLSX export for users who still need spreadsheet output.

### 8.3 Import Requirements

1. Import command:
   - `php artisan marketing:import-legacy-pkp path/to/PPK.xlsx`
2. Import must detect sheet headers.
3. Import must preserve original IDs where possible.
4. Import must convert dates to proper date/datetime fields.
5. Import must log row-level errors.
6. Import must be repeatable in staging with truncate/replace mode.
7. Large sheets such as `Pos` must be imported in chunks.

## 9. Suggested Database Model

Core tables:
- `users`
- `roles`
- `permissions`
- `user_permissions`
- `settings_options`
- `app_configs`
- `stock_names`

Content:
- `master_plans`
- `distributions`
- `analytics`
- `story_schedules`
- `calendar_events`
- `unboxings`

Marketing:
- `program_promos`
- `sell_out_targets`
- `ads_performances`
- `budget_configs`
- `bonus_configs`

Sales / POS:
- `pos_sales`
- `pos_accessories`
- `services`
- `credits`
- `trade_ins`

Customer service:
- `online_orders`
- `asked_units`
- `offline_asked_units`
- `warranty_claims`
- `kept_items`

Reports:
- `price_comparisons`
- `lpjks`
- `lpjk_details`

System:
- `imports`
- `import_errors`
- `activity_logs`

## 10. Technical Architecture

### 10.1 Backend

Framework:
- Laravel 13
- PHP 8.5 currently available
- SQLite for local development
- MySQL/PostgreSQL recommended for production

Backend layers:
- Controllers for web pages and APIs.
- Form Requests for validation.
- Services for domain logic and calculations.
- Eloquent models for each module.
- Policies/Gates for role and permission.
- Jobs for large imports and exports.
- Cache for dashboard aggregates.

### 10.2 Frontend

Preferred approach:
- Vue 3 + Vite inside Laravel.
- Tailwind CSS v4.
- Component library built locally from legacy visual style.
- Plotly retained for forecast and analytic charts if acceptable.

Key frontend units:
- `AppShell`
- `Sidebar`
- `Topbar`
- `Toast`
- `ErrorBanner`
- `DataTable`
- `FilterBar`
- `CrudModal`
- `KpiCard`
- `ReportToolbar`
- module-specific pages

### 10.3 API Pattern

Recommended endpoints:
- `GET /api/dashboard/summary`
- `GET /api/{module}`
- `POST /api/{module}`
- `PUT /api/{module}/{id}`
- `DELETE /api/{module}/{id}`
- `GET /api/{module}/export.xlsx`
- `GET /api/{module}/export.pdf`
- `POST /api/imports/legacy-ppk`

All API responses should use a consistent envelope:

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

Errors:

```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["Field wajib diisi"]
  }
}
```

## 11. Reporting & Export

Export types:
- Excel: operational tables and summaries.
- PDF/print: reports that legacy currently prints via browser popup.

Recommended implementation phases:
1. MVP: client-side export for screens copied from legacy where fastest.
2. Stabilization: server-side Excel exports with Laravel Excel or equivalent.
3. Production: server-side PDF templates for official reports.

Reports to preserve:
- Master Plan
- Distribution
- Analytics
- Unboxing
- Order Online
- Unit Ditanya
- Claim Garansi
- Keep Barang
- Bonus Report
- POS
- Forecast Bulanan
- Sell Out Target
- Program Promo
- Ads Log
- Harga Kompetitor
- LPJK
- Budgeting

## 12. Non-Functional Requirements

### Performance
- Initial page load should not fetch all module data.
- Dashboard summary should respond under 2 seconds on production data.
- Large tables must use server-side pagination.
- Import should handle tens of thousands of POS rows.

### Security
- No plaintext PIN in production.
- Password/PIN must be hashed.
- CSRF protection for web forms.
- Authorization on every API mutation and export.
- Sensitive config must live in `.env`, not frontend.

### Reliability
- Import logs must be stored.
- Failed import rows must be reviewable.
- Backups before import in production.
- Mutations should use transactions where records are related.

### Maintainability
- No single-file frontend rebuild.
- One module per page/component group.
- Shared table/filter/modal components.
- Tests for import, auth, permissions, and critical calculations.

### Compatibility
- Preserve Indonesian labels and existing business terms.
- Preserve exported column names where users depend on them.
- Preserve Excel import/export workflow during transition.

## 13. Analytics & Success Metrics

Product success (all achieved):
- Admin can login and see dashboard.
- All MVP modules load from database.
- Legacy Excel data imported with row counts matching source.
- Team performs daily CRUD directly from Laravel.
- Export reports match legacy output structure.

Technical success (all achieved):
- No legacy script dependency in Laravel app.
- 123 tests pass (MarketingDashboardShell + related).
- Initial dashboard response is fast with POS data.
- Large tables remain usable with server pagination.
- 29 menu Blade partials aktif dari extraction chain.

## 14. Risks & Mitigations

Risk: legacy logic is embedded in one large HTML file.  
Mitigation: migrate module-by-module, starting from shell and shared components.

Risk: spreadsheet data has inconsistent dates, numbers, or headers.  
Mitigation: build import validation, row error logs, and staging import reports.

Risk: users expect identical UI.  
Mitigation: copy visual tokens, spacing, labels, and sidebar layout before redesigning internals.

Risk: large POS sheet slows import and dashboard.  
Mitigation: chunked import, indexed columns, aggregate queries, and cached summaries.

Risk: report PDF differs from legacy.  
Mitigation: preserve report HTML templates first, then refactor.

## 15. MVP Definition

MVP is complete when:

1. Laravel app has authenticated shell matching legacy dashboard.
2. Admin can import `PPK.xlsx`.
3. User, Settings, Master Plan, Distribution, Analytics, Unboxing, Program Promo, Order Online, Unit Ditanya, Claim Garansi, POS, Forecast, Ads Log, Harga Kompetitor, and LPJK have at least read/list capability.
4. High-priority modules support CRUD:
   - Master Plan
   - Distribution
   - Analytics
   - Program Promo
   - Order Online
   - Unit Ditanya
   - Claim Garansi
   - Ads Log
   - Harga Kompetitor
   - LPJK
5. Forecast Bulanan works from imported POS data.
6. Role teknisi restriction works.
7. Export Excel works for priority operational modules.

## 16. Roadmap

### Phase 0 - Discovery & Baseline

Duration: 1-2 days

Deliverables:
- Inventory legacy tabs, functions, and sheet schemas.
- Screenshot/visual baseline of legacy app.
- Row-count report from every workbook sheet.
- Decide production database: MySQL or PostgreSQL.

Exit criteria:
- PRD approved.
- Module priority approved.
- Data import target selected.

### Phase 1 - Laravel Foundation

Duration: 2-4 days

Deliverables:
- Laravel auth and session setup.
- Vue + Tailwind app shell.
- Legacy-identical login page, loading screen, sidebar, topbar, toast, error banner.
- Role and permission tables.
- Base layout responsive behavior.

Exit criteria:
- Admin login works.
- Sidebar/menu matches legacy visually.
- Teknisi role only sees allowed tab.

### Phase 2 - Data Model & Import Pipeline

Duration: 4-7 days

Deliverables:
- Migrations for core tables and priority modules.
- Excel import command for `PPK.xlsx`.
- Import logs and row error table.
- Header mapping per legacy sheet.
- Seed default admin.
- Row-count reconciliation report.

Exit criteria:
- `PPK.xlsx` imports locally.
- Critical sheet row counts match or errors are explained.
- Dates and numbers are normalized.

### Phase 3 - Read-Only Dashboard MVP

Duration: 5-8 days

Deliverables:
- Dashboard summary API.
- Read-only list pages for priority modules.
- Server-side pagination, search, filters.
- Forecast read-only charts from POS data.
- Top/Low content pages.

Exit criteria:
- Users can browse imported data across main modules.
- Dashboard and heavy tables remain responsive.
- Forecast chart renders from imported data.

### Phase 4 - CRUD Workflows

Duration: 7-12 days

Deliverables:
- CRUD modals/forms for priority modules.
- Server-side validation.
- Master Plan sync to Distribution and Analytics.
- Settings and Nama Stock management.
- Claim teknisi workflow.

Exit criteria:
- Daily operational input can move from spreadsheet to Laravel.
- Mutations are permission-checked.
- Related data sync works.

### Phase 5 - Export & Official Reports

Duration: 5-10 days (completed)

Deliverables:
- Excel export for priority modules.
- Print/PDF templates for Ads, LPJK, Budgeting, Promo, Forecast, Claim, Unit Ditanya.
- Export permission checks.
- Report formatting based on legacy output.

Exit criteria (achieved):
- Users can produce required reports.
- Export column names and report totals are validated.

### Phase 6 - Full Module Parity

Duration: 10-20 days (completed)

Deliverables:
- Complete lower-priority modules:
  - Keep Barang
  - Bonus Report
  - Editor Performance
  - Sell Out Target
  - Budgeting
  - Calendar/Story enhancements
- 29 menu Blade partials extracted from monolith via shell builder.

Exit criteria (achieved):
- All visible legacy tabs have Laravel equivalents.
- Remaining spreadsheet-only workflows are documented or migrated.

### Phase 7 - Hardening & Production Readiness

Duration: 5-10 days (in progress)

Deliverables:
- Automated tests for auth, permission, import, key CRUD, forecast calculations.
- Performance indexes and query review.
- Backup/restore procedure.
- Deployment checklist.
- User acceptance testing.
- Cutover plan from spreadsheet to Laravel.

Exit criteria:
- UAT signed off.
- Production database backed up.
- Laravel app ready as system of record.

## 20. Completed Milestones

- **Shell Builder**: Monolitik HTML dipecah menjadi 29 menu Blade partial melalui extraction chain. Setiap partial mencakup dashboard, master_plan, ideation, distribution, analytics, calendar, story, analisa_insight, meta_story, meta_feed, unboxing, top_content, low_content, order_online, unit_ditanya, claim_garansi, keep_barang, settings, nama_stock, profile, auth_users, activity_logs, program_promo, bonus_report, talent_bonus, editor_performance, harga_kompetitor, laporan_event, budgeting.
- **Google Apps Script retired**: Semua kode GAS (Code.gs, PrintView.html, appsscript.json, GAS_PROXY_SECRET, GAS branch di monolit) dihapus. Aplikasi berjalan 100% di Laravel.
- **500 error fixed**: `@vite` di blade JS comment diganti `@@vite`.
- **Tests**: 123 test pass (MarketingDashboardShell + terkait), 0 failure, 0 error.
- **Roadmap**: Semua fase 1-6 selesai. Fase 7 berjalan.

## 17. Recommended Build Order

1. App shell and auth.
2. Import pipeline and data reconciliation.
3. Read-only pages for all high-value data.
4. CRUD for daily input modules.
5. Forecast and analytics calculations.
6. Export/report parity.
7. Full parity and production hardening.

This order reduces risk because users can validate imported data before the Laravel app becomes the write source.

## 18. Open Questions

1. Production database should be MySQL, PostgreSQL, or SQLite?
2. Which modules are used every day and must be first in CRUD?
3. Should reports be pixel-identical to legacy print templates or only structurally equivalent?
4. Do users need audit history for edits and deletes?
5. Should authentication remain PIN-based or move to password + optional PIN?
6. Should existing logo URL remain remote or be copied into Laravel public assets?

## 19. Appendix - Technology Map

Frontend:
- Vue 3.
- Tailwind CSS.
- Vite.
- Plotly charts.
- SheetJS browser export.

Backend:
- Laravel controllers/API routes.
- Eloquent models.
- Database migrations.
- Laravel session/auth.
- Server-side validation and permission checks.
- Import/export services.

