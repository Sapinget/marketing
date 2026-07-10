# Component Catalog

> Source of truth untuk semua class global di design system.
> Lihat juga: [ui-spec.md](ui-spec.md) — token sizes. [responsive-typography-refactor.md](responsive-typography-refactor.md) — refactor log.
>
> Audit date: 2026-07-10

---

## Prinsip G7

Menu baru harus bisa dibuat **hanya dari HTML + class** yang ada di sini. Zero CSS baru per menu.

---

## 1. Shell & Layout

| Class | Source | Notes |
|-------|--------|-------|
| `dashboard-sidebar-shell` | dashboard-shell.css | Fixed sidebar, GPU-accelerated transition |
| `dashboard-sidebar-content` | dashboard-shell.css | Opacity fade on sidebar collapse |
| `dashboard-main-shell` | dashboard-shell.css | Main content area, paddingLeft driven by Alpine |
| `page-view` | app.css | `p-3 md:p-6` — main content padding |
| `sidebar-accordion-panel` | dashboard-shell.css | Animated accordion container |

```html
<!-- Shell skeleton -->
<aside class="dashboard-sidebar-shell ...">
  <div class="dashboard-sidebar-content ...">...</div>
</aside>
<div class="dashboard-main-shell ...">
  <main class="page-view">...</main>
</div>
```

---

## 2. Navigation

| Class | Source | Notes |
|-------|--------|-------|
| `nav-list` | app.css | Nav container |
| `nav-item` | app.css | Top-level nav item, min-height 44px |
| `nav-group-toggle` | app.css | Accordion trigger, min-height 44px |
| `nav-subitem` | app.css | Sub-nav item, min-height 44px |
| `nav-icon` | app.css | Icon container within nav item |
| `nav-item.is-active` | app.css | Active state |
| `nav-subitem.is-active` | app.css | Active sub-item state |
| `sidebar-nav-item-active` | dashboard-shell.css | Gradient active background |
| `sidebar-accordion-panel` | dashboard-shell.css | Accordion content panel |

---

## 3. Shell Header

| Class | Source | Notes |
|-------|--------|-------|
| `topbar` | app.css | Fixed top bar, h-16 |
| `topbar-actions` | app.css | Right side button group |
| `icon-button` | app.css | Hamburger / topbar icon, 44×44px |
| `ghost-button` | app.css | Text-only topbar action |
| `menu-toggle` | app.css | Hamburger wrapper |
| `menu-toggle-lines` | app.css | 3-bar hamburger lines |
| `brand-row` | app.css | Sidebar logo row |
| `brand-mark` | app.css | Logo container |
| `user-card` | app.css | Sidebar user profile card |
| `avatar` | app.css | User avatar circle |
| `user-meta` | app.css | Name + role text next to avatar |

---

## 4. Page Header

| Class | Source | Notes |
|-------|--------|-------|
| `module-header` | app.css | Page header row (mobile: col, desktop: row) |
| `dashboard-hero` | app.css | Homepage hero (same layout as module-header) |
| `hero-badge` | app.css | Right-aligned badge block in hero |

```html
<div class="module-header">
  <div>
    <h1>Judul Halaman</h1>
    <p>Deskripsi singkat</p>
  </div>
  <div class="hero-badge">
    <span>Label</span>
    <strong>Nilai</strong>
  </div>
</div>
```

---

## 5. Buttons — CTA

| Class | Modifier | Notes |
|-------|----------|-------|
| `primary-cta-button` | — | Default blue primary |
| `primary-cta-button` | `primary-cta-button--accent` | Accent color variant |
| `primary-cta-button` | `primary-cta-button--info` | Info/teal variant |
| `secondary-cta-button` | — | Default outline secondary |
| `secondary-cta-button` | `secondary-cta-success` | Green outline |
| `secondary-cta-button` | `secondary-cta-danger` | Red outline |
| `secondary-cta-button` | `secondary-cta-neutral` | Slate outline |
| `secondary-cta-button` | `secondary-cta-link` | Link-style |
| `small-button` | — | Compact text button |
| `dark-button` | — | Dark/black CTA (login) |

```html
<button class="primary-cta-button primary-cta-button--accent active:scale-95">
  <i class="fa-solid fa-plus"></i> Tambah
</button>
<button class="secondary-cta-button secondary-cta-danger active:scale-95">
  <i class="fa-solid fa-file-pdf"></i> PDF
</button>
```

> F4 note: `secondary-cta-success/danger/neutral` should ideally be `secondary-cta-button--success` etc. Deferred rename (wide blade usage).

---

## 6. Buttons — Icon & Utility

| Class | Modifier | Notes |
|-------|----------|-------|
| `icon-utility-button` | — | Square icon button, 32×32px |
| `icon-utility-button` | `icon-utility-bordered` | With border |
| `icon-utility-button` | `icon-utility-round` | Circular |
| `icon-utility-button` | `icon-utility-danger` | Danger on hover |
| `modal-primary-button` | — | Modal confirm button, min-height 40px |
| `modal-primary-button` | `modal-primary-button--info` | Teal |
| `modal-primary-button` | `modal-primary-button--success` | Green |
| `modal-primary-button` | `modal-primary-button--danger` | Red |
| `modal-secondary-button` | — | Modal cancel button, min-height 40px |
| `reset-filter-button` | — | Clear filter / reset button |

```html
<button class="icon-utility-button icon-utility-bordered" aria-label="Prev">
  <i class="fa-solid fa-chevron-left text-body-sm"></i>
</button>
```

---

## 7. Buttons — Table Actions

| Class | Modifier | Notes |
|-------|----------|-------|
| `table-action-button` | — | Default table row action (28×28px) |
| `table-action-button` | `table-action-compact` | Smaller (24×24px) |
| `table-action-button` | `table-action-danger` | Red on hover |
| `table-action-button` | `table-action-link` | Blue link style |
| `table-action-button` | `table-action-view` | View action |
| `table-action-label` | — | Text label button in table |

```html
<div class="flex items-center gap-2">
  <button class="table-action-button table-action-compact" aria-label="Edit">
    <i class="fa-solid fa-pen text-overline"></i>
  </button>
  <button class="table-action-button table-action-compact table-action-danger" aria-label="Hapus">
    <i class="fa-solid fa-trash text-overline"></i>
  </button>
</div>
```

> F4 note: `table-action-compact/danger/link` should be `table-action-button--compact` etc. Deferred rename.

---

## 8. Filter & Select Controls

| Class | Modifier | Notes |
|-------|----------|-------|
| `filter-trigger-button` | — | Filter popover trigger |
| `select-trigger-button` | — | Select popover trigger |
| `select-trigger-button-compact` | — | Compact variant (period toolbar) |
| `select-trigger-button-form` | — | Form field variant |
| `select-trigger-button-form-tight` | — | Tight form variant (reduced gap) |
| `toolbar-select-shell` | — | Native `<select>` wrapper with custom icon |
| `toolbar-native-select` | — | Styled native `<select>` inside toolbar |
| `toolbar-select-shell__icon` | — | Chevron icon inside toolbar-select-shell |
| `search-select-popover` | — | Floating option list |
| `search-select-popover` | `search-select-popover--flip-up` | Opens upward |
| `search-select-popover` | `search-select-popover--compact` | Compact padding |
| `search-select-container` | — | Relative wrapper for popover positioning |
| `popover-option` | — | Single option in popover |
| `popover-option` | `popover-option-active` | Selected state |
| `popover-option-check` | — | Option with checkbox |
| `multi-select-chip` | — | Selected multi-select tag |

```html
<div class="relative search-select-container">
  <button type="button" @click="toggle" class="select-trigger-button select-trigger-button-compact">
    <span>Bulan</span>
    <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
  </button>
  <div v-if="open" class="search-select-popover search-select-popover--compact">
    <div @click="select(opt)" class="popover-option">{{ opt }}</div>
  </div>
</div>
```

---

## 9. Segmented Control

| Class | Modifier | Notes |
|-------|----------|-------|
| `segmented-control` | — | Default segmented control |
| `segmented-control` | `segmented-control--ios` | iOS pill style with sliding indicator |
| `segmented-control` | `segmented-control--equal` | Equal-width items |
| `segmented-control__item` | — | Single segment button |
| `segmented-control__item` | `segmented-control__item--active` | Active segment |

```html
<div class="segmented-control segmented-control--ios" :data-index="activeIdx">
  <button class="segmented-control__item" :class="{'segmented-control__item--active': activeIdx===0}" @click="activeIdx=0">Tab A</button>
  <button class="segmented-control__item" :class="{'segmented-control__item--active': activeIdx===1}" @click="activeIdx=1">Tab B</button>
</div>
```

---

## 10. Toolbar Layout

| Class | Notes |
|-------|-------|
| `mobile-toolbar-stack` | Vertical on mobile, horizontal on md+ |
| `toolbar-actions` | Button group at end of toolbar |
| `compact-period-toolbar` | Period label + controls combo (small) |
| `compact-period-toolbar__controls` | Controls side of compact period toolbar |
| `toolbar-trigger-field` | Trigger button styled as form field |
| `toolbar-trigger-field-form` | Form variant |

```html
<div class="mobile-toolbar-stack">
  <div class="relative flex-1 sm:w-48">
    <input class="form-input-search" type="text" placeholder="Cari..." />
  </div>
  <div class="toolbar-actions">
    <button class="primary-cta-button primary-cta-button--accent">Tambah</button>
    <button class="secondary-cta-button secondary-cta-danger">PDF</button>
  </div>
</div>
```

---

## 11. Form Inputs

| Class | Notes |
|-------|-------|
| `form-input` | Standard full-width input |
| `form-input-compact` | Compact height input |
| `form-input-compact-white` | Compact white background |
| `form-input-auth` | Auth/login input (larger) |
| `form-input-search` | Search input with left-icon padding |
| `form-input-popover` | Input inside a popover (no border-radius) |
| `form-input-leading-icon` | Icon positioned inside input left |
| `form-input-disabled` | Disabled state styling |
| `form-textarea` | Textarea |
| `date-trigger-button` | Date picker trigger |
| `date-trigger-button-compact` | Compact date trigger |
| `form-section-card` | Form section container card |
| `form-section-title` | Section heading inside form |
| `form-section-copy` | Section description text |

> F4 note: `form-input-compact/auth/search` should be `form-input--compact` etc. Deferred rename (wide usage).

```html
<div class="relative">
  <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
  <input class="form-input-search" type="text" placeholder="Cari..." />
</div>
```

---

## 12. Tables

| Class | Modifier | Notes |
|-------|----------|-------|
| `data-table-wrap` | — | Scroll wrapper for data table |
| `data-table` | — | Full data table styles |
| `table-toolbar` | — | Toolbar above table (filters + actions) |
| `table-empty-state` | — | Empty state placeholder, p-10 |
| `table-pager-bar` | — | Pagination bar |
| `table-pager-bar-compact` | — | Compact pagination bar |
| `table-sortable` | — | Sortable `<th>` with sort arrow |
| `table-sortable` | `table-sort-asc` | Ascending indicator |
| `table-sortable` | `table-sort-desc` | Descending indicator |

```html
<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th class="table-sortable" @click="sort('col')">Column</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="rows.length === 0">
        <td colspan="N"><div class="table-empty-state">Belum ada data</div></td>
      </tr>
    </tbody>
  </table>
</div>
<div class="table-pager-bar">
  <span class="text-body-sm text-slate-400">1-15 dari 100</span>
  <div class="flex gap-1">
    <button class="icon-utility-button icon-utility-bordered">...</button>
    <button class="icon-utility-button icon-utility-bordered">...</button>
  </div>
</div>
```

---

## 13. Cards & Panels

| Class | Notes |
|-------|-------|
| `kpi-card` | KPI stat card, radius 28px, p-4 |
| `panel` | Generic content panel, radius 8px |
| `section-card` | Base section card (white bg + border) |
| `section-card-body` | Section card with content padding |
| `section-card-shell` | Section card without inner padding (has header/table) |
| `stat-card` | Summary stat card with hover lift |
| `dashboard-summary-card-compact` | Compact KPI card for summary grid |
| `dashboard-summary-value` | Large value number inside summary card |
| `dashboard-summary-unit` | Unit label next to summary value |
| `dashboard-summary-grid-compact` | 2-col mobile / 4-col desktop compact grid |
| `mobile-record-card` | Card for mobile list view records |
| `mobile-data-card` | Full mobile data card with structured slots |
| `mobile-data-card__header` | Header row of mobile-data-card |
| `mobile-data-card__title` | Primary title in mobile-data-card |
| `mobile-data-card__meta` | Meta line in mobile-data-card |
| `mobile-data-card__summary` | Summary row (key-value pairs) |
| `mobile-data-card__actions` | Action buttons row |
| `surface-panel-soft` | Soft tinted panel surface |
| `settings-surface` | Settings page surface |
| `settings-panel-card` | Clickable settings item card |
| `settings-panel-item` | Settings list item row |
| `settings-entry-card` | Editable entry card in settings |
| `settings-empty-state` | Empty state in settings |
| `settings-empty-state__icon` | Icon block in settings empty state |
| `mini-stat-chip` | Inline micro stat chip |
| `mini-stat-chip--outlined` | Outlined variant |

```html
<!-- KPI grid -->
<div class="kpi-grid">
  <div class="kpi-card">
    <span>Label</span>
    <strong>Nilai</strong>
    <small>Keterangan</small>
  </div>
</div>

<!-- Section with table -->
<section class="section-card section-card-shell">
  <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
    <h3 class="type-heading-sm font-bold text-slate-700">Judul</h3>
  </div>
  <div class="overflow-x-auto">
    <table class="data-table">...</table>
  </div>
  <div class="table-pager-bar">...</div>
</section>

<!-- Summary grid -->
<div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
  <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
      <i class="fa-solid fa-chart-bar text-[120px]"></i>
    </div>
    <p class="text-overline font-bold uppercase tracking-widest text-blue-500 mb-3">Label</p>
    <div class="flex items-baseline gap-2">
      <span class="dashboard-summary-value">1.234</span>
      <span class="dashboard-summary-unit text-blue-400">unit</span>
    </div>
    <p class="text-body-sm font-bold text-blue-600 mt-3">Keterangan</p>
  </div>
</div>
```

---

## 14. Radius Utilities

| Class | Value | Usage |
|-------|-------|-------|
| `radius-card` | `var(--radius-card)` = 28px | KPI card |
| `radius-panel` | `var(--radius-sm)` = 8px | Generic panel |
| `radius-dialog` | 24px | Dialog surface |
| `radius-sheet` | 28px mobile / 20px desktop | Bottom sheet |
| `radius-sheet-top` | Top corners only |  |
| `radius-sheet-bottom` | Bottom corners only | |

---

## 15. Modals & Overlays

| Class | Notes |
|-------|-------|
| `mobile-sheet` | Bottom sheet surface |
| `overlay-dialog-surface` | Shared base for dialog/sheet |
| `modal-sheet-surface` | Standard modal sheet |
| `modal-sheet-surface-mobile-center` | Centered on mobile (small modal) |
| `modal-dialog-surface` | Desktop dialog box |
| `modal-dialog-surface-scroll` | Scrollable dialog variant |
| `overlay-backdrop` | Dark backdrop behind modal |
| `modal-header-bar` | Sticky modal header row |
| `modal-header-bar-sticky` | Header that stays sticky on scroll |
| `modal-header-copy` | Icon + title group inside header |
| `modal-header-icon` | Icon container in header, 44×44px |
| `modal-footer-bar` | Button row at bottom of modal |
| `modal-footer-actions` | Inner actions group in footer |
| `modal-width-compact` | max-width: 28rem |
| `modal-width-form` | max-width: 44rem |
| `modal-width-detail` | max-width: 56rem |
| `modal-width-wide` | max-width: 72rem |
| `overlay-motion-sheet` | Vue transition wrapper for sheet |
| `overlay-motion-dialog` | Vue transition wrapper for dialog |

```html
<transition name="fade">
  <div v-if="open" class="overlay-motion-sheet">
    <div class="overlay-backdrop" @click="close"></div>
    <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
      <div class="modal-header-bar radius-sheet-top">
        <div class="modal-header-copy">
          <div class="modal-header-icon bg-blue-500 text-white">
            <i class="fa-solid fa-pen text-heading-lg"></i>
          </div>
          <div>
            <div class="type-heading-sm font-bold">Judul Modal</div>
            <div class="text-body-sm text-slate-400">Subjudul</div>
          </div>
        </div>
      </div>
      <!-- content -->
      <div class="modal-footer-bar">
        <button class="modal-secondary-button" @click="close">Batal</button>
        <button class="modal-primary-button" @click="save">Simpan</button>
      </div>
    </div>
  </div>
</transition>
```

---

## 16. Badges & Pills

| Class | Modifier | Notes |
|-------|----------|-------|
| `badge` | — | Status badge |
| `badge` | `progress` | In-progress (amber) |
| `badge` | `done` | Done (green) |
| `badge` | `draft` | Draft (slate) |
| `mini-stat-chip` | — | Micro inline stat |
| `mini-stat-chip--outlined` | — | Outlined mini stat |

---

## 17. Calendar

| Class | Notes |
|-------|-------|
| `calendar-day-button` | Individual day cell |
| `calendar-footer-action` | Action button in calendar footer |

---

## 18. Typography

### Font-size only (compose with font-weight etc.)

| Class | Token | px |
|-------|-------|----|
| `text-display-lg` | `--fs-display-lg` | 26px |
| `text-display-sm` | `--fs-display-sm` | 18px |
| `text-heading-lg` | `--fs-heading-lg` | 16px |
| `text-heading-sm` | `--fs-heading-sm` | 13px |
| `text-body` | `--fs-body` | 11px |
| `text-body-sm` | `--fs-body-sm` | 10px |
| `text-overline` | `--fs-overline` | 9px |
| `text-overline-xs` | `--fs-overline-xs` | 8px |

### Type presets (font-size + line-height + tracking)

| Class | Alias | Notes |
|-------|-------|-------|
| `type-overline-xs` | — | 8px, tracking-[0.12em] |
| `type-overline` / `type-micro` | — | 9px |
| `type-body-sm` / `type-meta` | — | 10px, line-height 1.5 |
| `type-body` | — | 11px, line-height 1.6 |
| `type-heading-sm` / `type-title` | — | 13px, font-medium |

---

## 19. Grid Layouts (app.css)

| Class | Notes |
|-------|-------|
| `kpi-grid` | 1-col → 2-col@768 → 4-col@1080 |
| `content-grid` | 1-col → 1.1fr/0.9fr@768 |

---

## 20. Animation & Motion

| Class | Notes |
|-------|-------|
| `animate-fadeIn` | Fade + slide in on mount |
| `motion-stagger-item` | Stagger delay on list items |
| `fade-enter-active` etc. | Vue transition classes (auto-applied) |
| `sidebar-accordion-enter-active` etc. | Vue accordion transition |
| `toast-enter-active` etc. | Vue toast transition |
| `ideation-kanban-board` | Disables transitions inside kanban |

---

## F4 — Naming Convention Issues (deferred rename)

Pattern target: `[komponen]-[elemen]--[modifier]`

| Current | Should be | Status |
|---------|-----------|--------|
| `secondary-cta-success` | `secondary-cta-button--success` | Deferred |
| `secondary-cta-danger` | `secondary-cta-button--danger` | Deferred |
| `secondary-cta-neutral` | `secondary-cta-button--neutral` | Deferred |
| `secondary-cta-link` | `secondary-cta-button--link` | Deferred |
| `table-action-compact` | `table-action-button--compact` | Deferred |
| `table-action-danger` | `table-action-button--danger` | Deferred |
| `table-action-link` | `table-action-button--link` | Deferred |
| `table-action-view` | `table-action-button--view` | Deferred |
| `form-input-compact` | `form-input--compact` | Deferred |
| `form-input-auth` | `form-input--auth` | Deferred |
| `form-input-search` | `form-input--search` | Deferred |
| `form-input-popover` | `form-input--popover` | Deferred |
| `section-card-body` | `section-card--body` | Deferred |
| `section-card-shell` | `section-card--shell` | Deferred |

> These renames require global search-replace across all blade + JS files. Safe to batch-execute once visual QA (Fase E) completes.
