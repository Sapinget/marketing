# Audit Form Dropdown Search

## Scope

- File audited: `public/marketing-dashboard.html`
- Focus: form field yang seharusnya bisa pakai dropdown searchable dari data DB / data existing / settings
- Excluded: menu `Nama Stock`
- Status implementasi terakhir: sebagian temuan utama sudah diimplementasikan pada `2026-06-30`

## Sudah Diimplementasikan

- `Order Online > Handle` sekarang searchable dropdown dari data existing
- `Order Online > Type Unit / Produk` sekarang searchable dropdown dari source unit gabungan
- `Unit Ditanya > RAM / Internal / Size / Tipe` sekarang searchable dropdown
- `Claim Garansi > Tipe / Seri / Model` sekarang searchable dropdown
- `Keep Barang > Type HP / Handle By / Kasir By / Team Gudang` sekarang searchable dropdown
- `Claim Garansi > Status` sekarang sudah tidak hardcoded penuh, sudah merge data existing + fallback
- `Keep Barang > Status` sekarang sudah tidak hardcoded penuh, sudah merge data existing + fallback

## Sudah Sesuai

### Master Plan

- `Format` sudah pakai searchable dropdown dari `formatOptions`
- `Platform` sudah pakai searchable dropdown dari `platformOptions`
- `Colab` sudah pakai searchable dropdown
- `Editor` sudah pakai searchable dropdown dari `editorOptions`
- `Status` sudah pakai searchable dropdown dari `statusOptions`

### Order Online

- `Ecommerce` sudah pakai searchable dropdown dari `orderanEcommerceOptions`
- `Pengiriman` sudah pakai searchable dropdown dari `orderanPengirimanOptions`
- `Status` sudah pakai searchable dropdown dari `orderanStatusOptions`

### Unit Ditanya

- `Kategori` sudah pakai searchable dropdown dari `unitKategoriOptions`
- `Brand` sudah pakai searchable dropdown dari `unitBrandOptions`
- `Seri` sudah pakai searchable dropdown dari `nsSeriOptions`
- `Kondisi` sudah pakai searchable dropdown dari `unitKondisiOptions`
- `Available` sudah pakai searchable dropdown dari `unitAvailableOptions`
- `RAM` sudah pakai searchable dropdown dari `unitRAMOptions`
- `Internal` sudah pakai searchable dropdown dari `unitInternalOptions`
- `Size` sudah pakai searchable dropdown dari `unitSizeOptions`

### Sell Out

- `Vendor` sudah pakai searchable dropdown dari `sellOutVendorOptions`
- `Kategori` sudah pakai searchable dropdown
- `Brand` sudah pakai searchable dropdown
- `Seri` sudah pakai searchable dropdown
- `RAM` sudah pakai searchable dropdown
- `Internal` sudah pakai searchable dropdown
- `Size` sudah pakai searchable dropdown
- `Kondisi` sudah pakai searchable dropdown

### Claim Garansi

- `Lokasi Klaim` sudah pakai searchable dropdown dari `claimLokasiOptions`
- `Garansi` sudah pakai searchable dropdown dari `claimGaransiOptions`

### Program Promo

- `Kategori` sudah pakai searchable dropdown dari `kategoriPromoOptions`

### Unboxing

- `Editor` sudah pakai searchable dropdown

### Distribution

- `Platform` sudah pakai dropdown dari `platformOptions`

### Analytics

- `Platform` sudah pakai dropdown dari `platformOptions`

## Temuan Utama

### 1. Field masih text input padahal data kandidat sudah ada

#### Order Online

- `Handle` masih text input. Kandidat source bisa diambil dari histori `orderanOnlineData`.
- `Type Unit / Produk` masih text input. Kandidat source bisa diambil dari histori order / master produk yang sudah dipakai di menu lain.

#### Unit Ditanya

- `Tipe` masih text input. Ini tidak konsisten karena field produk lain di form sama sudah pakai source data existing.

#### Claim Garansi

- `Tipe` masih text input.
- `Seri` masih text input.
- `Model` masih text input.
- Tiga field ini kandidat kuat untuk reuse master produk / histori unit.

#### Keep Barang

- `Handle By` masih text input, padahal computed source `keepBarangUniqueHandleBy` sudah ada.
- `Type HP` masih text input. Kandidat source bisa diambil dari histori keep barang atau master unit.
- `Kasir By` masih text input. Kandidat source bisa diambil dari histori keep barang.
- `Team Gudang` masih text input. Kandidat source bisa diambil dari histori keep barang.

### 2. Dropdown sudah ada, tapi source masih hardcoded

#### Claim Garansi

- `Status` pakai `claimStatusOptions` hardcoded, belum DB/settings-backed.

#### Keep Barang

- `Status` dropdown ada, tapi masih hardcoded, belum mengikuti data existing/settings.

#### Ads

- `Platform` pakai `adsPlatformOptions` hardcoded.
- `Kategori` pakai `adsKategoriOptions` hardcoded.

#### LPJK

- `Status` pakai `lpjkStatusOptions` hardcoded.

#### Unboxing

- `Status` pakai list hardcoded `DRAFT / REVIEW / PUBLISH`.

## Prioritas Rekomendasi

### Prioritas 1

- Ubah field yang sudah jelas punya source existing jadi searchable dropdown:
- `Keep Barang > Handle By`
- `Order Online > Handle`
- `Unit Ditanya > Tipe`

### Prioritas 2

- Tambah source dropdown untuk field produk/people yang datanya sudah berulang di histori:
- `Order Online > Type Unit / Produk`
- `Claim Garansi > Tipe / Seri / Model`
- `Keep Barang > Type HP / Kasir By / Team Gudang`

### Prioritas 3

- Pindah dropdown hardcoded ke source settings / DB supaya konsisten dan gampang maintain:
- `Claim Garansi > Status`
- `Keep Barang > Status`
- `Ads > Platform, Kategori`
- `LPJK > Status`
- `Unboxing > Status`

## Catatan Implementasi

- Pola komponen searchable dropdown sudah ada di file ini lewat `search-select-popover`.
- Source data existing juga sudah banyak tersedia dalam bentuk computed options.
- Gap terbesar bukan di UI component, tapi di field mapping yang belum diarahkan ke options source.
