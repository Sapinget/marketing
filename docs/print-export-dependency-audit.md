# Print/Export Subsystem — Dependency Audit

> **Fase 2a** dari Marketing Dashboard Blade Modularization Plan
> Tanggal: 2026-07-07
> Sumber: `public/marketing-dashboard.html` (21.096 baris)

---

## 1. Ringkasan

Sistem print/export di dashboard ini terdiri dari:

- **13 fungsi export PDF** (per menu)
- **6 fungsi export Excel** (per menu + 1 generic)
- **1 fungsi export Excel generic** (router)
- **1 fungsi export PDF generic** (router)
- **8 core print helper functions** (shared)
- **~10 global utility dependencies** (formatter, API, notifikasi)

Total: ~1.200 baris kode JavaScript untuk print/export dari ~7.700 baris total JS app.

---

## 2. Core Print Helper Functions

Semua fungsi ini berada di bagian akhir `createApp()` (sekitar baris 19620-19850).

| Fungsi | Baris | Deskripsi | Dependencies |
|--------|-------|-----------|-------------|
| `getPrintBaseStyles_()` | 19620 | Membangun `<style>` block untuk CSS print (A4, tabel, header, signature) | `getThemeVarsCSS_()` |
| `getPrintOrgHeaderHTML_()` | 19665 | HTML header organisasi (logo kiri + kanan, nama, alamat) | (none) |
| `getPrintHTML_({title, subtitle, period, totalLabel, totalValue, headers, rows, extraStyles, extraHtml, showSignature})` | 19681 | **Builder utama** — menggabungkan semua komponen jadi HTML print lengkap | `getPrintBaseStyles_()`, `getPrintOrgHeaderHTML_()` |
| `getPrintAutoBootstrapScript_()` | 19720 | Script auto-print (wait for fonts/images, trigger print, close after print) | (none) |
| `_printNoHeaderCss` | 19762 | CSS constant untuk padding print | (none) |
| `buildStandalonePrintHtml_(html, {autoPrint})` | 19767 | Wrapping HTML dengan CSS padding + optional auto-print script | `_printNoHeaderCss`, `getPrintAutoBootstrapScript_()` |
| `waitForPrintAssets_(printWindow)` | 19767 | Promise yang resolve ketika semua font & image di print window loaded | (none) |
| `submitBrowserPrintJob_(printHtml)` | 19794 | POST ke `/print-job` dengan HTML payload | `jsonApi()` |
| `openPrintWindow_(html, reportName)` | 19800 | **Entry point utama** — buka popup, kirim ke backend, arahkan ke `/print-job/{token}` | `buildStandalonePrintHtml_()`, `submitBrowserPrintJob_()`, `showNotification()`, `getFriendlyErrorMessage()`, `notifyError()`, `ensureRunApi()`, `_isGasHost`, `_hasNativeGas`, `window.MARKETING_BACKEND_URL` |

### Flow Print:

```
Menu export function
  └─ getPrintHTML_({ title, headers, rows, ... })
      ├─ getPrintBaseStyles_()           → CSS inline
      ├─ getPrintOrgHeaderHTML_()        → kop surat
      └─ return HTML string
  └─ openPrintWindow_(html, reportName)
      ├─ buildStandalonePrintHtml_(html) → tambah CSS padding
      ├─ [if GAS] btoa → storePrintJobLaravel → popup → /print-job/{token}
      └─ [if browser] submitBrowserPrintJob_() → POST /print-job → popup → /print-job/{token}
```

---

## 3. Global Dependencies (Digunakan oleh Print/Export)

| Dependency | Baris | Tipe | Digunakan Oleh |
|-----------|-------|------|----------------|
| `resolveAppUrl(url)` | 14208 | Utility | `loadSettings`, `getSettings`, `submitBrowserPrintJob_`, runner methods |
| `jsonApi(url, options)` | 14229 | Utility | `submitBrowserPrintJob_`, semua CRUD API |
| `formatShortDate(dateStr)` | 15777 | Formatter | Semua template + export PDF |
| `formatNumber(value)` | 16023 | Formatter (Intl.NumberFormat id-ID) | Semua template + export PDF |
| `formatCurrency(value)` | 18459 | Formatter (Rp) | export Bonus, export Budget |
| `showNotification(message, type)` | 16050 | Notifikasi | Semua export function (sukses/error/nodata) |
| `notifyError(prefix, error, fallback)` | 16087 | Notifikasi error | Semua export PDF (catch handler) |
| `getFriendlyErrorMessage(error, fallback)` | ~16080 | Utility | `notifyError`, `openPrintWindow_` |
| `ensureRunApi()` | ~16800 | Factory | Semua export (Excel via runner, PDF via storePrintJobLaravel) |
| `_isGasHost` | 16871 | Boolean flag | `openPrintWindow_` (branch GAS vs browser) |
| `_hasNativeGas` | 16872 | Boolean flag | `openPrintWindow_` |
| `getThemeVarsCSS_()` | 19602 | Utility | `getPrintBaseStyles_()` |
| `ensureXLSX()` | 18246 | Async loader | Semua export Excel (load `XLSX` CDN) |
| `monthNames` | ~13500 | Data | exportBonusToPDF |
| `esc(v)` (helper) | ~18880 | Utility | exportBonusToPDF (inline) |

---

## 4. Matriks Menu → Export Functions

### 4.1 Export PDF

| Menu | Nama Fungsi | Baris | Kompleksitas | Risiko Ekstraksi |
|------|-----------|-------|-------------|-------------------|
| analytics | `exportAnalyticsToPDF()` | 15683 | SEDANG — grouping data, chart | SEDANG |
| dashboard | `exportPdf()` (generic router) | 15726 | RENDAH — router ke fungsi spesifik | RENDAH |
| unit_ditanya | `exportUnitDitanyaToPDF()` | 18341 | RENDAH — tabel sederhana | **RENDAH → PILOT** |
| claim_garansi | `exportClaimGaransiToPDF()` | 18374 | RENDAH — tabel sederhana | **RENDAH → PILOT** |
| keep_barang | `exportKeepBarangToPDF()` | 18432 | RENDAH — tabel sederhana | RENDAH |
| bonus_report | `exportBonusToPDF()` | 18870 | **TINGGI** — bonus calculation, multiple summary rows | TINGGI |
| program_promo | `exportPromoToPDF()` | 19174 | SEDANG — tabel dengan kategori | SEDANG |
| sell_out | `exportSellOutToPDF()` | 19473 | SEDANG — vendor grouping (inline di fungsi) | SEDANG |
| ads_log | `exportAdsLogToPDF()` | 19870 | SEDANG — multi-kolom | SEDANG |
| harga_kompetitor | `exportPriceComparisonToPDF()` | 19974 | RENDAH — tabel sederhana | **RENDAH → PILOT** |
| lpjk_detail | `exportLpjkDetailToPDF()` | 20111 | SEDANG — multi-level data | SEDANG |
| budgeting | `exportBudgetToPDF()` | 20241 | SEDANG — grouping, multiple sections | SEDANG |

### 4.2 Export Excel

| Menu | Nama Fungsi | Baris | Kompleksitas |
|------|-----------|-------|-------------|
| Generic | `exportExcel()` (router) | 15654 | RENDAH — dispatch |
| unit_ditanya | `exportUnitDitanyaToExcel()` | 18254 | RENDAH |
| claim_garansi | `exportClaimGaransiToExcel()` | 18299 | RENDAH |
| keep_barang | `exportKeepBarangToExcel()` | 18401 | RENDAH |
| bonus_report | `exportBonusToExcel()` | 18939 | SEDANG |
| sell_out | `exportSellOutToExcel()` | 19426 | SEDANG |
| budgeting | `exportBudgetToExcel()` | 20338 | SEDANG |

> Note: Export Excel untuk menu master, ideation, distribution, analytics, unboxing, orderan_online, top_content_platform, low_content_platform menggunakan generic `exportExcel()` yang mengirim data via runner.

---

## 5. Analisis Dependency Per Fungsi

### 5.1 Fungsi dengan Dependency Minimal (Pilot Kandidat)

**exportUnitDitanyaToPDF()** (baris 18341):
```javascript
// Dependencies:
//   - filteredUnitDitanyaData (vue ref)
//   - formatShortDate()
//   - esc() inline
//   - getPrintHTML_()
//   - openPrintWindow_()
//   - showNotification()
//   - notifyError()
// Tidak ada dependency ke: chart, bonus calculation, state kompleks
```

**exportPriceComparisonToPDF()** (baris 19974):
```javascript
// Dependencies:
//   - filteredHargaKompetitorData (vue ref)
//   - formatCurrency()
//   - formatShortDate()
//   - esc() inline
//   - getPrintHTML_()
//   - openPrintWindow_()
//   - showNotification()
//   - notifyError()
// Tidak ada dependency ke: chart, grouping kompleks
```

### 5.2 Fungsi dengan Dependency Tinggi

**exportBonusToPDF()** (baris 18870):
```javascript
// Dependencies COMPLEX:
//   - filteredBonusRows (vue computed — tergantung masterPlanData, distributionData, dsb)
//   - bonusTotal (vue computed)
//   - bonusFilter, bonusMonth, bonusYear (vue refs)
//   - monthNames (global array)
//   - formatShortDate(), formatCurrency(), formatNumber()
//   - esc() inline
//   - getPrintHTML_(), openPrintWindow_()
//   - Extra styles dengan column widths fixed
//   - Multiple summary sections (extraHtml)
```

**exportBudgetToPDF()** (baris 20241):
```javascript
// Dependencies COMPLEX:
//   - budgetingConfig (vue ref)
//   - Multiple category grouping logic
//   - Summary calculations
//   - chart data (mungkin)
```

---

## 6. Rekomendasi Urutan Ekstraksi

### Fase 2b — Pilot Extraction (1-2 menu)

Berdasarkan audit, rekomendasi untuk pilot:

| Priority | Menu | Alasan |
|----------|------|--------|
| **1** | **unit_ditanya** | Dependency minimal, tabel sederhana, sudah ada export Excel juga |
| **2** | **harga_kompetitor** | Dependency minimal, tabel flat, PDF + Excel patterns sederhana |
| **3** | **claim_garansi** | Mirip unit_ditanya, risiko rendah |

### Output Pilot

1. Modul `print-core.js`:
   - `getPrintBaseStyles()`
   - `getPrintOrgHeaderHTML()`
   - `getPrintHTML()`
   - `buildStandalonePrintHtml()`
   - `waitForPrintAssets()`
   - `getPrintAutoBootstrapScript()`

2. Modul `print-browser.js`:
   - `submitBrowserPrintJob()`
   - `openPrintWindow()` (browser path)

3. Modul `print-gas.js`:
   - `openPrintWindow()` (GAS path)
   - (adapter functions for GAS bridge)

4. Modul per menu (pilot):
   - `export/unit-ditanya.js` — `exportUnitDitanyaToPDF()`
   - `export/harga-kompetitor.js` — `exportPriceComparisonToPDF()`

### Setelah Pilot — Rollout

| Urutan | Menu | Kelompok |
|--------|------|---------|
| 1-3 | unit_ditanya, harga_kompetitor, claim_garansi | **RENDAH** — tabel sederhana, grouping minimal |
| 4-6 | program_promo, ads_log, keep_barang | **SEDANG** — multi-kolom, sedikit grouping |
| 7-9 | sell_out, lpjk_detail, budgeting | **SEDANG** — grouping, summary |
| 10 | analytics | **SEDANG** — chart |
| 11 | bonus_report | **TINGGI** — kompleksitas perhitungan |

---

## 7. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Dependency global kehilangan scope saat dipindah ke modul | Export gagal karena function undefined | Export module harus import dependency secara eksplisit |
| `getPrintHTML_()` adalah closure yang mengakses `getPrintBaseStyles_()` dan `getPrintOrgHeaderHTML_()` | Fungsi tidak bisa dipisah tanpa refactor | Ketiga fungsi ini HARUS dipindah bersama sebagai modul |
| `openPrintWindow_()` mengakses `_isGasHost`, `_hasNativeGas`, `window.MARKETING_BACKEND_URL` | GAS flow rusak | Module harus export flag-flag ini atau menerimanya sebagai parameter |
| `exportBonusToPDF()` mengakses banyak vue refs/computed | Dependency brittle | Perlu dependency injection atau menerima data sebagai parameter |
| Generic `exportExcel()` dan `exportPdf()` routing | Fungsi generic tidak bisa dipindah sebelum semua menu spesifik dipindah | Biarkan generic router di main app sampai semua menu selesai |

---

## 8. File Structure Target

```
resources/js/dashboard/
  export/
    print-core.js        ← getPrintBaseStyles_, getPrintOrgHeaderHTML_, getPrintHTML_,
                           buildStandalonePrintHtml_, waitForPrintAssets_, getPrintAutoBootstrapScript_
    print-browser.js     ← submitBrowserPrintJob_, openPrintWindow_ (browser path)
    print-gas.js         ← openPrintWindow_ (GAS path), GAS adapter
    unit-ditanya.js      ← exportUnitDitanyaToExcel_, exportUnitDitanyaToPDF_
    harga-kompetitor.js  ← exportPriceComparisonToPDF_
    claim-garansi.js     ← exportClaimGaransiToExcel_, exportClaimGaransiToPDF_
    keep-barang.js       ← exportKeepBarangToExcel_, exportKeepBarangToPDF_
    ...
  shared/
    formatters.js        ← formatShortDate_, formatNumber_, formatCurrency_
    notifications.js     ← showNotification_, notifyError_, getFriendlyErrorMessage_
    api.js               ← jsonApi_, resolveAppUrl_, ensureRunApi_
```

> **Catatan**: File JS eksternal membutuhkan build tool (vite/webpack) atau dimuat sebagai ES modules.
> Alternatif: script tag terpisah di Blade head, atau inline module.

---

## 9. Yang Tidak Berubah

- `POST /print-job` — endpoint tetap
- `GET /print-job/{token}` — endpoint tetap
- CSS print styles — tidak berubah sampai ada refactor khusus
- Kontrak response token — tidak berubah
- Apps Script bridge (`storePrintJobLaravel`) — tidak berubah
