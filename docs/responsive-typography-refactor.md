# Responsive & Typography Refactor Plan

> Audit date: 2026-07-10 · Status: IN PROGRESS
> Lihat juga: [ui-spec.md](ui-spec.md) — source of truth ukuran semua komponen

---

## 0. Goals

| # | Goal | Ukuran Sukses |
|---|------|---------------|
| G1 | **Type scale sistematis** — 14 ukuran ad-hoc → 8 token golden ratio | Zero `font-size: Npx` literal di CSS (kecuali definisi token) |
| G2 | **Zero inline style** — tidak ada `style="..."` di Blade kecuali nilai dinamis yang benar-benar runtime | Grep di bawah → 0 hasil (exclude `design-system.blade.php`) |
| G3 | **Mobile-first breakpoints** — semua `@media` pakai `min-width` only | `grep 'max-width' resources/css/` → 0 hasil |
| G4 | **Single breakpoint set** — tidak ada magic number breakpoint di luar token | Semua angka breakpoint referensikan `--breakpoint-*` token |
| G5 | **No arbitrary Tailwind font** — tidak ada `text-[Npx]` di Blade | `grep -r 'text-\[' resources/views/` → 0 hasil |
| G6 | **Responsive coverage** — semua halaman berfungsi di 375/768/1080/1440px | QA checklist fase E lulus semua |
| G7 | **Global-first component system** — menu baru cukup compose class yang sudah ada, zero CSS baru per menu | Buat menu baru tanpa tambah satu baris pun di CSS file |

### Prinsip G7 — Global-First

Menu baru harus bisa dibuat **hanya dari HTML + class**, seperti:

```html
<!-- Contoh menu baru — zero CSS baru -->
<div class="module-header"> ... </div>
<div class="dashboard-summary-grid-compact"> ... </div>
<div class="data-table-shell"> ... </div>
```

Supaya ini bisa, semua pattern UI harus:
1. Tinggal di `dashboard-shell.css` (global) — bukan di `<style>` tag per-blade
2. Punya nama class yang deskriptif dan konsisten
3. Terdokumentasi di component catalog (lihat Fase F)

### Prioritas

```
G2 (inline style) + G3 (mobile-first) → G1 (type scale) → G4 + G5 → G7 (global system) → G6 (QA)
```

> **G2 paling penting:** Inline style bypass semua token dan membuat responsive override mustahil tanpa `!important`.
> **G7 goal akhir:** Developer tidak perlu buka CSS file sama sekali saat buat menu baru.

**Verify G2 (gunakan grep ini, bukan versi tanpa exclude):**
```bash
grep -r 'style="' resources/views/ --include="*.blade.php" \
  | grep -v ':style=' \
  | grep -v 'design-system.blade.php'
```

> **Catatan scope:** Dari scan awal, `views/reference/design-system.blade.php` punya 119 inline style — intentional karena itu file showcase/dokumentasi komponen. File UI real hanya **6 file** dengan masing-masing 1 inline style. Scope G2 jauh lebih kecil dari perkiraan.

---

## 1. Typography Audit — Current State

### Font sizes found (px, raw count)

| Size | Count | Where |
|------|-------|-------|
| 8px | 3 | table sort arrow, settings counter, compact card |
| 9px | 4 | brand subtitle, calendar footer, user-meta span, type-micro |
| 9.5px | 1 | table tbody/tfoot (mobile) |
| 10px | 14+ | meta labels, nav icon, chevron, badges, type-meta |
| 10.5px | 1 | nav-subitem |
| 11px | 20+ | body/button/nav/form default — **de facto base** |
| 12px | 5 | input, avatar, search, data-table td |
| 13px | 2 | status-list strong, type-title |
| 15px | 1 | summary value mobile |
| 16px | 2 | hero-badge strong, panel-header h3 |
| 18px | 2 | topbar h2, dashboard-summary-value |
| 24px | 1 | login panel h1 |
| 26px | 2 | dashboard-hero h1, kpi-card strong |

**Problem:** 14 distinct sizes in 8–26px range. No system. Mix of `px` and `rem`.

---

## 2. Golden Ratio Type Scale — Semantic Groups

φ = 1.618 · Anchor: 11px (de facto base)

| Grup | Token | Value | Ratio | Use |
|------|-------|-------|-------|-----|
| Display | `--fs-display-lg` | 26px | 16 × φ ≈ 25.9 | KPI value, hero h1, login h1 |
| Display | `--fs-display-sm` | 18px | 11 × φ ≈ 17.8 | Topbar title, summary value |
| Heading | `--fs-heading-lg` | 16px | 10 × φ ≈ 16.2 | Panel header h3, badge strong |
| Heading | `--fs-heading-sm` | 13px | √φ × 11 ≈ 14 → 13 | Section title, table strong |
| Body | `--fs-body` | 11px | **base** | Button, nav, form, body text |
| Body | `--fs-body-sm` | 10px | base − 1 | Meta label, badge, icon label |
| Overline | `--fs-overline` | 9px | base ÷ φ ≈ 6.8 → 9 | Eyebrow, micro label |
| Overline | `--fs-overline-xs` | 8px | base ÷ φ² → floor 8 | Sort arrow, counter pill |

**Chain bersih:** `10 → 16 → 26px` dan `11 → 18px` (masing-masing ×φ)

**Eliminasi:** 10.5, 12, 15, 24px → ke token terdekat

### CSS Custom Properties (target: app.css `@theme`)

```css
/* Typography */
--fs-display-lg:  1.625rem;   /* 26px */
--fs-display-sm:  1.125rem;   /* 18px */
--fs-heading-lg:  1rem;       /* 16px */
--fs-heading-sm:  0.8125rem;  /* 13px */
--fs-body:        0.6875rem;  /* 11px — base */
--fs-body-sm:     0.625rem;   /* 10px */
--fs-overline:    0.5625rem;  /* 9px  */
--fs-overline-xs: 0.5rem;     /* 8px  */
```

---

## 3. Responsive Audit — Current State

### Breakpoints found

| Query | Type | Location | Issue |
|-------|------|----------|-------|
| `min-width: 640px` | mobile-first ✓ | dashboard-shell.css:1269, 1842 | OK |
| `max-width: 767px` | desktop-first ✗ | dashboard-shell.css:1091, 1432, 2145 | Inconsistent |
| `min-width: 768px` | mobile-first ✓ | dashboard-shell.css:766, 851, 1586, 2138 | OK |
| `max-width: 768px` | desktop-first ✗ | app.css:929 | Inconsistent |
| `max-width: 1080px` | desktop-first ✗ | app.css:919 | Inconsistent |
| `prefers-reduced-motion` | — | both files | OK |
| `print` | — | dashboard-shell.css:2155 | OK |

**Problem:** Mix of `max-width` (desktop-first) and `min-width` (mobile-first). No single breakpoint system.

### Target breakpoint system (mobile-first)

| Name | Min-width | Replaces |
|------|-----------|---------|
| `sm` | 640px | existing 640 |
| `md` | 768px | existing 768, replace max-width:767 with outside-md |
| `lg` | 1080px | replace max-width:1080 |

---

## 4. Checklist — Progres

> Urutan eksekusi: **A0 → A → B → S → C → D → F → E**

---

### Fase A0 — Token Foundation *(kerjakan pertama, blok semua fase lain)*

Tambah semua token ke blok `@theme` di `app.css` sebelum fase lain dimulai.

- [x] **Typography tokens** (8 token semantik) — `app.css :root`
- [x] **Spacing tokens** (10 token, 4pt grid) — `app.css :root`
- [x] **Border radius tokens** (9 token) — `app.css :root`
- [x] **Icon tokens** (6 token) — `app.css :root`
- [x] **Breakpoint tokens** (3 token) — `app.css @theme`
- [x] **Z-index tokens** (7 token) — `app.css :root`

---

### Fase A — Type Scale

- [x] `app.css` — 36 replacements (9, 10, 10.5, 11, 12, 13, 16, 18, 22, 24, 26px → tokens)
- [x] `dashboard-shell.css` — 55 replacements (6.5, 8, 9, 10, 11, 13, 15, 18px + rem values → tokens)
- [x] Blade — 1514 replacements across 41 files (text-[8–26px] → text-body/overline/heading/display)
  - Intentional outliers **tidak diubah**: `text-[120px]` (42×), `text-[14px]` (5×), `text-[7px]` (3×), `text-[20px]` (1×)
  - ~~Tailwind `@theme --font-size-*`~~ → **tidak bisa**: Tailwind v4 tidak resolve `var()` di `@theme` saat build
  - Fix: utility class `.text-body`, `.text-overline` dll ditulis langsung di `dashboard-shell.css` (end of file)
  - Build ulang → utility classes confirmed ada di compiled CSS
- [x] Update `type-*` utility classes — 335 renames di 37 blade files
  - `type-micro` → `type-overline-xs` (1×)
  - `type-meta` → `type-body-sm` (285×)
  - `type-title` → `type-heading-sm` (49×)
  - CSS: tambah `.type-overline-xs`, `.type-overline`, `.type-body-sm`, `.type-heading-sm` + backward-compat alias
  - Verify: 0 sisa old names di blade

---

### Fase B — Responsive Unification ✅ 2026-07-10

- [x] `app.css` — konversi semua `max-width` ke mobile-first
  - [x] `max-width: 1080px` → base `kpi-grid: 1fr`, `@media (min-width: 768px)` 2-col, `@media (min-width: 1080px)` 4-col
  - [x] `max-width: 768px` (sidebar) → base = mobile collapsed + `.sidebar.is-open`, `@media (min-width: 768px)` = expanded
  - [x] `.main-panel` → base `margin-left: 0`, at 768px → `240px`
  - [x] `.icon-button` → base `display: inline-flex`, at 768px → `display: none`
  - [x] `.topbar` padding, `.topbar-actions` gap, `.ghost-button` → mobile defaults + 768px overrides
  - [x] `.page-view` → base `padding: 14px`, at 768px → `24px`
  - [x] `.dashboard-hero, .module-header, .table-toolbar` → base `flex-direction: column; align-items: stretch`, at 768px → `row; center`
  - [x] `.content-grid` → base `1fr`, at 768px → `1.1fr 0.9fr`
- [x] `dashboard-shell.css` — konversi semua `max-width: 767px`
  - [x] Baris 1091 — dashboard-summary compact card sizing → jadikan default, desktop overrides di `@media (min-width: 768px)`
  - [x] Baris 1432 — modal footer bar buttons full-width → jadikan default, desktop revert di `@media (min-width: 768px)`
  - [x] Baris 2157 — compact table padding → jadikan default, desktop padding di `@media (min-width: 768px)`
- [x] Standardisasi urutan media query: **(1) mobile default → (2) sm 640 → (3) md 768 → (4) lg 1080 → (5) print**
- [x] Tidak menyentuh: `prefers-reduced-motion`, `print`
- [x] Verify: `grep '@media.*max-width' resources/css/` → 0 hasil ✓
- [x] Build: `npm run build` → ✓ built in 500ms

---

### Fase S — Symmetrical Fixes *(4pt grid violations)* ✅ 2026-07-10

**app.css:**
- [x] **I7** `nav-item` min-height 42px → 44px (touch target)
- [x] **I9** `kpi-card` border-radius 8px → `var(--radius-card)` (28px); split dari `.panel` (stays `--radius-sm`)
- [x] **I10** `dashboard-hero, module-header` border-radius 8px → `var(--radius-module)` (32px)
- [x] **I11** `icon-button` → `width: var(--icon-bg-md); height: var(--icon-bg-md)` (44px, md tier)
- [x] **I3** nav-subitem 10.5px → `--fs-body-sm` — already done in Fase A ✓
- [x] Audit app.css: `brand-subtitle` margin 6→8, toast `12px 14px`→`12px 16px`, `brand-mark` padding 6→8, `user-card` gap 10→12 pad 14→16, `topbar` gap 14→12 pad 14→12, `topbar-actions` gap 6→8, `page-view` 14→12, hero h1 margin 6→8, `search-shell` gap 6→8, input pad 10→12, `data-table th` `12px 14px`→`12px 16px`, `data-table td` `13px 14px`→`12px 16px`

**dashboard-shell.css:**
- [x] **I1** `modal-secondary/primary-button` min-height 42px → 40px
- [x] **I4** `secondary-cta-button` font-size `--fs-body-sm` → `--fs-body`
- [x] **I8** `table-empty-state` padding `2.5rem` → `var(--space-8)` (40px)
- [x] **I12** `modal-header-icon` 40px → `var(--icon-bg-md)` (44px)
- [x] **I13** `dashboard-summary-card-compact` desktop padding `1rem 1.1rem` → `var(--space-4)` (16px equal)
- [x] **I6** `dashboard-summary-card-compact` mobile base: `4.65rem`→72px, `0.55rem`→`--space-2`, `1.1rem`→`--space-4`; desktop: `5.5rem`→88px; `.dashboard-summary-card` pad→`var(--space-4)`; `.stat-card` pad→`var(--space-4)`
- [x] **I14** `select-trigger-button-form-tight` gap 6px → `var(--space-2)` (8px)
- [x] **I15** `filter-trigger-button` padding Y `10px` → `var(--space-3)` (12px)
- [x] **I16** `secondary-cta-button` padding X `14px` → `var(--space-4)` (16px)
- [x] **I17** `reset-filter-button` padding X `14px` → `var(--space-4)` (16px)
- [x] Audit dashboard-shell.css: modal-footer gap 10→8, table-action gap 6→8, compact border-radius 10→8, icon-utility border-radius 10→8, select pad 10→12, popover compact pad 6→8, period toolbar gap 6→8, segmented filter pad `8px 10px`→`8px 12px`, status-pill pad 6→8, entity-badge pad 10→8, mini-stat-chip gap 6→8, form-input-compact/search pad 10→12, multi-select-chip radius 10→8, calendar-footer pad `6px 10px`→`8px 12px`, mobile-card pads 10→12, sort-arrow margin 6→8, drag-handle margin `10px auto 2px`→`8px auto 4px`, table th 6→4, table td 5→4, desktop td 6→8
- [x] Build: `npm run build` → ✓ 574ms

---

### Fase C — Menu Blade Files ✅ 2026-07-10

> Tiap file: (1) hapus `style="..."` statis → pindah ke CSS class, (2) cek responsive, (3) pastikan hanya pakai class yang ada di global CSS

- [x] `activity-logs.blade.php` — clean. All classes global. Uses sm:/md:.
- [x] `analytics.blade.php` — clean. `text-[120px]` intentional (decorative icon).
- [x] `auth-users.blade.php` — clean. All classes global. Uses sm:/md:.
- [x] `claim-garansi.blade.php` — clean. `text-[120px]` intentional.
- [x] `distribution.blade.php` — clean. `text-[120px]` intentional.
- [x] `keep-barang.blade.php` — clean. All classes global.
- [x] `master-plan.blade.php` — clean. `text-[120px]` intentional.
- [x] `settings.blade.php` — clean. All classes global. Uses md:/lg:/xl:.
- [x] `story.blade.php` — clean. `text-[120px]` intentional.
- [x] `unboxing.blade.php` — clean. `text-[120px]` intentional.
- [x] `unit-ditanya.blade.php` — clean. `text-[120px]` intentional.
- [x] `custom-scrollbar` class tidak ada di CSS → tambah definisi ke `dashboard-shell.css`
- [x] Bonus: fix `style="min-width:Npx"` di 4 non-checklist menu files → `min-w-[Npx]` Tailwind class
- [x] Bonus: fix `style="height:96px"` di `analisa-insight.blade.php` → `h-24`
- [x] G2 check: `grep -r 'style="' resources/views/ | grep -v ':style=' | grep -v design-system` → **1 sisa** (di shell/app-frame-sidebar.blade.php, scope Fase D)
- [x] Build: `npm run build` → ✓ 581ms

---

### Fase D — Shell Templates ✅ 2026-07-10

> Bedakan `:style="..."` Alpine dynamic (boleh) vs `style="..."` statis (harus pindah ke class).

- [x] `app-frame-sidebar.blade.php` — 1 static `style=` found (`transition-timing-function:cubic-bezier(0.22,1,0.36,1)`) → moved to `.dashboard-sidebar-shell` in `dashboard-shell.css`
- [x] `app-frame-sidebar-nav-admin.blade.php` — clean. No static `style=`. Nav items use `px-5 py-3` (48px height) ≥44px ✓
- [x] `app-frame.blade.php` — clean. Sidebar margin via Alpine `:style=` (paddingLeft) — correctly dynamic
- [x] overlay z-index — uses `z-[70]`/`z-[80]` in `:class=` bindings. Note: `--z-sidebar: 200` token defined but unused; actual values are 70/80. Not a G2 violation (not `style=` attrs). Flag for future alignment.
- [x] `app-script-*.blade.php` (40 files) — grep confirms zero `.style` / `setAttribute('style'...)` / `innerHTML` style injection
- [x] G2 final: `grep -r 'style="' resources/views/ | grep -v ':style=' | grep -v design-system` → **0 hasil** ✅
- [x] Build: `npm run build` → ✓ 796ms

---

### Fase F — Global Component System (G7) ✅ 2026-07-10

#### F1 — Inventory pattern di `dashboard-shell.css` ✅

- [x] Layout: `dashboard-sidebar-shell`, `dashboard-main-shell`, `page-view`, `sidebar-accordion-panel`
- [x] Page header: `module-header`, `dashboard-hero`, `hero-badge`
- [x] Cards: `kpi-card`, `panel`, `section-card`/`section-card-body`/`section-card-shell`, `stat-card`, `dashboard-summary-card-compact`, `mobile-data-card`, `settings-*`
- [x] Buttons CTA: `primary-cta-button` (+`--accent`/`--info`), `secondary-cta-button` (+`-success`/`-danger`/`-neutral`/`-link`)
- [x] Buttons icon: `icon-utility-button` (+`-bordered`/`-round`/`-danger`), `icon-button`, `ghost-button`, `table-action-button` (+variants)
- [x] Modal buttons: `modal-primary-button` (+`--info`/`--success`/`--danger`), `modal-secondary-button`
- [x] Filters: `filter-trigger-button`, `select-trigger-button` (+compact/form/form-tight), `toolbar-select-shell`, `reset-filter-button`
- [x] Segmented: `segmented-control` (+`--ios`/`--equal`), `segmented-control__item` (+`--active`)
- [x] Toolbar: `mobile-toolbar-stack`, `toolbar-actions`, `compact-period-toolbar`
- [x] Forms: `form-input`, `form-input-compact`, `form-input-auth`, `form-input-search`, `form-input-popover`, `form-textarea`, `date-trigger-button`, `form-section-card`
- [x] Popover: `search-select-popover` (+`--flip-up`/`--compact`), `search-select-container`, `popover-option`, `multi-select-chip`
- [x] Tables: `data-table`, `data-table-wrap`, `table-toolbar`, `table-empty-state`, `table-pager-bar`, `table-sortable`
- [x] Modals: `mobile-sheet`, `modal-sheet-surface`, `modal-dialog-surface`, `modal-header-bar`, `modal-header-copy`, `modal-header-icon`, `modal-footer-bar`, `modal-width-*`
- [x] Typography: `text-display-lg/sm`, `text-heading-lg/sm`, `text-body/sm`, `text-overline/xs`, `type-*` presets
- [x] Radius utils: `radius-card`, `radius-panel`, `radius-dialog`, `radius-sheet`
- [x] Grid: `kpi-grid`, `content-grid`, `dashboard-summary-grid-compact`
- [x] Animation: `animate-fadeIn`, `motion-stagger-item`, Vue transition classes

#### F2 — Migrasi `<style>` blade tag → `dashboard-shell.css` ✅

- [x] `grep -r '<style>' resources/views/` → 2 hasil: `views/db/tables.blade.php` + `views/db/table-preview.blade.php`
- [x] Keduanya standalone DB debug pages — tidak load design system CSS. **Exempt, tidak perlu migrasi.**

#### F3 — Component catalog ✅

- [x] `docs/component-catalog.md` dibuat — 20 section, semua pattern + HTML snippet

#### F4 — Naming convention audit ✅ (documented, rename deferred)

- [x] Audit: 14 class modifier pakai `-` bukan `--` (contoh: `secondary-cta-danger` → `secondary-cta-button--danger`)
- [x] Dokumentasi di `docs/component-catalog.md` bagian F4
- [ ] Rename batch (deferred — safe after visual QA Fase E selesai)

---

### Fase E — QA ✅ 2026-07-10 (grep goals), visual pending

- [ ] Visual test 375px (iPhone SE) — semua menu
- [ ] Visual test 768px (iPad portrait) — semua menu
- [ ] Visual test 1080px (laptop) — semua menu
- [ ] Visual test 1440px (desktop) — semua menu
- [ ] `prefers-reduced-motion` masih aktif
- [ ] Print stylesheet tidak broken
- [x] Verify goals:
  - [x] G1: `grep 'font-size:.*px' resources/css/ | grep -v '@theme\|--fs-'` → **0 hasil** ✅
  - [x] G2: `grep -r 'style="' resources/views/ | grep -v ':style=' | grep -v design-system` → **0 hasil** ✅
  - [x] G3: `grep '@media.*max-width' resources/css/` → **0 hasil** ✅
  - [x] G5: `grep -r 'text-\[' resources/views/ | grep -v text-\[120px\]` → **0 hasil** ✅ (fixed: calendar `text-[14px]`→`text-heading-sm`, `text-[7px]`→`text-overline-xs`; budgeting `text-[14px]`×4→tokens, `text-[20px]`→`text-display-sm`; master-plan `text-[7px]`→`text-overline-xs`)
- [x] Build: `npm run build` → ✓ 625ms

---

## 5. Ukuran Perubahan Estimasi

| Scope | Items |
|-------|-------|
| `@theme` token baru | ~35 token |
| `app.css` font-size declarations | ~20 |
| `dashboard-shell.css` font-size declarations | ~60 |
| `dashboard-shell.css` spacing/padding non-4pt | ~15 |
| `dashboard-shell.css` border-radius fixes | ~5 |
| `dashboard-shell.css` icon-button/modal-header-icon | 2 |
| Blade inline style removals | TBD (perlu scan) |
| Blade arbitrary `text-[Npx]` | ~5 |
| Breakpoint conversions | 10 |
| **Total CSS declarations** | **~117** |

---

## Catatan

- `6.5px` di mobile compact card → `var(--fs-overline-xs)` (8px, naik sedikit — cek visual)
- `0.58rem` = 9.28px → `var(--fs-overline)` (9px)
- `nav-subitem` 10.5px → `var(--fs-body-sm)` (10px), cek alignment dengan nav-item
- `15px` mobile summary → `var(--fs-display-sm)` (18px) terlalu besar? alternatif `var(--fs-heading-lg)` (16px) — putuskan saat eksekusi
- `icon-button` tier: pilih sm(32px) untuk toolbar icons, md(44px) untuk action yang lebih prominent
- Jangan sentuh `dashboard-shell.css:1276` (prefers-reduced-motion) dan `:2155` (print)
- Fase S bisa dikerjakan paralel dengan Fase A **hanya setelah A0 selesai**
- `design-system.blade.php` — exclude dari semua G2 grep, file ini intentional showcase
- `type-*` rename: blade dulu, CSS setelah semua blade sudah diupdate — jangan terbalik
