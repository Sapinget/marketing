# Pura Pura Ponsel — Marketing Dashboard Design System

> Design foundation, tokens, components, and states for the Marketing Dashboard UI.

---

## Foundations

### Color Palette

#### Brand / Accent

| Token | Hex | Usage |
|---|---|---|
| `--ppp-accent` | `#4f63ff` | Primary CTA, links, focus rings, active nav |
| `--ppp-accent-dark` | `#3d4fdb` | Accent hover state |
| `--ppp-nav-text` | `#5066eb` | Navigation text accent |

#### Slate Scale

| Swatch | Hex | Usage |
|---|---|---|
| Slate 950 | `#0f172a` | Primary text, dark buttons |
| Slate 700 | `#334155` | Table body text |
| Slate 600 | `#475569` | Secondary text, ghost buttons |
| Slate 500 | `#64748b` | Meta text, nav items (default) |
| Slate 400 | `#94a3b8` | Muted text, sub-nav |
| Slate 300 | `#cbd5e1` | Input icons, scrollbar thumb |
| Slate 200 | `#e2e8f0` | Borders, dividers (`--ppp-line`) |
| Slate 100 | `#f1f5f9` | Hover backgrounds |
| Slate 50 | `#f8fafc` | Page/sidebar background (`--ppp-bg`) |

#### Semantic Colors

| Role | Hex | Context |
|---|---|---|
| Blue 600 | `#2563eb` | Hero badge, info accent |
| Green 600 / Emerald 500 | `#059669` / `#10b981` | Success, done badges |
| Red 600 / Rose 600 | `#dc2626` / `#e11d48` | Danger, delete, error |
| Red 500 / 700 | `#ef4444` / `#b91c1c` | Alert close / alert text |
| Indigo 500 | `#6366f1` | Table action view |

#### Surface

| Token | Hex | Usage |
|---|---|---|
| `--ppp-card` | `#ffffff` | Card, panel, modal surface |
| `--ppp-sidebar` | `#f8fafc` | Sidebar background |
| `--ppp-bg` | `#f8fafc` | Page background |
| `--ppp-line` | `#e2e8f0` | Borders and dividers |

---

### Typography

#### Font Family

```css
'Instrument Sans', ui-sans-serif, system-ui, sans-serif
```

Weights loaded: **400, 500, 600**. Imported via Bunny CDN through Vite.

#### Type Scale

| Token | Size | Weight | Letter-spacing | Usage |
|---|---|---|---|---|
| Micro | 9px | 600 | — | Timestamps, tiny labels |
| Meta | 10px | 700 | 0.08em | Table headers, eyebrow, badges |
| Body | 11px | 500 | — | Content, form labels, nav items, buttons |
| Title | 13px | 600 | — | Row titles, card titles |

#### Heading Scale

| Level | Size | Weight | Usage |
|---|---|---|---|
| h1 | 26px | 700 | Dashboard hero, module header |
| h2 | 18px | 700 | Topbar title |
| h3 | 16px | 700 | Panel title |
| Eyebrow | 10px | 600 | Section labels (0.18em LS, uppercase) |

#### Letter-spacing Scale

| Value | Usage |
|---|---|
| 0.08em | Meta text, badges, buttons |
| 0.12em | Table headers, search labels, KPIs |
| 0.14em | Brand name |
| 0.18em | Eyebrow, login button |
| 0.22em | Compact summary cards |

---

### Spacing

#### Gap Scale

| Value | Usage |
|---|---|
| 2px | Segmented control items |
| 4px | Menu toggle lines |
| 6px | Form compact, search shell |
| 8px | Buttons, filter pills, toolbar items |
| 10px | User card, DS rows |
| 12px | KPI grid, nav items, panels, alerts |
| 14px | Topbar |
| 16px | Content grid, login form, panel header |
| 24px | Dashboard hero, page view |

#### Padding Scale

| Value | Usage |
|---|---|
| 6px 8px | Table cells (mobile) |
| 8px 12px | Form-input-compact |
| 10px 12px | Search input |
| 12px 14px | Table th, panel rows |
| 12px 16px | Form input |
| 12px 20px | Toast |
| 14px 16px | Panel header, user card |
| 16px | KPI card, panel content |
| 16px 18px | Login button |
| 16px 24px | Page view, modal footer |
| 20px | Dashboard hero |
| 24px | Page view padding |
| 1.25rem | Section card body |
| 1.5rem | Modal header |

---

### Border Radius

| Token | Value | Usage |
|---|---|---|
| `r-sm` | 12px | Buttons, badge, avatar, brand mark, filter pills, compact inputs |
| `r-md` | 16px | Primary CTA, form inputs, search shell, toasts, alerts, segmented control |
| `r-lg` | 24px | Section cards, stat cards, table empty state |
| `r-xl` | 28px | Panels, loading screen logo, dashboard summary cards |
| `r-modal` | 32px | Modal shell, overlay dialog surface |

Additional: **8px** (KPI cards, table actions), **10px** (icon utility), **20px** (form section cards), **999px** (badges, avatar, scrollbar).

---

### Shadows

| Shadow | Value | Context |
|---|---|---|
| Sidebar | `0 28px 48px rgba(15, 23, 42, 0.08)` | Fixed sidebar |
| Icon button | `0 12px 24px rgba(15, 23, 42, 0.12)` + `inset 0 1px 0 #fff` | Default state |
| Icon button hover | `0 16px 30px rgba(15, 23, 42, 0.16)` | Hover state |
| Icon button pressed | `0 5px 14px rgba(15, 23, 42, 0.18)` + `inset 0 2px 4px rgba(148,163,184,0.26)` | Active state |
| Popover/overlay | `0 25px 50px -12px rgba(15, 23, 42, 0.18)` | Search select, mobile sheet, dialog |
| Focus accent | `0 0 0 3px rgba(79, 99, 255, 0.08)` | Input focus ring |
| Segmented active | `0 1px 2px rgba(15, 23, 42, 0.06)` + `0 4px 10px rgba(15, 23, 42, 0.05)` | Active segment |
| Filter open | `0 10px 24px rgba(15, 23, 42, 0.08)` | Trigger when popover open |
| CTA active | `0 6px 18px rgba(15, 23, 42, 0.10)` | Button active press |

Shadows are used sparingly and consistently — primarily for elevation (sidebar, overlays) and interactive state feedback (buttons).

---

## Design Tokens

All tokens are defined as CSS custom properties on `:root` and mirrored as Tailwind v4 `@theme` values.

```css
:root {
  --ppp-accent:      #4f63ff;
  --ppp-accent-dark: #3d4fdb;
  --ppp-sidebar:     #f8fafc;
  --ppp-text:        #0f172a;
  --ppp-muted:       #94a3b8;
  --ppp-nav-text:    #5066eb;
  --ppp-line:        #e2e8f0;
  --ppp-bg:          #f8fafc;
  --ppp-card:        #ffffff;
  --ppp-danger:      #dc2626;
  --font-sans:       'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}
```

```css
@theme {
  --font-sans: ...;
  --color-ppp-accent: #4f63ff;
  --color-ppp-accent-dark: #3d4fdb;
  --color-ppp-sidebar: #f8fafc;
  --color-ppp-text: #0f172a;
  --color-ppp-muted: #94a3b8;
  --color-ppp-nav-text: #5066eb;
}
```

Use tokens instead of raw values to ensure consistency across the UI.

---

## Reusable Components

### Buttons

#### Primary / Dark Button

```html
<button class="dark-button">
  Simpan
</button>
```

| Property | Value |
|---|---|
| Height | 34–40px |
| Radius | 12px |
| Background | `#0f172a` |
| Text | `#fff`, 11px, 700, uppercase |
| Border | None |
| Hover | Background `#020617` |
| Active | `scale(0.97)` + `0 6px 18px` shadow |

#### Ghost Button

```html
<button class="ghost-button">
  Batal
</button>
```

| Property | Value |
|---|---|
| Height | 34px |
| Radius | 12px |
| Background | `#fff` |
| Text | `#475569`, 11px, 700 |
| Border | `1px solid var(--ppp-line)` |
| Hover | Darker background |

#### Icon Button

```html
<button class="icon-button" aria-label="Settings">
  <svg>...</svg>
</button>
```

| Property | Value |
|---|---|
| Size | 36×36px |
| Radius | 12px |
| Background | Radial + linear gradient |
| Border | `1px solid rgba(148,163,184,0.28)` |
| Hover | Accent border, lift shadow |
| Active | `scale(0.96)`, inset shadow |

#### Secondary CTA Button

```html
<button class="secondary-cta-button">Edit</button>
<button class="secondary-cta-button --success">Approve</button>
<button class="secondary-cta-button --danger">Hapus</button>
<button class="secondary-cta-button --link">Lihat</button>
```

| Property | Value |
|---|---|
| Height | 40px |
| Radius | 12px |
| Background | `#fff` / `#f8fafc` |
| Text | `#475569`, 10px, 700, uppercase |
| Border | `1px solid var(--ppp-line)` |

#### Modal Button

```html
<button class="modal-primary-button">Konfirmasi</button>
<button class="modal-secondary-button">Batal</button>
```

Primary: accent `rgb(80 102 235)` bg, white text. Hover darker.
Secondary: `rgb(241 245 249)` bg, `rgb(71 85 105)` text.

#### Table Action Button

```html
<button class="table-action-button">Aksi</button>
<button class="table-action-button table-action-danger">Hapus</button>
<button class="table-action-button table-action-link">Detail</button>
<button class="table-action-button table-action-view">Lihat</button>
```

| Property | Value |
|---|---|
| Height | 44px (32px for compact) |
| Radius | 12px |
| Background | `rgb(248 250 252)` |
| Text | `rgb(100 116 139)` |

#### Filter / Select Trigger

```html
<button class="filter-trigger-button">Filter</button>
<button class="select-trigger-button" role="combobox">Pilih</button>
<button class="select-trigger-button --form">Form Select</button>
<button class="select-trigger-button --compact">Compact</button>
```

| Property | Value |
|---|---|
| Height | 36–48px |
| Radius | 12–16px |
| Background | `rgb(248 250 252)` |
| Border | `1px solid rgb(226 232 240)` |
| Open state | Accent border + shadow |

#### Common Button Transition

```css
transition: transform 0.18s ease, box-shadow 0.18s ease;
```

---

### Inputs

#### Form Input

```html
<input class="form-input" type="text" placeholder="Nama" />
```

| Property | Value |
|---|---|
| Radius | 16px |
| Background | `rgb(248 250 252)` |
| Border | `1px solid rgb(226 232 240)` |
| Focus | Accent border + `0 0 0 3px rgba(79, 99, 255, 0.08)`, bg → white |

#### Compact Input

```html
<input class="form-input-compact" type="text" placeholder="Cari..." />
```

Radius 12px. Same border/focus style. Use for dense layouts.

#### Search Input

```html
<input class="form-input-search" type="search" placeholder="Cari..." />
```

Radius 12px. 36px left padding for search icon.

#### Disabled Input

```html
<input class="form-input-disabled" type="text" disabled />
```

Background `rgb(241 245 249)`, cursor `not-allowed`, opacity 0.8.

#### Input Transition

```css
transition: all 0.15s ease;
```

or

```css
transition: border-color 0.18s ease, box-shadow 0.18s ease;
```

---

### Cards

#### KPI Card

```html
<div class="kpi-card">
  <div class="kpi-label">Total Views</div>
  <div class="kpi-value">12,345</div>
</div>
```

| Property | Value |
|---|---|
| Background | `#fff` |
| Border | `1px solid var(--ppp-line)` |
| Radius | 8px |
| Padding | 16px |
| Value text | 26px, 700 |

#### Panel

```html
<div class="panel">
  <div class="panel-header">...</div>
  <div class="panel-content">...</div>
</div>
```

| Property | Value |
|---|---|
| Background | `#fff` |
| Border | `1px solid var(--ppp-line)` |
| Radius | 8px |
| Overflow | hidden |
| Header | Bottom border separator |

#### Section Card

```html
<div class="section-card">
  ...
</div>
```

| Property | Value |
|---|---|
| Background | `#fff` |
| Border | `1px solid rgb(241 245 249)` |
| Radius | 24px |
| Body padding | 1.25rem |

#### Stat Card

```html
<div class="stat-card">
  ...
</div>
```

Radius 20px. Hover: accent border. Used for summary statistics.

#### Dashboard Summary Card

```html
<div class="dashboard-summary-card">
  ...
</div>
<div class="dashboard-summary-card-compact">
  ...
</div>
```

Radius 20px. Compact variant has 4.25rem right padding for absolute decorator.

#### Form Section Card

```html
<div class="form-section-card">
  ...
</div>
```

Gradient background (slate 50 → white), radius 20px, inset shadow top.

#### Mobile Record Card

Used on mobile viewports. Padding 1rem, no border-radius by default.

#### Overlay / Dialog Surface

```html
<div class="overlay-dialog-surface">
  ...
</div>
```

Radius 24–32px, white bg, shadow `0 25px 50px -12px`.

#### Empty State

```html
<div class="table-empty-state">
  Tidak ada data
</div>
```

Dashed border, radius 24px, centered uppercase text.

---

### Navigation

#### Sidebar

| Property | Value |
|---|---|
| Width | 240px (fixed left) |
| Background | `var(--ppp-sidebar)` = `#f8fafc` |
| Border | `1px solid var(--ppp-line)` |
| Shadow | `0 28px 48px rgba(15, 23, 42, 0.08)` |
| Transition | `transform 0.42s cubic-bezier(0.22, 1, 0.36, 1)` |
| Z-index | 80 |

#### Brand Row

Height 64px. Brand mark 36×36px, radius 12px, white bg with `1px solid #dbeafe` border.

#### Nav Item

```html
<a class="nav-item is-active" href="#dashboard">Dashboard</a>
<a class="nav-item" href="#reports">Laporan</a>
```

| State | Style |
|---|---|
| Default | 42px height, 11px/600, `#64748b` |
| Hover | Background `#f1f5f9`, color `#475569` |
| Active | Gradient `var(--ppp-accent)` → `var(--ppp-accent-dark)`, text `#fff` |

#### Nav Sub-item

```html
<a class="nav-subitem" href="#sub-page">Sub Page</a>
```

34px height, 10.5px, `#94a3b8` default. Same hover/active as nav-item.

#### Nav Group Toggle

```html
<button class="nav-group-toggle">
  Group Name
  <svg class="chevron">...</svg>
</button>
```

42px height. Chevron rotates 180° when open.

#### User Card

Placed at sidebar bottom. Avatar 36×36px, `#0f172a` bg, radius 12px, white text.
Name: 11px/700 `#0f172a`. Role: 9px uppercase `--ppp-muted`.
Border top: `1px solid var(--ppp-line)`.

#### Topbar

| Property | Value |
|---|---|
| Height | 64px |
| Background | `rgba(255, 255, 255, 0.9)` |
| Position | Sticky top, z-index 50 |
| Title | 18px, 700, `#0f172a` |

#### Mobile Navigation (≤768px)

Sidebar becomes overlay. Hidden off-screen with `transform: translateX(calc(-100% - 18px))`. `.is-open` slides in. Backdrop: `rgba(15, 23, 42, 0.18)`, `backdrop-filter: blur(2px)`, z-index 70.

#### Segmented Control

```html
<div class="segmented-control">
  <button class="is-active">Bulan Ini</button>
  <button>Minggu Ini</button>
  <button>Hari Ini</button>
</div>
```

| Property | Value |
|---|---|
| Container | `inline-flex`, bg `#f1f5f9`, radius 16px, padding 4px, gap 2px |
| Items | Radius 12px, 11px/700, `rgb(100 116 139)` |
| Active | White bg, `#0f172a` text, shadow `0 1px 2px / 0 4px 10px` |

#### Filter Pills

```html
<div class="filter-pills">
  <button class="is-active">Semua</button>
  <button>Brand</button>
  <button>Vendor</button>
</div>
```

Flex wrap, gap 8px. Default: white bg, `1px solid var(--ppp-line)`, radius 12px.
Active: `#0f172a` bg + border, white text.

---

## Variants & States

### Default

- **Buttons**: Solid background for primary, bordered for ghost/secondary
- **Inputs**: Bordered with `rgb(248 250 252)` background
- **Cards**: White surface with hairline border
- **Nav items**: Muted slate text with transparent background

### Hover

- **Primary button**: Darker background (`#020617`)
- **Ghost button**: Darker surface background
- **Icon button**: Accent border, elevated shadow
- **Stat card**: Accent border highlight
- **Nav item**: Slate 100 background, darker slate text
- **Table action**: Color shift (blue for default, red for danger)
- **Input popover item**: Slate 100 background

### Active / Pressed

- **Buttons**: `scale(0.97)` transform, inset shadow for depth
- **Icon button**: `scale(0.96)`, pressed shadow with inset
- **Segmented item**: White surface, bold text, elevated shadow
- **Filter pill**: Dark slate background, white text
- **Nav item**: Accent gradient background, white text

### Disabled

- **Input**: `rgb(241 245 249)` bg, cursor `not-allowed`, reduced opacity
- **Button**: Reduced opacity, no hover effects

### Error

- **Input**: Red border (`#dc2626`), red-tinted focus ring
- **Button**: `.table-action-danger` / `--danger` variant — rose/red tones
- **Alert text**: `#b91c1c` on `#fef2f2` background
- **Modal danger**: Rose accent button

### Focus

All interactive elements use a consistent focus ring:

```css
box-shadow: 0 0 0 3px rgba(79, 99, 255, 0.08);
border-color: var(--ppp-accent);
```

Compact variants use a 2px ring instead of 3px.

---

## Responsive Breakpoints

| Breakpoint | Layout |
|---|---|
| >1080px | KPI 4-col, sidebar fixed 240px |
| 769–1080px | KPI 2-col, content-grid 1-col |
| ≤768px | Sidebar overlay, KPI 1-col, topbar compact, h1 22px |
| ≥640px | Horizontal toolbar layout |
| ≥768px | Chips 5-col, sheets radius 32px |

---

## Motion

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    transition: none !important;
    animation: none !important;
  }
}
```

Entry animations use `cardReveal` keyframes with `var(--stagger-delay)` for staggered list appearance.

---

*Generated from codebase audit — `resources/css/app.css`, `resources/css/dashboard-shell.css`, and `public/marketing-dashboard.html`.*
