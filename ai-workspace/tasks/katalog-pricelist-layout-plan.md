# Plan: Rapikan Posisi Teks Generate Katalog Pricelist

## Konteks

Menu `Katalog Pricelist` saat ini generate katalog dengan canvas dari file:

- `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`

Referensi layout rapi:

- `/Users/serverbot/Library/CloudStorage/GoogleDrive-official@purapuraponsel.com/Shared drives/PURA PURA PONSEL/DESAIN/SPESIAL PRICE/SPESIAL PRICE 26 JANUARI 2026/REALME.png`

Template background saat ini:

- `/Users/serverbot/Documents/ref2.png`

Masalah saat ini:

- Text hasil generate masih berupa layout dua kolom bebas.
- Tidak ada header tabel.
- Nama produk, harga normal, dan special price tidak sejajar seperti referensi.
- Harga special terlalu jauh ke kanan dan kurang konsisten.
- Row spacing belum mengikuti gaya tabel price list.
- Area teks belum simetris terhadap area kosong pada template `ref2.png`.

## Goal Visual

Ubah output `Generate Katalog` agar menyerupai referensi `REALME.png`:

1. Layout berbentuk tabel rapi.
2. Ada header bar gelap dengan kolom:
   - `TYPE`
   - `RAM`
   - `STORAGE`
   - `NORMAL PRICE`
   - `SPECIAL PRICE`
3. Row zebra / alternate background abu-abu tipis.
4. Semua teks rata dan simetris:
   - TYPE rata kiri.
   - RAM dan STORAGE center.
   - Harga rata kanan.
5. Harga normal dari `HARGA NASIONAL`.
6. Harga special dari `SPECIAL PRICE` / fallback `SPESIAL PRICE`.
7. Tidak memakai format `1. Produk` panjang seperti saat ini; urutan tetap menentukan order, tapi nomor tidak perlu ditampilkan kecuali nanti diminta.
8. Area tabel otomatis berada di ruang kosong tengah template `ref2.png`, tidak menabrak footer.

## Layout Target untuk Template `ref2.png`

Template `ref2.png` ukuran story: `1080x1350` atau `1080x1920` tergantung template upload. Dari visual yang dilampirkan, area kosong utama berada di tengah.

Rekomendasi default layout:

```js
{
  x: 72,
  y: 475,
  width: 936,
  headerHeight: 46,
  rowHeight: 36,
  maxItems: 20,
  borderRadius: 12,
  columns: {
    type: 0.33,
    ram: 0.12,
    storage: 0.14,
    normal: 0.21,
    special: 0.20
  },
  headerColor: '#3f3f3f',
  headerTextColor: '#ffffff',
  rowEvenColor: 'rgba(255,255,255,0.0)',
  rowOddColor: 'rgba(255,255,255,0.55)',
  textColor: '#3f3f3f',
  normalPriceColor: '#3f3f3f',
  specialPriceColor: '#3f3f3f',
  fontFamily: 'Inter, Arial, sans-serif',
  headerFontSize: 16,
  bodyFontSize: 16,
  priceFontSize: 16
}
```

Catatan: `y`, `rowHeight`, dan `maxItems` harus bisa diubah lewat UI template agar bisa disesuaikan untuk background lain.

## Data Mapping

Dari row pricelist:

| Kolom tabel | Field data | Catatan |
| --- | --- | --- |
| TYPE | `seri` jika ada, fallback dari `nama_produk` tanpa brand/ram/storage | Jangan tampilkan brand berulang jika template sudah brand-specific |
| RAM | `ram` | Center align |
| STORAGE | `storage` | Center align |
| NORMAL PRICE | `harga_nasional` | Format `1.299.000`, tanpa `Rp` agar sesuai referensi |
| SPECIAL PRICE | `special_price` | Format `1.239.000`, tanpa `Rp` |

Jika `seri` kosong:

- Gunakan `nama_produk`.
- Bersihkan prefix brand dan suffix RAM/STORAGE bila bisa.

Contoh:

```text
ITEL P80 4GB/128GB -> P80
SAMSUNG GALAXY A07 4GB/64GB -> GALAXY A07
```

## Perubahan Kode yang Dibutuhkan

### 1. Refactor `drawCatalogPage()`

File:

- `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`

Fungsi sekarang menggambar:

- Nama produk di kiri.
- Harga normal di kiri bawah.
- Special price di kolom bebas kanan.

Ubah menjadi:

- `drawTableHeader(ctx, cfg, geometry)`
- `drawTableRow(ctx, row, index, cfg, geometry)`
- `catalogProductType(row)` helper
- `formatCatalogPrice(value)` helper tanpa prefix `Rp`

### 2. Tambah geometry kolom

Hitung dari `x`, `width`, dan proporsi kolom:

```js
const colTypeX = x;
const colRamX = x + width * 0.33;
const colStorageX = x + width * 0.45;
const colNormalX = x + width * 0.59;
const colSpecialX = x + width * 0.80;
```

Gunakan `ctx.textAlign`:

- TYPE: `left`
- RAM: `center`
- STORAGE: `center`
- NORMAL: `right`
- SPECIAL: `right`

### 3. Header bar rounded

Canvas tidak punya native rounded rect di semua browser lama. Buat helper:

```js
const fillRoundedRect = (ctx, x, y, width, height, radius) => { ... };
```

Gunakan untuk header.

### 4. Zebra rows

Untuk row ganjil:

```js
ctx.fillStyle = cfg.rowOddColor || 'rgba(255,255,255,0.55)';
ctx.fillRect(x, rowY, width, rowHeight);
```

Untuk row genap bisa transparent atau putih tipis.

### 5. Text baseline

Set:

```js
ctx.textBaseline = 'middle';
```

Agar text vertikal center di setiap row.

### 6. Auto page capacity

`maxItems` default untuk tabel harus berdasarkan tinggi yang tersedia:

```js
const availableHeight = canvas.height - y - footerSafeArea;
const computedMaxItems = Math.floor((availableHeight - headerHeight) / rowHeight);
```

Tetap izinkan override `layout_config.maxItems`.

Footer safe area:

- Story/ref2: minimal `90px`.
- Jika footer background besar, gunakan default `120px`.

### 7. UI Template Config

Menu sekarang hanya expose:

- `x`
- `y`
- `width`
- `maxItems`

Tambahkan field opsional:

- `rowHeight`
- `headerHeight`
- `headerFontSize`
- `bodyFontSize`
- `priceFontSize`

Supaya posisi bisa tuning tanpa edit kode.

## Default Layout Baru

Ubah default `catalogTemplateForm.layout_config` dari mode bebas ke mode tabel:

```js
layout_config: {
  x: 72,
  y: 475,
  width: 936,
  headerHeight: 46,
  rowHeight: 36,
  maxItems: 20,
  borderRadius: 12,
  headerFontSize: 16,
  bodyFontSize: 16,
  priceFontSize: 16,
  headerColor: '#3f3f3f',
  headerTextColor: '#ffffff',
  rowOddColor: 'rgba(255,255,255,0.55)',
  rowEvenColor: 'rgba(255,255,255,0)',
  textColor: '#3f3f3f',
  normalPriceColor: '#3f3f3f',
  specialPriceColor: '#3f3f3f'
}
```

## Backward Compatibility

Template lama mungkin masih punya config lama:

```js
nameFontSize
normalPriceFontSize
specialPriceFontSize
```

Saat render:

- Jika config tabel baru tidak ada, tetap gunakan default tabel.
- Tidak perlu mempertahankan layout bebas lama karena goal sekarang adalah tabel rapi.

## Testing Plan

### Unit/Feature Test

Update `tests/Feature/MarketingDashboardShellTest.php`:

- Assert ada helper/function baru:
  - `fillRoundedRect`
  - `catalogProductType`
  - `formatCatalogPrice`
  - `drawCatalogTableHeader` atau string header `NORMAL PRICE`
- Assert UI config expose:
  - `headerHeight`
  - `rowHeight`
  - `priceFontSize`

### Browser/Build Verification

Jalankan:

```bash
php artisan test tests/Feature/MarketingDashboardShellTest.php --filter='pricelist|catalog'
php artisan test tests/Feature/PricelistCatalogTest.php
npm run build
```

### Manual QA

1. Buka `http://127.0.0.1:8090/#pricelist_katalog`.
2. Pilih template background `ref2.png`.
3. Pilih brand, contoh `REALME` atau `ITEL`.
4. Generate preview.
5. Pastikan:
   - Header tabel berada simetris.
   - Kolom sejajar.
   - Harga normal dan special price rata kanan.
   - Row tidak menabrak footer.
   - Jika produk banyak, auto paginate.

## Risiko

1. Background tiap template bisa punya area kosong berbeda.
   - Solusi: layout config tetap editable.
2. Nama produk terlalu panjang.
   - Solusi: implement text truncate/fit-to-width untuk TYPE.
3. Font browser berbeda dari desain referensi.
   - Solusi: pakai font stack dashboard dulu; nanti bisa tambah font khusus kalau dibutuhkan.
4. Data `seri` beberapa produk aksesoris bisa kosong/aneh.
   - Solusi: fallback ke cleaned `nama_produk`.

## Acceptance Criteria

- Output generate katalog terlihat seperti tabel price list referensi `REALME.png`.
- Posisi tabel simetris di template `ref2.png`.
- Header dan row sejajar rapi.
- Harga normal/special price tidak tumpang tindih.
- No regression: tests pass dan build sukses.
