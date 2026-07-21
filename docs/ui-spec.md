# UI Specification — Design Token & Component Dimensions

> Source of truth untuk semua ukuran UI. Buat komponen baru → ikuti spec ini, bukan tebak-tebakan.
> Audit date: 2026-07-10

---

## Prinsip: Symmetrical Design

Semua nilai dimensi mengikuti dua aturan:

### 1. 4pt Grid
Setiap nilai spacing, padding, gap, border-radius harus **habis dibagi 4**.

```
✓ Valid:   4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56px
✗ Invalid: 6, 10, 14, 18 (ganjil terhadap grid), 9, 11, 13px
```

> Font size dikecualikan dari 4pt grid — font pakai golden ratio scale sendiri (Section 0).

### 2. Axis Symmetry
Padding harus simetris per-axis: **top = bottom**, **left = right**.

```css
/* ✓ Simetris */
padding: 12px 16px;   /* top=bottom=12, left=right=16 */
padding: 16px;        /* sama semua */

/* ✗ Tidak simetris */
padding: 10px 16px 12px 16px;  /* top ≠ bottom */
padding: 1rem 1.1rem;          /* left ≠ right */
```

> Satu-satunya pengecualian axis: `padding-left` lebih besar karena ada icon di dalam (input dengan icon kiri). Ini intentional dan diizinkan.

### 3. Implikasi: nilai yang harus dihapus

| Nilai lama | Masalah | Ganti dengan |
|-----------|---------|-------------|
| 6px | bukan 4pt | 8px |
| 10px | bukan 4pt | 8px (compact) atau 12px (standard) |
| 14px | bukan 4pt | 12px (compact) atau 16px (standard) |
| `1rem 1.1rem` | left ≠ right | `16px` (equal) |
| `1.1rem` ≈ 17.6px | bukan 4pt | 16px atau 20px |

---

## 0. Typography Scale

14 ukuran ad-hoc dipadatkan ke **8 token semantik** dalam 4 grup. Setiap grup punya peran visual yang berbeda.

### Grup & Token

| Grup | Token | Size | Ratio (φ=1.618) | Digunakan untuk |
|------|-------|------|-----------------|-----------------|
| **Display** | `--fs-display-lg` | 26px | 16 × φ ≈ 25.9 | KPI value, hero h1, login h1 |
| **Display** | `--fs-display-sm` | 18px | 11 × φ ≈ 17.8 | Topbar title, summary value |
| **Heading** | `--fs-heading-lg` | 16px | 10 × φ ≈ 16.2 | Panel header h3, badge strong |
| **Heading** | `--fs-heading-sm` | 13px | √φ × 11 ≈ 14 → 13 | Section title, table strong |
| **Body** | `--fs-body` | 11px | **base** | Button, nav, form, body text |
| **Body** | `--fs-body-sm` | 10px | base − 1 (bridge) | Meta label, badge, icon label |
| **Overline** | `--fs-overline` | 9px | base ÷ φ ≈ 6.8 → 9 | Eyebrow tag, micro label |
| **Overline** | `--fs-overline-xs` | 8px | base ÷ φ² → floor 8 | Sort arrow, counter pill |

### Rantai golden ratio bersih

```
10 → 16 → 26px   (×φ tiap step)
11 → 18px         (×φ)
```

### Konsolidasi dari ukuran lama

| Ukuran lama | Diganti dengan |
|-------------|---------------|
| 8px | `--fs-overline-xs` |
| 9px, 9.5px | `--fs-overline` |
| 10px, 10.5px | `--fs-body-sm` |
| 11px | `--fs-body` |
| 12px | `--fs-body` *(turun 1px, cek visual)* |
| 13px | `--fs-heading-sm` |
| 15px | `--fs-display-sm` *(naik 3px, mobile summary — cek visual)* |
| 16px | `--fs-heading-lg` |
| 18px | `--fs-display-sm` |
| 24px | `--fs-display-lg` *(naik 2px, cek visual)* |
| 26px | `--fs-display-lg` |

### Aturan penggunaan per grup

**Display** — angka besar, hero, KPI. Tidak untuk copy panjang. Maks 1 per section.

**Heading** — judul section, panel header. Boleh `font-weight: 600+`. Tidak untuk label/badge.

**Body** — semua teks interaktif: button, input, nav, body copy. Default jika ragu → `--fs-body`.

**Overline** — label di atas heading, badge, tag kecil, data tabel compact. Selalu uppercase atau `letter-spacing` jika di atas heading.

### CSS Custom Properties (tambah ke `@theme`)

```css
/* Typography */
--fs-display-lg: 1.625rem;   /* 26px */
--fs-display-sm: 1.125rem;   /* 18px */
--fs-heading-lg: 1rem;       /* 16px */
--fs-heading-sm: 0.8125rem;  /* 13px */
--fs-body:       0.6875rem;  /* 11px — base */
--fs-body-sm:    0.625rem;   /* 10px */
--fs-overline:   0.5625rem;  /* 9px  */
--fs-overline-xs:0.5rem;     /* 8px  */
```

---

## 00. Iconography

### Ukuran icon → ukuran background container

Semua nilai 4pt grid. Padding simetris (sama tiap sisi).

| Tier | Icon size | Padding (tiap sisi) | Background size | 4pt ✓ |
|------|-----------|---------------------|----------------|-------|
| sm | 16px | 8px | **32px** | ✓ |
| md | 20px | 12px | **44px** | ✓ |
| lg | 24px | 16px | **56px** | ✓ |

> Formula: `background = icon + (padding × 2)`
> ~~52px~~ direvisi ke **56px** — padding 14px bukan 4pt grid, naik ke 16px.
> Border radius container mengikuti rumus outer = inner + padding (lihat Section 2).

### Token

```css
/* Icon sizes */
--icon-sm:  16px;
--icon-md:  20px;
--icon-lg:  24px;

/* Icon container (background) — 4pt grid */
--icon-bg-sm: 32px;   /* 16 + 8×2  */
--icon-bg-md: 44px;   /* 20 + 12×2 */
--icon-bg-lg: 56px;   /* 24 + 16×2 */
```

### Pemetaan ke class yang sudah ada

| Class | Icon size | Background | Status |
|-------|-----------|------------|--------|
| `icon-button` | ~16–20px | 36×36px | ❌ bukan 4pt, bukan tier valid — fix ke 32px (sm) atau 44px (md) |
| `modal-header-icon` | ~20px | 40×40px | ❌ bukan 4pt — fix ke 44px (md) |
| nav `nav-icon` | ~16px | dalam nav-item 44px | ⚠ nav-item naik ke 44px (sesuai touch target) |

### Aturan

- Icon **tidak boleh** tampil tanpa container jika berdiri sendiri (standalone icon button)
- Container selalu `display: flex; align-items: center; justify-content: center`
- Pilih satu dari 3 tier (sm/md/lg) — tidak ada ukuran di luar tabel ini

---

## 1. Spacing Tokens

Semua padding, gap, margin harus pakai token ini. **Tidak boleh hardcode angka langsung.**
Semua nilai adalah **4pt grid** (habis dibagi 4).

| Token | Value | Use |
|-------|-------|-----|
| `--space-1` | 4px | Micro gap (icon ke label dalam badge kecil) |
| `--space-2` | 8px | Default gap (button icon-text, table cell Y, compact button gap) |
| `--space-3` | 12px | Compact padding (compact button/input X, small button X, nav subitem, modal footer gap) |
| `--space-4` | 16px | Default padding (primary button X, form input, card, panel) |
| `--space-5` | 20px | Large padding (nav item X, module header Y) |
| `--space-6` | 24px | Section gap (module header gap, modal header padding) |
| `--space-7` | 32px | Page-level spacing |
| `--space-8` | 40px | Hero/display spacing |
| `--space-9` | 48px | Max form field height |
| `--space-10` | 56px | Extra large (jarang dipakai) |

> Nilai 6, 10, 14px **dihapus** karena bukan 4pt grid. Lihat tabel konversi di bagian Prinsip.

> **Target @theme block (`app.css`):**
> ```css
> --space-1: 4px;  --space-2: 8px;  --space-3: 12px; --space-4: 16px;
> --space-5: 20px; --space-6: 24px; --space-7: 32px; --space-8: 40px;
> --space-9: 48px; --space-10: 56px;
> ```

---

## 2. Border Radius Tokens

Saat ini: 5 nilai berbeda tanpa sistem (8, 12, 16, 20, 24px). Target:

| Token | Value | Use |
|-------|-------|-----|
| `--radius-sm` | 8px | Dense cards (kpi-card, module-header) |
| `--radius-md` | 12px | Compact buttons, compact inputs, nav items |
| `--radius-lg` | 16px | Standard inputs, form buttons, info-panel, modal icon |
| `--radius-xl` | 20px | Summary cards (dashboard-summary-card-compact) |
| `--radius-2xl` | 24px | Empty states, large surfaces |
| `--radius-full` | 9999px | Pills, chips, segmented filter |

### Rumus: Outer Radius

```
outer-radius = inner-radius + padding
```

Container yang wrap elemen lain → radius container harus mengikuti rumus ini agar sudut luar dan dalam terlihat konsentris (tidak ada efek "radius bertabrakan").

**Contoh dalam project ini:**

| Container | Padding | Inner element radius | Outer radius seharusnya | Sekarang | Status |
|-----------|---------|---------------------|------------------------|----------|--------|
| `kpi-card` | 16px | button/badge ~12px | 12 + 16 = **28px** | 8px | ❌ fix |
| `dashboard-summary-card-compact` | ~16px | text (no inner radius) | N/A | 20px | ✓ ok |
| `info-panel-soft` | 12px 16px | text only | N/A | 16px | ✓ ok |
| `module-header` | 20px | button 12px | 12 + 20 = **32px** | 8px | ❌ fix |
| Modal container | 24px | button 12px | 12 + 24 = **36px** | tidak diset | ⚠ cek |
| `segmented-control` | ~4px | item 12px | 12 + 4 = **16px** | tidak diset | ⚠ cek |
| Form field group | 16px | input 12px | 12 + 16 = **28px** | tidak diset | ⚠ cek |

**Aturan praktis:**

- Elemen **innermost** (button, input, badge) → pakai `--radius-md` atau `--radius-lg`
- Container langsung wrapping → `inner-radius + padding` = tambah token terdekat
- Jika container tidak punya inner element dengan radius → bebas pilih token sesuai konteks

**Implikasi pada token:**

Perlu tambah token untuk hasil rumus umum:

| Token | Value | Rumus | Use |
|-------|-------|-------|-----|
| `--radius-card` | 28px | 12 + 16 | Card yang contain button/badge dengan padding 16px |
| `--radius-module` | 32px | 12 + 20 | Module-header / section container padding 20px |
| `--radius-modal` | 36px | 12 + 24 | Modal dialog container |

---

## 3. Z-Index Scale

| Token | Value | Use |
|-------|-------|-----|
| `--z-base` | 0 | Normal flow |
| `--z-raised` | 10 | Cards hover state |
| `--z-dropdown` | 100 | Popover, select dropdown |
| `--z-sidebar` | 200 | Sidebar overlay (mobile) |
| `--z-topbar` | 300 | Topbar (above sidebar content) |
| `--z-modal` | 400 | Modal backdrop + dialog |
| `--z-toast` | 500 | Alert/toast (above modal) |

---

## 4. Button Spec

### Tinggi (height tier)

| Tier | min-height | Digunakan pada |
|------|-----------|----------------|
| **micro** | 28px | calendar-footer-action |
| **compact** | 34px | small-button, ghost-button, nav-subitem touch |
| **icon** | 36px | icon-button, select-compact, date-compact |
| **default** | 40px | primary, secondary, filter, select, reset ← semua default |
| **modal** | 40px | modal-primary, modal-secondary ← *fix dari 42px → standar ke 40* |
| **form** | 48px | select-form, date-trigger, form input height |

> **Aturan:** Semua button dalam satu konteks harus satu tier. Jangan campur 40 dan 42 dalam satu toolbar/modal.

### Dimensi per class

| Class | min-height | padding | border-radius | font-size | gap |
|-------|-----------|---------|---------------|-----------|-----|
| `primary-cta-button` | 40px | 0 16px | --radius-md | --fs-sm (11px) | 8px |
| `primary-cta-button` | 40px | 0 14px | --radius-md | --fs-xs (10px) | 8px |
| `small-button` | 34px | 0 12px | --radius-md | --fs-sm (11px) | 6px |
| `ghost-button` | 34px | 0 12px | --radius-md | --fs-sm (11px) | 6px |
| `icon-button` | 36px | — | --radius-md | — | — |
| `icon-button` width/height | 36×36px | — | — | — | — |
| `filter-trigger-button` | 40px | 10px 16px | --radius-md | --fs-sm | 8px |
| `select-trigger-button` | 40px | 10px 16px | --radius-md | --fs-sm | 8px |
| `select-trigger-button-compact` | 36px | 8px 12px | --radius-md | --fs-sm | 6px |
| `select-trigger-button-form` | 48px | 12px 16px | --radius-lg | --fs-sm | 8px |
| `select-trigger-button-form-tight` | 48px | 12px | --radius-lg | --fs-sm | 6px |
| `date-trigger-button` | 48px | 12px 16px | --radius-lg | --fs-sm | 8px |
| `date-trigger-button-compact` | 36px | 8px 12px | --radius-md | --fs-sm | 6px |
| `reset-filter-button` | 40px | 0 14px | --radius-md | --fs-sm | 8px |
| `modal-primary-button` | 40px *(fix 42→40)* | 0 20px | --radius-md | --fs-sm | 8px |
| `modal-secondary-button` | 40px *(fix 42→40)* | 0 20px | --radius-md | --fs-sm | 8px |
| `segmented-filter-button` | — | 8px 10px | --radius-full | --fs-xs | — |
| `segmented-control__item` | — | 8px 16px | --radius-md | --fs-sm | — |
| `calendar-footer-action` | 28px | 6px 10px | --radius-md | --fs-micro | — |

---

## 5. Input / Form Field Spec

| Class | Konteks | min-height | padding | border-radius | font-size |
|-------|---------|-----------|---------|---------------|-----------|
| `form-input` | Form modal/page | 48px | 12px 16px | --radius-lg | --fs-sm |
| `form-input-auth` | Login page | 48px | 16px 16px 16px 40px | --radius-lg | --fs-sm |
| `form-input-compact` | Inline form | 40px | 10px 16px | --radius-md | --fs-sm |
| `form-input-compact-white` | Compact white bg | 36px | 8px 12px | --radius-md | --fs-sm |
| `form-input-search` | Search with icon | 40px | 10px 16px 10px 36px | --radius-md | --fs-sm |
| `form-input-popover` | Inside popover | 36px | 8px 16px 8px 36px | --radius-md | --fs-sm |
| `form-textarea` | Multi-line | auto | 12px 16px | --radius-lg | --fs-sm |

> **Aturan:** Icon di dalam input → `padding-left: 36px` (compact) atau `40px` (form).

---

## 6. Table Spec

### Padding sel

| Konteks | Header padding | Cell padding | Header font | Cell font |
|---------|---------------|--------------|-------------|-----------|
| `data-table` (standard) | 12px 14px | 13px 14px | --fs-xs (10px) | --fs-md (12px→13px) |
| `#app table` (compact) | 8px 12px | 6px 12px | --fs-xs (10px) | --fs-micro (9px) |
| Mobile (`max-width: 767px`) | 6px 10px | 5px 10px | --fs-xs | --fs-nano (8px) |

### Ukuran lain

| Element | Value |
|---------|-------|
| Row min-height (standard) | 44px *(implicit dari cell padding + font)* |
| Row min-height (compact) | 32px |
| `table-empty-state` padding | 2.5rem (40px) |
| `table-empty-state` border-radius | --radius-2xl (24px) |
| `mobile-data-card` gap | 12px |

---

## 7. Card / Panel Spec

| Class | padding | border-radius | min-height | Keterangan |
|-------|---------|---------------|-----------|------------|
| `kpi-card` | 16px | --radius-sm (8px) | — | Dense KPI |
| `dashboard-summary-card-compact` | 1rem 1.1rem (16px 17.6px) | --radius-xl (20px) | 5.5rem (88px) | Summary strip |
| `info-panel-soft` | 12px 16px | --radius-lg (16px) | — | Informational |
| `panel-header` | 16px | — | — | Section header bar |
| `module-header` | 20px | --radius-sm (8px) | — | Page hero area |

---

## 8. Modal Spec

| Ukuran modal | max-width | Digunakan untuk |
|-------------|-----------|-----------------|
| `modal-width-compact` | 28rem (448px) | Konfirmasi, alert kecil |
| `modal-width-form` | 44rem (704px) | Form tambah/edit |
| `modal-width-detail` | 56rem (896px) | Detail data |
| `modal-width-wide` | 72rem (1152px) | Tabel dalam modal |

| Element | Value |
|---------|-------|
| `modal-header-bar` padding | 1.5rem (24px) |
| `modal-footer-bar` padding | 16px 24px |
| `modal-footer-bar` gap | 12px |
| `modal-header-icon` size | 40×40px, --radius-lg |

---

## 9. Navigation / Sidebar Spec

| Element | Value |
|---------|-------|
| Sidebar width | 240px |
| Topbar height | 64px |
| Brand row height | 64px |
| `nav-item` min-height | 42px *(target: 44px untuk touch)* |
| `nav-item` padding | 0 20px |
| `nav-item` gap | 12px |
| `nav-item` font-size | --fs-sm (11px) |
| `nav-subitem` min-height | 34px |
| `nav-subitem` padding-left | 40px (indent) |
| `nav-subitem` font-size | --fs-xs (10px) *(fix dari 10.5px)* |

> **Catatan:** `nav-item` min-height 42px di bawah standar touch target 44px. Naikkan ke 44px di Fase B.

---

## 10. Inkonsistensi yang Harus Diperbaiki

| # | Masalah | Nilai sekarang | Target |
|---|---------|---------------|--------|
| I1 | `modal-primary/secondary-button` tinggi berbeda dari default | 42px | 40px |
| I2 | `kpi-card` border-radius berbeda dari `module-header` padahal konteks sama | 8px ✓ | keduanya --radius-sm |
| I3 | `nav-subitem` font-size tanggung | 10.5px | --fs-xs (10px) |
| I4 | `primary-cta-button` font-size berbeda dari primary | 10px | --fs-sm (11px) |
| I5 | Tidak ada spacing token — semua angka hardcode | — | Tambah --space-* |
| I6 | `dashboard-summary-card-compact` pakai rem campur | 5.5rem, 1rem 1.1rem | konversi ke px via token |
| I7 | `nav-item` min-height di bawah touch target | 42px | 44px |
| I8 | `table-empty-state` padding pakai rem | 2.5rem | var(--space-11) |
| I9 | `kpi-card` radius tidak ikuti rumus outer = inner + padding | 8px (harusnya 28px) | --radius-card (28px) |
| I10 | `module-header` radius tidak ikuti rumus | 8px (harusnya 32px) | --radius-module (32px) |
| I11 | `icon-button` size 36px — bukan 4pt tier valid | 36px | 32px (sm) atau 44px (md) |
| I12 | `modal-header-icon` size 40px — bukan 4pt tier valid | 40px | 44px (md) |
| I13 | `dashboard-summary-card-compact` padding `1rem 1.1rem` — axis tidak simetris, bukan 4pt | 16px 17.6px | 16px (equal) |
| I14 | `select-trigger-button-form-tight` gap 6px — bukan 4pt | 6px | 8px |
| I15 | `filter-trigger-button` padding Y 10px — bukan 4pt | 10px 16px | 12px 16px |
| I16 | `primary-cta-button` padding X 14px — bukan 4pt | 0 14px | 0 16px |
| I17 | `reset-filter-button` padding X 14px — bukan 4pt | 0 14px | 0 16px |

---

## 11. Aturan Buat Menu Baru

Checklist wajib sebelum merge menu baru:

- [ ] Tidak ada `style="..."` statis di blade (inline style)
- [ ] Tidak ada `<style>` tag di blade
- [ ] Tidak ada `font-size: Npx` literal — pakai `--fs-*` token
- [ ] Semua padding/gap referensikan `--space-*` token
- [ ] Border radius pakai `--radius-*` token
- [ ] Button heights mengikuti tier di Section 4
- [ ] Input padding mengikuti Section 5
- [ ] Breakpoint pakai `@media (min-width: var(--breakpoint-*))` — bukan angka langsung
- [ ] Tidak ada `text-[Npx]` arbitrary Tailwind class
