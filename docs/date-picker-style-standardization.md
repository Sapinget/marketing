# Date Picker Style Standardization

## Tujuan

Samakan seluruh date picker / select date di dashboard ke gaya visual date picker pada modal `Master Plan`, tanpa mengubah logic, state, atau default date masing-masing menu.

Referensi visual utama:

- [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php) pada trigger `Tanggal Rencana` Master Plan
- [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php) pada modal kalender global
- [resources/css/dashboard-shell.css](../resources/css/dashboard-shell.css) untuk primitive `date-trigger-button`, `date-trigger-button-compact`, `calendar-day-button`, dan footer action kalender

## Aturan

- Ubah visual saja.
- Jangan ubah source state date.
- Jangan ubah default range / default tanggal existing.
- Jangan ubah nama context `openCalendar(...)`.
- Semua trigger date harus konsisten ukuran, padding, radius, icon spacing, dan state selected.
- Semua kalender tetap pakai modal kalender global yang sekarang.

## Baseline Style

Target style mengikuti `Master Plan`:

- Trigger date memakai primitive global `date-trigger-button`
- Variant compact memakai `date-trigger-button-compact`
- Selected date di kalender wajib teks putih
- Footer kalender pakai action kecil seperti `Reset` dan `Selesai` yang sudah diringankan
- Modal kalender tetap di luar canvas utama via `teleport to="body"`

## Inventaris Date Picker

### Filter Date Picker

- [x] `Master Plan`  
  File: [resources/views/dashboard/partials/menus/master-plan.blade.php](../resources/views/dashboard/partials/menus/master-plan.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing menu ini

- [x] `Top Content`  
  File: [resources/views/dashboard/partials/menus/top-content.blade.php](../resources/views/dashboard/partials/menus/top-content.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Low Content`  
  File: [resources/views/dashboard/partials/menus/low-content.blade.php](../resources/views/dashboard/partials/menus/low-content.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Distribution`  
  File: [resources/views/dashboard/partials/menus/distribution.blade.php](../resources/views/dashboard/partials/menus/distribution.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Analytics`  
  File: [resources/views/dashboard/partials/menus/analytics.blade.php](../resources/views/dashboard/partials/menus/analytics.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Unboxing`  
  File: [resources/views/dashboard/partials/menus/unboxing.blade.php](../resources/views/dashboard/partials/menus/unboxing.blade.php)  
  Context: `openCalendar($event, 'filter')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Meta Story`  
  File: [resources/views/dashboard/partials/menus/meta-story.blade.php](../resources/views/dashboard/partials/menus/meta-story.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'metaStory')`  
  Default behavior: tetap pakai `metaStoryDateFilter`

- [x] `Meta Feed`  
  File: [resources/views/dashboard/partials/menus/meta-feed.blade.php](../resources/views/dashboard/partials/menus/meta-feed.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'metaFeed')`  
  Default behavior: tetap pakai `metaFeedDateFilter`

- [x] `Analisa Insight`  
  File: [resources/views/dashboard/partials/menus/analisa-insight.blade.php](../resources/views/dashboard/partials/menus/analisa-insight.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'insight')`  
  Default behavior: tetap pakai `insightDateFilter`

- [x] `Ads Log`  
  File: [resources/views/dashboard/partials/menus/ads-log.blade.php](../resources/views/dashboard/partials/menus/ads-log.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'ads_log')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Harga Kompetitor`  
  File: [resources/views/dashboard/partials/menus/harga-kompetitor.blade.php](../resources/views/dashboard/partials/menus/harga-kompetitor.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'hargaKompetitor')`  
  Default behavior: tetap pakai filter tanggal existing

- [x] `Order Online`  
  File: [resources/views/dashboard/partials/menus/order-online.blade.php](../resources/views/dashboard/partials/menus/order-online.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'orderanOnline')`  
  Default behavior: tetap pakai `orderanOnlineDateRange`

- [x] `Unit Ditanya`  
  File: [resources/views/dashboard/partials/menus/unit-ditanya.blade.php](../resources/views/dashboard/partials/menus/unit-ditanya.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'unitDitanya')`  
  Default behavior: tetap pakai `unitDitanyaDateRange`

- [x] `Budgeting` top range filter  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'filter', '', 'budgeting')`  
  Default behavior: tetap pakai `budgetDateFilter`

### Form / Modal Date Picker

- [x] `Master Plan`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'master')`  
  Default behavior: tetap pakai `masterForm.Tanggal_Rencana`

- [x] `Master Plan Distribution Meta`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'published', plat)`  
  Default behavior: tetap pakai `masterForm.Distribution_Meta[plat].date`

- [x] `Story`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'story')`  
  Default behavior: tetap pakai `storyForm.Tanggal`

- [x] `Order Online`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'orderanOnline1')`  
  Default behavior: tetap pakai `orderanOnlineForm['TANGGAL']`

- [x] `Unit Ditanya`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'unitDitanya1')`  
  Default behavior: tetap pakai `unitDitanyaForm['TANGGAL']`

- [x] `Claim Garansi - Tanggal Masuk`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'claimGaransi1')`  
  Default behavior: tetap pakai `claimGaransiForm['TANGGAL_MASUK']`

- [x] `Claim Garansi - Tanggal Diambil`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'claimGaransi2')`  
  Default behavior: tetap pakai `claimGaransiForm['TANGGAL_DIAMBIL']`

- [x] `Claim Garansi - Tanggal Estimasi`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'claimGaransi3')`  
  Default behavior: tetap pakai `claimGaransiForm['TANGGAL_ESTIMASI']`

- [x] `Keep Barang - Tanggal Keep`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'keepBarangTanggalKeep')`  
  Default behavior: tetap pakai `keepBarangForm.TANGGAL_KEEP`

- [x] `Keep Barang - Rencana Pengambilan`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'keepBarangRencanaAmbil')`  
  Default behavior: tetap pakai `keepBarangForm.RENCANA_PENGAMBILAN`

- [x] `Keep Barang - Deadline Gudang`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'keepBarangDeadlineGudang')`  
  Default behavior: tetap pakai `keepBarangForm.TANGGAL_EXPIRED`

- [x] `Program Promo - Start`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'promoDate1')`  
  Default behavior: tetap pakai `promoForm.Periode_Start`

- [x] `Program Promo - End`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'promoDate2')`  
  Default behavior: tetap pakai `promoForm.Periode_End`

- [x] `Unboxing - Upload Date`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'unboxingUploadDate')`  
  Default behavior: tetap pakai `unboxingForm.Upload_Date`

- [x] `Distribution - Tanggal Publish`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'distribution')`  
  Default behavior: tetap pakai `distributionForm.Tanggal_Publish`

- [x] `Sell Out - Periode Start`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'sotDate1')`  
  Default behavior: tetap pakai `sellOutForm.Periode_Start`

- [x] `Sell Out - Periode End`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'sotDate2')`  
  Default behavior: tetap pakai `sellOutForm.Periode_End`

- [x] `Harga Kompetitor - Tanggal Cek`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'hargaKompetitorCek')`  
  Default behavior: tetap pakai `hargaKompetitorForm.Tanggal_Cek`

- [x] `Ads Log - Tanggal`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'adsTanggal')`  
  Default behavior: tetap pakai `adsForm.Tanggal`

- [x] `LPJK - Tanggal`  
  File: [resources/views/dashboard/partials/menus/budgeting.blade.php](../resources/views/dashboard/partials/menus/budgeting.blade.php)  
  Context: `openCalendar($event, 'form', '', 'lpjkTanggal')`  
  Default behavior: tetap pakai `lpjkForm.Tanggal`

- [x] `Asset Vendor Inventory - Purchase Date`  
  File: [resources/views/dashboard/partials/menus/asset-vendor-inventory.blade.php](../resources/views/dashboard/partials/menus/asset-vendor-inventory.blade.php)  
  Context: `openCalendar($event, 'form', '', 'aviTanggal')`  
  Default behavior: tetap pakai `aviForm.Purchase_Date`

## Scope Teknis

Perubahan yang boleh:

- class trigger date
- spacing icon kalender
- radius / border / hover / focus state
- ukuran teks header kalender
- ukuran teks footer kalender
- state selected / in-range / start / end

Perubahan yang tidak boleh:

- `getDefaultDateRange()`
- nilai default form date seperti `todayStr()`
- nama context `openCalendar`
- logic `isStartDate`, `isEndDate`, `isSelectedDate`, `isInRange`
- struktur penyimpanan tanggal ke form/filter masing-masing

## Catatan Implementasi

- Prioritaskan pakai class global, bukan utility inline berulang.
- Jika ada trigger date yang masih pakai utility mentah, migrasikan ke `date-trigger-button` atau `date-trigger-button-compact`.
- Semua perubahan visual harus diverifikasi di mobile dan desktop.
