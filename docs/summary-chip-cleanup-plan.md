# Summary Card + Chip Cleanup Plan

## Progress Tracker

### DELETE (hapus chips, tidak tambah card baru)
- [ ] master-plan — hapus chips Status breakdown
- [ ] unboxing — hapus chips Status breakdown
- [ ] orderan — hapus chips Status breakdown
- [ ] claim-garansi — hapus chips Status breakdown

### CONVERT (tambah 1 card baru, hapus chips)
- [ ] ideation — Top Format
- [ ] distribution — Top Platform
- [ ] analytics — Top Platform Views
- [ ] story — Top Status
- [ ] promo — Top Kategori
- [ ] unit-ditanya — Top Brand
- [ ] lpjk — Top Status
- [ ] meta-story — Top Post Type
- [ ] meta-feed — Top Post Type
- [ ] avi — Top Vendor

### CONVERT SPECIAL
- [ ] harga — ganti Untung+Rugi dengan 4 range margin cards
- [ ] keep-barang — chips-grid → 4 summary cards proper

---

## Aturan Dasar

| Kondisi | Aksi |
|---------|------|
| Chip menampilkan dimensi yang SAMA dengan card (misal: Status breakdown, card juga punya Selesai/Proses) | **DELETE** chips section |
| Chip menampilkan dimensi yang BERBEDA / tidak ada di card manapun | **CONVERT** chip → card baru |
| Tidak ada card summary sama sekali (chip = satu-satunya summary) | **CONVERT** chips-grid → summary card section proper |

---

## Per-Menu Detail

### 1. master-plan `[ DELETE ]`

| | Detail |
|-|--------|
| **Cards** | Total Plan · Selesai (DONE+PUBLISHED) · Dalam Proses (EDITING+SHOOTING+PROGRES) · Bulan Ini |
| **Chips** | Status breakdown: DONE, PUBLISHED, EDITING, SHOOTING, PROGRES, IDE (top-6 by count) |
| **Overlap** | Chip dimension = Status. Card "Selesai" dan "Dalam Proses" juga dimension Status (aggregated). |
| **Aksi** | **DELETE** chips |

- [ ] JS `app-script-summary-computed-cluster.blade.php`: hapus `chips: _sChips(st)` dari `masterSummary`
- [ ] Blade `master-plan.blade.php`: hapus block `chips-grid`

---

### 2. ideation `[ CONVERT → Top Format ]`

| | Detail |
|-|--------|
| **Cards** | Total Ide · Status Ide (count IDE status) · Format (# jenis format) · Platform (# platform) |
| **Chips** | Format breakdown: nama format per count (Short Video: 5, Static: 3, …) |
| **Overlap** | Card "Format" = angka jumlah jenis (e.g. "4 jenis"). Chip = NAMA format + count. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Format" |

Card baru yang ditambahkan:
```js
{ label: 'Top Format', value: chips[0]?.label ?? '-', icon: 'fa-film',
  color: 'text-violet-500', sub: formatNumber(chips[0]?.n ?? 0) + ' konten',
  subColor: 'text-violet-600' }
```

- [ ] JS `app-script-summary-computed-cluster.blade.php`: ganti `chips: _sChips(fmt)` → card ke-5 di `ideationSummary`
- [ ] Blade `ideation.blade.php`: hapus `chips-grid` (card loop auto-render card ke-5)

---

### 3. distribution `[ CONVERT → Top Platform ]`

| | Detail |
|-|--------|
| **Cards** | Total Distribusi · Ada Link · Belum Link · Platform (# platform) |
| **Chips** | Platform breakdown: nama platform + count (TikTok: 8, IG: 5, …) |
| **Overlap** | Card "Platform" = jumlah platform. Chip = NAMA platform. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Platform" |

Card baru:
```js
{ label: 'Top Platform', value: chips[0]?.label ?? '-', icon: 'fa-share-nodes',
  color: 'text-blue-500', sub: formatNumber(chips[0]?.n ?? 0) + ' konten',
  subColor: 'text-blue-600' }
```

- [ ] JS: ganti `chips: _sChips(plat)` → card ke-5 di `distributionSummary`
- [ ] Blade `distribution.blade.php`: hapus `chips-grid`

---

### 4. analytics `[ CONVERT → Top Platform Views ]`

| | Detail |
|-|--------|
| **Cards** | Total Views · Total Likes · Total Comments · Total Konten |
| **Chips** | Platform → Views breakdown (TikTok: 50K views, IG: 20K, …) sorted by views |
| **Overlap** | Card = aggregate total semua platform. Chip = per-platform views. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Platform Views" |

Card baru:
```js
{ label: 'Top Platform', value: chips[0]?.label ?? '-', icon: 'fa-chart-bar',
  color: 'text-emerald-500', sub: formatNumber(chips[0]?.n ?? 0) + ' views',
  subColor: 'text-emerald-600' }
```

- [ ] JS `analyticsSummary`: hapus `chips`, tambah card ke-5 pakai `platV` entry pertama (sudah sorted by views)
- [ ] Blade `analytics.blade.php`: hapus `chips-grid`

---

### 5. unboxing `[ DELETE ]`

| | Detail |
|-|--------|
| **Cards** | Total Unboxing · Selesai (done/published/upload agg.) · Proses (sisanya) · Editor (# editor) |
| **Chips** | Status breakdown: per-status count individual |
| **Overlap** | Card "Selesai" dan "Proses" = Status dimension aggregated. Chip = dimensi Status juga. |
| **Aksi** | **DELETE** chips |

- [ ] JS: hapus `chips: _sChips(st)` dari `unboxingSummary`
- [ ] Blade `unboxing.blade.php`: hapus `chips-grid`

---

### 6. story `[ CONVERT → Top Status ]`

| | Detail |
|-|--------|
| **Cards** | Total Story · Ganjil (minggu ganjil) · Genap (minggu genap) · Status (# tipe status) |
| **Chips** | Status breakdown: DONE: 5, PENDING: 2, DRAFT: 1, … |
| **Overlap** | Card "Status" = count dari berapa jenis status ada (e.g. "3 tipe"). Chip = NAMA status + count. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Status" |

Card baru:
```js
{ label: 'Top Status', value: chips[0]?.label ?? '-', icon: 'fa-tag',
  color: 'text-amber-500', sub: formatNumber(chips[0]?.n ?? 0) + ' item',
  subColor: 'text-amber-600' }
```

- [ ] JS: ganti `chips: _sChips(st)` → card ke-5 di `storySummary`
- [ ] Blade `story.blade.php`: hapus `chips-grid`

---

### 7. promo `[ CONVERT → Top Kategori ]`

| | Detail |
|-|--------|
| **Cards** | Total Program · Kategori (# jenis kategori) · Total Nilai · Rata Harga |
| **Chips** | Kategori breakdown: nama kategori + count |
| **Overlap** | Card "Kategori" = jumlah jenis. Chip = NAMA kategori. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Kategori" |

Card baru:
```js
{ label: 'Top Kategori', value: chips[0]?.label ?? '-', icon: 'fa-tag',
  color: 'text-rose-500', sub: formatNumber(chips[0]?.n ?? 0) + ' program',
  subColor: 'text-rose-600' }
```

- [ ] JS: ganti `chips: _sChips(kat)` → card ke-5 di `promoSummary`
- [ ] Blade `promo.blade.php`: hapus `chips-grid`

---

### 8. orderan `[ DELETE ]`

| | Detail |
|-|--------|
| **Cards** | Total Orderan · Total Cair · Selesai (done/selesai/cair/lunas agg.) · Ekspedisi (# jasa) |
| **Chips** | Status breakdown: per-status count individual |
| **Overlap** | Card "Selesai" = Status dimension aggregated. Chip = Status dimension juga. |
| **Aksi** | **DELETE** chips |

- [ ] JS: hapus `chips: _sChips(st)` dari `orderanSummary`
- [ ] Blade `orderan.blade.php`: hapus `chips-grid`

---

### 9. unit-ditanya `[ CONVERT → Top Brand ]`

| | Detail |
|-|--------|
| **Cards** | Total Unit · Total Ditanya · Available · Brand (# brand) |
| **Chips** | Brand breakdown: nama brand + count |
| **Overlap** | Card "Brand" = jumlah brand. Chip = NAMA brand. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Brand" |

Card baru:
```js
{ label: 'Top Brand', value: chips[0]?.label ?? '-', icon: 'fa-mobile-screen',
  color: 'text-blue-500', sub: formatNumber(chips[0]?.n ?? 0) + ' unit',
  subColor: 'text-blue-600' }
```

- [ ] JS: ganti `chips: _sChips(brand)` → card ke-5 di `unitDitanyaSummary`
- [ ] Blade `unit-ditanya.blade.php`: hapus `chips-grid`

---

### 10. claim-garansi `[ DELETE ]`

| | Detail |
|-|--------|
| **Cards** | Total Klaim · Selesai · Pending · Jenis Garansi (# jenis) |
| **Chips** | Status breakdown: per-status count individual |
| **Overlap** | Card "Selesai" + "Pending" = Status dimension aggregated. Chip = Status dimension juga. |
| **Aksi** | **DELETE** chips |

- [ ] JS: hapus `chips: _sChips(st)` dari `claimSummary`
- [ ] Blade `claim-garansi.blade.php`: hapus `chips-grid`

---

### 11. harga `[ CONVERT SPECIAL — 4 range cards ]`

| | Detail |
|-|--------|
| **Cards** | Total Produk · Avg Margin · Untung (margin >0) · Rugi/Tipis (margin ≤0) |
| **Chips** | Margin range: >20%: N · 10-20%: N · <10%: N · NEGATIF: N |
| **Overlap** | Cards Untung/Rugi = binary. Chips = 4-tier range. Data BERBEDA (chips lebih detail). |
| **Aksi** | **CONVERT** chips → ganti card "Untung" + "Rugi/Tipis" dengan 4 card range margin |

Cards baru (replace cards[2] dan cards[3]):
```js
{ label: '>20% Margin', value: formatNumber(pct20plus), icon: 'fa-circle-check', color: 'text-emerald-500', sub: 'Margin sehat', subColor: 'text-emerald-600' },
{ label: '10–20%',      value: formatNumber(pct10to20), icon: 'fa-circle-half-stroke', color: 'text-blue-500', sub: 'Margin layak', subColor: 'text-blue-600' },
{ label: '<10%',        value: formatNumber(pctUnder10), icon: 'fa-triangle-exclamation', color: 'text-amber-500', sub: 'Margin tipis', subColor: 'text-amber-600' },
{ label: 'Negatif',     value: formatNumber(pctNeg),    icon: 'fa-circle-xmark', color: 'text-rose-500', sub: 'Margin rugi', subColor: 'text-rose-600' },
```

Total cards setelah: 6 (Total Produk, Avg Margin, >20%, 10-20%, <10%, Negatif).

- [ ] JS `app-script-summary-computed-cluster.blade.php`: refactor `hargaSummary` — hapus chips, extend cards ke 6
- [ ] Blade `harga.blade.php`: hapus `chips-grid`

---

### 12. lpjk `[ CONVERT → Top Status ]`

| | Detail |
|-|--------|
| **Cards** | Total Event · Total Budget · Realisasi · Sisa |
| **Chips** | Status breakdown: per-event-status count (BERJALAN, SELESAI, PLANNING, …) |
| **Overlap** | Cards = financial dimension. Chips = Status dimension. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Status" |

Card baru:
```js
{ label: 'Top Status', value: chips[0]?.label ?? '-', icon: 'fa-circle-dot',
  color: 'text-violet-500', sub: formatNumber(chips[0]?.n ?? 0) + ' event',
  subColor: 'text-violet-600' }
```

- [ ] JS: ganti `chips: _sChips(st)` → card ke-5 di `lpjkSummary`
- [ ] Blade `lpjk.blade.php`: hapus `chips-grid`

---

### 13. meta-story `[ CONVERT → Top Post Type ]`

| | Detail |
|-|--------|
| **Cards** | Total Story · Views · Reach · Follows · Navigation · Link Clicks · Profile Visits · Sticker Taps |
| **Chips** | Post type breakdown: nama post_type + count (Reel: 10, Image: 5, …) |
| **Overlap** | Cards = metric aggregates. Chips = content type breakdown. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Post Type" (card ke-9) |

Card baru:
```js
{ label: 'Top Post Type', value: chips[0]?.label ?? '-', icon: 'fa-clapperboard',
  color: 'text-rose-500', sub: formatNumber(chips[0]?.n ?? 0) + ' post',
  subColor: 'text-rose-600' }
```

- [ ] JS `app-script-meta-ig-analytics-presentation.blade.php`: ganti `chips: _sChips(_sCnt(d, r => r.post_type))` dari `metaStorySummary` → card ke-9
- [ ] Blade meta-story view: hapus `chips-grid`

---

### 14. meta-feed `[ CONVERT → Top Post Type ]`

| | Detail |
|-|--------|
| **Cards** | Total Konten · Views · Reach · Engagement Rate · Likes · Comments · Shares · Saves |
| **Chips** | Post type breakdown: nama post_type + count |
| **Overlap** | Cards = metric aggregates. Chips = content type breakdown. BERBEDA. |
| **Aksi** | **CONVERT** chips → 1 card baru "Top Post Type" (card ke-9) |

Card baru:
```js
{ label: 'Top Post Type', value: chips[0]?.label ?? '-', icon: 'fa-photo-film',
  color: 'text-blue-500', sub: formatNumber(chips[0]?.n ?? 0) + ' post',
  subColor: 'text-blue-600' }
```

- [ ] JS `app-script-meta-ig-analytics-presentation.blade.php`: ganti `chips: _sChips(...)` dari `metaFeedSummary` → card ke-9
- [ ] Blade meta-feed view: hapus `chips-grid`

---

### 15. asset-vendor-inventory `[ CONVERT → Top Vendor ]`

| | Detail |
|-|--------|
| **Cards** | Total Asset · Unique Vendors · Unique Brands · Total Quantity |
| **Chips** | `aviSummaryChips`: vendor breakdown (Samsung: 5, Apple: 3, …) |
| **Overlap** | Card "Unique Vendors" = angka jumlah vendor. Chip = NAMA vendor. BERBEDA. |
| **Aksi** | **CONVERT** `aviSummaryChips` → 1 card baru "Top Vendor" |

- [ ] JS `app-script-asset-vendor-inventory-operations.blade.php`: tambah `const aviTopVendor = computed(() => aviSummaryChips.value[0] ?? null)`
- [ ] Blade `asset-vendor-inventory.blade.php`: hapus `chips-grid`, tambah card ke-5 menggunakan `aviTopVendor`
- [ ] `app-script-return-block.blade.php`: tambah `aviTopVendor` ke return block, hapus `aviSummaryChips`

---

### 16. keep-barang `[ CONVERT SPECIAL — chips → 4 summary cards ]`

| | Detail |
|-|--------|
| **Cards** | TIDAK ADA (tidak punya summary card section) |
| **Chips** | Total · PENDING · DONE · CANCEL (inline hardcoded di blade, bukan `_sChips()`) |
| **Overlap** | Chips = SATU-SATUNYA summary data. |
| **Aksi** | **CONVERT** chips-grid → proper summary card section (4 cards) |

JS: `keepBarangSummary` sudah ada `{ total, pending, done, cancel }` — tidak perlu ubah computed, hanya markup.

- [ ] Blade `keep-barang.blade.php`: ganti `chips-grid` block (lines 123–136) dengan `summary-cards-grid` menggunakan `dashboard-summary-card-compact`

---

## Urutan Eksekusi

- [ ] **Batch 1 — DELETE** (master-plan, unboxing, orderan, claim-garansi): hanya hapus code, paling aman
- [ ] **Batch 2 — keep-barang**: markup-only, tidak ada JS computed yang berubah
- [ ] **Batch 3 — harga**: JS restructure terbesar (6 cards dari 4), kerjakan sendiri
- [ ] **Batch 4 — CONVERT cluster** (ideation, distribution, analytics, story, promo, unitDitanya, lpjk): semua di file computed cluster yang sama
- [ ] **Batch 5 — Meta** (meta-story, meta-feed): file JS berbeda
- [ ] **Batch 6 — AVI**: computed di file berbeda + return block update
- [ ] **Final cleanup**: cek apakah `_sChips` masih dipakai di tempat lain, hapus jika tidak

---

## Catatan Implementasi

- Pattern card baru harus mengikuti struktur `dashboard-summary-card-compact` yang sudah ada
- Card ke-5 pada menu yang sebelumnya punya 4 card: loop `v-for="c in xyzSummary.cards"` di blade akan auto-render tanpa perlu ubah blade loop
- **Khusus AVI**: tidak pakai `{ cards, chips }` pattern — chip terpisah. Perlu tambah card ke blade secara manual (tidak ada cards loop)
- **Khusus keep-barang**: tidak ada `{ cards, chips }` computed. Perlu tambah `section-summary-grid` block di blade sebelum content table
- Setelah semua selesai, `_sChips` helper function di computed cluster bisa dihapus jika tidak ada yang pakai lagi
