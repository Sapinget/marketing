# Figma Component Map

> Audit lengkap semua UI element + state untuk pembuatan SVG/Figma design system.
> Source: `dashboard-shell.css` + semua blade partials.
> Audit date: 2026-07-10
>
> Lihat juga: [component-catalog.md](component-catalog.md) — class reference. [ui-spec.md](ui-spec.md) — design tokens.

---

## Design Tokens

### Color

| Token | Value | Usage |
|-------|-------|-------|
| `--ppp-bg` | `rgb(248 250 252)` | Page background (slate-50) |
| `--ppp-card` | `rgb(255 255 255)` | Card/surface background |
| `--ppp-text` | `rgb(15 23 42)` | Primary text (slate-900) |
| `--ppp-muted` | `rgb(148 163 184)` | Muted/placeholder text (slate-400) |
| `--ppp-line` | `rgb(226 232 240)` | Border/divider (slate-200) |
| `--ppp-accent` | `#4f63ff` | Primary brand / button / header table |
| `--ppp-accent-dark` | (darker) | Hover darken accent |
| `--ppp-nav-text` | sidebar nav text | Sidebar item text |
| `--ppp-danger` | `rgb(239 68 68)` | Error/danger (red-500) |
| `--ppp-danger-fill` | danger fill | Danger background |
| `--ppp-sidebar` | sidebar bg | Sidebar panel background |

### Typography Scale

| Token | Size | Usage |
|-------|------|-------|
| `--fs-display-lg` | ~24px | Page title, hero number |
| `--fs-display-sm` | ~20px | Section heading |
| `--fs-heading-lg` | ~16px | Card heading |
| `--fs-heading-sm` | ~14px | Sub-heading |
| `--fs-body` | ~12px | Body default |
| `--fs-body-sm` | ~11px | Secondary body |
| `--fs-overline` | ~10px | Label/caption |
| `--fs-overline-xs` | ~9px | Micro label |

### Spacing Grid (4pt)

```
4 · 8 · 12 · 16 · 20 · 24 · 28 · 32 · 36 · 40 · 44 · 48px
```

### Height Scale

| Height | Components |
|--------|-----------|
| 32px | `table-action-compact`, `icon-utility-button` |
| 36px | `primary-cta-button`, `secondary-cta-button`, `select-trigger-button`, `select-trigger-button-compact` |
| 44px | `select-trigger-button-form`, `select-trigger-button-form-tight` |

### Border Radius Scale

| Value | Token class | Usage |
|-------|-------------|-------|
| 8px | — | Badges, chips kecil |
| 12px | — | Form triggers |
| 16px | — | Button pills, popover options |
| 20px | — | Summary cards |
| 24px | `.radius-card` | Section cards, table shell |
| 28px | `.radius-panel` | Panels |
| 32px | `.radius-dialog` | Modals |

---

## Komponen: Buttons

### 1. primary-cta-button

**Dimensi:** h=36px, px=14px, radius=16px, font=`--fs-body` uppercase, fw=700

| State | Background | Text | Border | Shadow |
|-------|-----------|------|--------|--------|
| Default | `--ppp-accent` | white | none | `0 1px 2px rgba(79,99,255,0.18)` |
| Hover | darker accent | white | none | lifted |
| Active | scale(0.97) | white | none | reduced |
| Disabled | accent 50% opacity | white | none | none |

**Variants (color modifier):**

| Variant | Class | Background |
|---------|-------|-----------|
| Default/Accent | `.primary-cta-button` | `--ppp-accent` (#4f63ff) |
| Info | `.primary-cta-button--info` | blue-600 |
| Success | `.primary-cta-button--success` | green-600 |
| Danger | `.primary-cta-button--danger` | red-500 |

**Figma frames needed:**
- [ ] default
- [ ] hover
- [ ] active (pressed)
- [ ] disabled
- × 4 color variants = **16 frames**

---

### 2. secondary-cta-button

**Dimensi:** h=36px, px=12px, radius=16px, font=`--fs-body` uppercase, fw=700

| State | Background | Text | Border |
|-------|-----------|------|--------|
| Default | slate-100 | slate-600 | none |
| Hover | slate-200 | slate-700 | none |
| Active | scale(0.97) | — | — |
| Disabled | opacity 0.5 | — | — |

**Variants:**

| Variant | Class | Style |
|---------|-------|-------|
| Neutral | `.secondary-cta-neutral` | abu-abu standard |
| Success | `.secondary-cta-success` | green tint |
| Danger | `.secondary-cta-danger` | red tint |
| Link | `.secondary-cta-link` | no background, underline |

**Figma frames needed:**
- [ ] default
- [ ] hover
- [ ] active
- [ ] disabled
- × 4 variants = **16 frames**

---

### 3. icon-utility-button

**Dimensi:** 32×32px, radius=8px

| State | Background | Icon color |
|-------|-----------|-----------|
| Default | transparent | slate-500 |
| Hover | slate-100 | slate-700 |
| Active | slate-200 | — |
| Disabled | opacity 0.5 | — |

**Variants:**

| Variant | Class | Style |
|---------|-------|-------|
| Default | `.icon-utility-button` | no border |
| Bordered | `.icon-utility-bordered` | 1px border |
| Round | `.icon-utility-round` | radius=50% |
| Danger | `.icon-utility-danger` | hover red |

**Figma frames needed:**
- [ ] default, hover, active, disabled × 4 variants = **16 frames**

---

### 4. table-action-button

**Dimensi compact:** 32×32px, radius=8px, font=`--fs-overline`

| State | Background | Icon |
|-------|-----------|------|
| Default | transparent | slate-500 |
| Hover | slate-100 | slate-700 |
| Active | — | — |

**Variants:**

| Variant | Class | Style |
|---------|-------|-------|
| Default compact | `.table-action-button.table-action-compact` | gray |
| Danger | `.table-action-danger` | red on hover |
| Link | `.table-action-link` | accent color |
| View | `.table-action-view` | — |
| With label | `.table-action-label` | icon + text |

**Figma frames needed:**
- [ ] default, hover × 5 variants = **10 frames**

---

### 5. select-trigger-button

**Dimensi:** h=36px, px=12px, radius=16px

| State | Background | Border | Icon rotation |
|-------|-----------|--------|--------------|
| Default | slate-50 | slate-200 | 0deg |
| Hover | white | slate-300 | 0deg |
| Open | white | accent | 180deg (chevron) |
| Focus | white | accent | — |

**Variants:**

| Variant | Class | Height | px | Notes |
|---------|-------|--------|-----|-------|
| Standard | `.select-trigger-button` | 36px | 12px | toolbar |
| Compact | `.select-trigger-button-compact` | 36px | 10px | inline/date compact |
| Form | `.select-trigger-button-form` | 44px | 14px | inside modal form |
| Form tight | `.select-trigger-button-form-tight` | 44px | 10px | form tighter |

**Figma frames needed:**
- [ ] default, hover, open (chevron rotate), focus × 4 variants = **16 frames**

---

### 6. calendar-day-button

**Dimensi:** variable, radius=8px

| State | Style |
|-------|-------|
| Default | transparent |
| Hover | accent-light bg |
| Selected/Active | accent bg, white text |
| Today | accent border |
| Disabled | muted opacity |
| Range in | accent-10 bg |
| Range start/end | accent bg, round one side |

**Figma frames needed:** 7 states

---

### 7. segmented-filter-button

**Dimensi:** h=32px, px=12px, radius=8px

| State | Style |
|-------|-------|
| Default | transparent |
| Hover | slate-100 |
| Active | — (handled by parent segmented-control) |

---

## Komponen: Segmented Control

### segmented-control (standard)

**Dimensi:** radius=16px, bg=slate-100, p=4px inside

| Item state | Background | Text |
|-----------|-----------|------|
| Inactive | transparent | slate-500 |
| Hover | white 60% | slate-700 |
| Active | white | slate-900, fw=700 |

### segmented-control--ios

Same as standard + animated ::before sliding pill (0.34s cubic-bezier).

### segmented-control--equal

Grid layout, 2 columns equal width.

**Figma frames needed:**
- [ ] 1-tab active (default)
- [ ] 2-tab active
- [ ] 3-tab active
- [ ] Standard vs iOS variant
- **= 6 frames**

---

## Komponen: Form Inputs

### form-input

**Dimensi:** h=auto, py=10px, px=12px, radius=8px, font=11px

| State | Background | Border | Text |
|-------|-----------|--------|------|
| Default (empty) | slate-50 | slate-200 | — |
| Default (filled) | slate-50 | slate-200 | slate-700 |
| Focus | white | accent | slate-900 |
| Placeholder | — | — | slate-400 |
| Disabled | slate-100 | slate-200 | slate-400, opacity 0.8 |
| Error | white | red-400 | — |

**Variants:**

| Variant | Class | Notes |
|---------|-------|-------|
| Base | `.form-input` | standard |
| Auth | `.form-input-auth` | h=40px, leading icon space |
| Compact | `.form-input-compact` | font=12px |
| Compact white | `.form-input-compact-white` | white bg |
| Search | `.form-input-search` | pl=36px (icon left) |
| Popover | `.form-input-popover` | inside dropdown |
| Disabled | `.form-input-disabled` | locked state |

**With leading icon:** `.form-input-leading-icon` wrapper

**Figma frames needed:**
- [ ] empty, filled, focus, placeholder, disabled, error × 7 variants = **42 frames**

---

### form-textarea

Same token as `form-input`. Resize: vertical only.

States: default, focus, filled, disabled

---

### toolbar-native-select

**Dimensi:** h=40px, px=10px, radius=8px, relative container

| State | Border | Background |
|-------|--------|-----------|
| Default | slate-200 | slate-50 |
| Hover | slate-300 | white |
| Focus | accent | white |

---

## Komponen: Search Select Popover

### search-select-popover

**Container:** absolute, min-w=200px, radius=16px, shadow=elevated, bg=white, z=50

**Sub-components:**
- `.search-select-popover__search` — input field di top
- `.search-select-popover__options` — scrollable list

**Container states:**

| State | Description |
|-------|------------|
| Closed | display: none |
| Opening | scale(0.98) + translateY(-6px) + opacity:0 → enter |
| Open | scale(1) + translateY(0) + opacity:1 |
| Flip-up | `--flip-up` variant, animates from bottom |
| Searching | input active, options filtered |
| Empty results | no options visible |
| Closing | reverse enter animation |

**Option item (`.popover-option`):**

| State | Background | Text |
|-------|-----------|------|
| Default | white | slate-700 |
| Hover | slate-50 | slate-900 |
| Active/Selected | `.popover-option-active` | accent bg, white |

**Checkbox option (`.popover-option-check`):**

| State | Checkbox | Background |
|-------|----------|-----------|
| Unchecked | empty box | white |
| Hover | — | slate-50 |
| Checked | check icon | — |
| Active+Checked | accent checkbox | accent-tint bg |

**Multi-select chip (`.multi-select-chip`):**
- pill badge, accent bg, white text, ×close icon

**Figma frames needed:**
- [ ] Closed trigger
- [ ] Open (normal)
- [ ] Open (flip-up)
- [ ] Searching
- [ ] Empty results
- [ ] option default / hover / active
- [ ] checkbox unchecked / hover / checked / active
- [ ] multi-select chip
- **= 14 frames**

---

## Komponen: Cards & Surfaces

### dashboard-summary-card

**Dimensi:** radius=20px, p=16px, bg=white, border=1px `--ppp-line`

| State | Shadow | Transform |
|-------|--------|-----------|
| Default | sm shadow | — |
| Hover | md shadow | translateY(-1px) |

**Inner anatomy:**
- Icon block: 36×36px, radius=10px, accent-tint bg
- Label: `--fs-overline` uppercase muted
- Value: `--fs-display-sm` fw=700
- Unit/trend: `--fs-body-sm` muted

**Variants:**
- Standard: `.dashboard-summary-card`
- Compact: `.dashboard-summary-card-compact` (tighter, smaller height on mobile)

**Grid wrapper:** `.dashboard-summary-grid-compact` — 2col mobile, 4col desktop

**Figma frames needed:**
- [ ] Default card (desktop)
- [ ] Hover card
- [ ] Compact card (mobile)
- [ ] Grid 2-col (mobile)
- [ ] Grid 4-col (desktop)
- **= 5 frames**

---

### section-card

**Dimensi:** radius=24px, bg=white, border=1px `--ppp-line`, shadow=sm

**Variants:**

| Variant | Class | Padding |
|---------|-------|---------|
| With padding | `.section-card-body` | 1.25rem (20px) all |
| No padding | `.section-card-shell` | 0 (overflow hidden) |

---

### stat-card

| State | Border | Shadow |
|-------|--------|--------|
| Default | `--ppp-line` | sm |
| Hover | accent 30% | md |

---

### settings-panel-card

| State | Border | Background |
|-------|--------|-----------|
| Default | `--ppp-line` | white |
| Hover | accent-light | accent-5 |

**Sub: `.settings-panel-item`** — inner item, bg=accent-5, radius=16px

---

### mobile-record-card

**Dimensi:** p=16px, radius=16px, bg=white, border=1px `--ppp-line`

**Anatomy:**
- Header area
- Title (heading-sm)
- Meta (body-sm, muted)
- Summary grid (2 cols)
- Actions row (bottom)

---

### mobile-data-card

Similar to record-card. Sub-components:
- `.mobile-data-card__header`
- `.mobile-data-card__title`
- `.mobile-data-card__meta`
- `.mobile-data-card__summary` — 2col grid
- `.mobile-data-card__actions`

---

## Komponen: Status & Badges

### status-pill

**Dimensi:** px=8px, py=2px, radius=999px, font=`--fs-overline`, fw=600, inline-flex

| Variant | Class | Color |
|---------|-------|-------|
| Default | `.status-pill` | slate |
| Neutral | `.status-pill--neutral` | gray-400 bg |
| Warm | `.status-pill--warm` | orange tint |

---

### entity-badge

**Dimensi:** px=10px, py=3px, radius=999px, font=`--fs-overline`, fw=600

| Variant | Class | Background | Text |
|---------|-------|-----------|------|
| Info | `.entity-badge--info` | blue-50 | blue-700 |
| Success | `.entity-badge--success` | green-50 | green-700 |
| Warn | `.entity-badge--warn` | orange-50 | orange-700 |
| Danger | `.entity-badge--danger` | red-50 | red-700 |

**Figma frames needed:** 4 variants

---

### mini-stat-chip

**Dimensi:** px=8px, py=2px, radius=999px, font=`--fs-overline-xs`

| Variant | Class | Style |
|---------|-------|-------|
| Filled | `.mini-stat-chip` | accent bg, white text |
| Outlined | `.mini-stat-chip--outlined` | accent border, accent text |

**Wrapper:** `.mini-stat-chip-row` — flex row gap-2

---

### summary-counter-pill

**Dimensi:** radius=16px, px=10px, py=4px, bg=accent-10, accent text

---

### multi-select-chip

**Dimensi:** px=8px, py=3px, radius=999px, bg=accent, white text, × close

---

## Komponen: Table

### Table Shell (`.admin-table-shell`)

**Dimensi:** radius=24px, border=1px `--ppp-line`, overflow hidden

**Anatomy:**
```
admin-table-shell
├── table-toolbar-shell        ← kontrol area (border-bottom)
│   ├── table-toolbar-shell__left   ← search (26rem)
│   └── table-toolbar-shell__right  ← date, export, filter, add, reset
└── table (actual HTML table)
    ├── thead (bg=accent, text=white, GLOBAL !important)
    └── tbody
        └── tr (hover: bg=slate-50)
```

### table-toolbar-shell

**Style:** bg=white, border-bottom=1px `--ppp-line`, p=12px 16px

**Layout:**
- Mobile: flex-col, full width stacked
- Desktop (640px+): flex-row, left + right groups

### Table Header (thead)

| Property | Value |
|----------|-------|
| Background | `var(--ppp-accent)` !important |
| Text color | `rgb(255 255 255)` !important |
| Font | `--fs-overline` uppercase |
| Font weight | 600 |

### Table Sortable Column (`.table-sortable`)

| State | Icon | Color |
|-------|------|-------|
| Default | `↕` (after content) | muted |
| Hover | — | accent |
| Sorted ASC | `↑` | accent |
| Sorted DESC | `↓` | accent |

**Layout:** `display: inline-flex; align-items: center; gap: 4px` — icon tetap di samping teks walau wrap

### Table Row

| State | Background |
|-------|-----------|
| Default | white |
| Hover | slate-50 |
| Selected | accent-5 |

### table-pager-bar

**Dimensi:** p=`1rem 1.5rem`, border-top=1px `--ppp-line`
- Compact: `.table-pager-bar-compact`

**Anatomy:**
- Total count label
- Pagination buttons (prev/next + page numbers)
- Per-page select

### table-empty-state

**Style:** dashed border, muted text, centered, radius=12px

---

## Komponen: Modal & Dialog

### Modal Width Variants

| Variant | Class | Max-width |
|---------|-------|----------|
| Compact | `.modal-width-compact` | 28rem (448px) |
| Form | `.modal-width-form` | 44rem (704px) |
| Detail | `.modal-width-detail` | 56rem (896px) |
| Wide | `.modal-width-wide` | 72rem (1152px) |

### Modal Anatomy

```
overlay-backdrop (dim layer)
└── modal-sheet-surface (or overlay-dialog-surface)
    ├── modal-header-bar (sticky optional)
    │   ├── modal-header-icon   ← colored icon bg
    │   └── modal-header-copy   ← title + subtitle
    ├── scroll content area
    │   └── form-section-card (sections)
    │       ├── form-section-title
    │       └── form-section-copy
    └── modal-footer-bar
        └── modal-footer-actions
            ├── secondary-cta-button (cancel)
            └── primary-cta-button (submit)
```

### modal-header-bar

**Dimensi:** p=1.5rem (24px), border-bottom=1px `--ppp-line`

**States:**
- Standard
- Sticky (`.modal-header-bar-sticky`) — position: sticky, top:0, z-10, bg=white

### modal-footer-bar

**Dimensi:** p=16px 24px, border-top=1px `--ppp-line`, bg=white

**States:**
- Standard
- With scroll lock (`.modal-scroll-lock`) — shadow above

### Overlay States

| State | Description |
|-------|------------|
| Closed | display:none |
| Entering | backdrop: opacity 0→1 + blur 0→8px (220ms); surface: scale(0.98)+translateY(10px)+opacity:0 → normal (180ms opacity, 220ms transform) |
| Open | fully visible |
| Closing | reverse enter |

### Mobile Sheet (`modal-sheet-surface`)

**Style:** max-height=90dvh, radius-sheet (top corners 24px only)

**::before:** drag handle — 32×4px, radius=2px, bg=slate-300, centered top

**States:** same enter/exit + different transform origin (bottom up)

**Figma frames needed:**
- [ ] Modal closed (empty)
- [ ] Modal open — compact/form/detail/wide
- [ ] Modal entering (mid-animation)
- [ ] Modal with sticky header
- [ ] Modal with scroll lock footer
- [ ] Mobile sheet (bottom up)
- [ ] Mobile sheet entering
- **= 10 frames per modal size**

---

## Komponen: Toolbar Patterns

### toolbar-actions (`.toolbar-actions`)

**Layout:** `display: grid; grid-template-columns: repeat(auto-fit, minmax(72px, 1fr)); gap: 8px`

Dipakai untuk grup tombol di kanan toolbar.

### compact-period-toolbar

**Layout:**
- Mobile: flex-col
- Desktop: flex-row, label + controls grid

**Sub:**
- `.compact-period-toolbar__label` — fs-overline, muted
- `.compact-period-toolbar__controls` — grid buttons

### mobile-toolbar-stack

**Layout:** flex-col pada mobile, flex-row 640px+

---

## Komponen: Sidebar & Navigation

### dashboard-sidebar-shell

**Dimensi:** fixed left, width=240px (open) / 0 (closed), h=100dvh

**Transition:** width + transform + box-shadow, 440ms cubic-bezier(0.22, 1, 0.36, 1)

**States:**
- Open (desktop default)
- Closed/collapsed
- Mobile overlay mode

### sidebar-accordion-panel

**Transition:** max-height (0→360px) + opacity (0→1) + transform (translateY(-8px)→0), 280ms cubic-bezier(0.22, 1, 0.36, 1)

**States:**
- Collapsed (max-height: 0, invisible)
- Expanding (mid-animation)
- Expanded (visible)
- Collapsing (mid-animation)

### nav-icon

Icon dalam sidebar nav item.

**States:**
- Inactive: slate-400
- Active (`.sidebar-nav-item-active`): accent

**Figma frames needed:**
- [ ] Sidebar open
- [ ] Sidebar collapsed
- [ ] Nav item inactive
- [ ] Nav item active
- [ ] Nav item hover
- [ ] Accordion closed
- [ ] Accordion open
- **= 7 frames**

---

## Komponen: Settings

### settings-surface

**Style:** bg=60% opacity slate-50 pattern

### settings-empty-state

**Style:** dashed border, centered, radius=12px

**Anatomy:**
- `.settings-empty-state__icon` — 48×48px, radius=12px, accent-tint bg, icon inside
- Title text
- Body text

### info-panel-soft / surface-panel-soft

**Style:** radius=16px, bg=accent-5 or slate-50, p=16px

---

## Komponen: Status States (cross-component)

Semua komponen tabel/list/form butuh frame ini:

| State | Trigger | Visual |
|-------|---------|--------|
| **Loading** | async fetch | skeleton rows (shimmer) atau `.fa-spinner fa-spin` |
| **Empty** | no data | `.table-empty-state` — dashed border, icon, copy |
| **Error** | API fail | toast notification merah + icon `fa-triangle-exclamation` |
| **Success** | action complete | toast notification hijau + icon `fa-check-circle` |

### Skeleton Loading

**Style:** shimmer animation, bg=slate-200/50, radius=8px, animated gradient

**Shapes:**
- Text line (full width, h=12px)
- Text line short (50% width, h=10px)
- Circle (32×32px)
- Table row (h=44px, full width)
- Card block (full width, h=80px)

---

## Komponen: Toast Notification

**Dimensi:** fixed bottom, centered, p=12px 16px, radius=16px, shadow=elevated

**Animation:** translate(-50%, 10px) scale(0.985) → normal (180ms opacity + 220ms transform)

| Type | Background | Icon | Text |
|------|-----------|------|------|
| Success | green-50 | `fa-check-circle` green | green-800 |
| Error | red-50 | `fa-triangle-exclamation` red | red-800 |
| Info | blue-50 | `fa-circle-info` blue | blue-800 |
| Warning | orange-50 | `fa-triangle-exclamation` orange | orange-800 |

**States:**
- Entering
- Visible
- Exiting

---

## Komponen: Summary Chips

**Grid wrapper (`.chips-grid`):** auto-fit, 4 col mobile, 5 col desktop

### metric-chip-card

**Anatomy:**
- Label: `--fs-overline-xs` uppercase muted
- Value: `--fs-heading-sm` fw=600
- Optional: sub-label, trend indicator

**Variants:**
- `.metric-chip-card` — default align-left
- `.metric-chip-card--align-end` — right-aligned

---

## Animasi & Transitions — Inventory untuk Figma

### Butuh Smart Animate / Variant Connection

| Animation | Duration | Easing | Trigger |
|-----------|----------|--------|---------|
| Fade in/out | 150ms | ease | `v-if` show/hide |
| Modal enter | 220ms transform, 180ms opacity | ease-out | modal open |
| Modal exit | reverse | ease-in | modal close |
| Sidebar open | 440ms | cubic-bezier(0.22,1,0.36,1) | toggle |
| Popover enter | 220ms transform, 180ms opacity | ease-out | click trigger |
| Popover flip-up | same, bottom origin | — | near viewport bottom |
| Accordion expand | 280ms | cubic-bezier(0.22,1,0.36,1) | nav click |
| Segmented slide | 340ms | cubic-bezier(0.22,1,0.36,1) | tab click |
| Toast enter | 220ms transform, 180ms opacity | ease-out | notification |
| Button press | 180ms scale(0.97) | ease | click |
| Chevron rotate | 220ms | cubic-bezier(0.22,1,0.36,1) | select open |
| Card reveal | 320ms stagger | cubic-bezier(0.22,1,0.36,1) | page load |
| Logo breathe | 2s infinite | ease-in-out | loading screen |

### Keyframes Reference

```
cardReveal: opacity:0, translateY:10px, scale:0.992 → opacity:1, translateY:0, scale:1
fadeIn:     opacity:0, translateY:8px → opacity:1, translateY:0
logo-breathe: scale:1 → scale:1.05 → scale:1
```

---

## Icons — Font Awesome Set

### Navigation & UI Control
`fa-bars` · `fa-chevron-down` · `fa-chevron-left` · `fa-chevron-right` · `fa-angles-left` · `fa-angles-right` · `fa-arrow-left` · `fa-arrow-right` · `fa-arrow-up` · `fa-arrow-down` · `fa-arrow-up-right-from-square` · `fa-search` · `fa-magnifying-glass` · `fa-filter` · `fa-sort` · `fa-xmark` · `fa-sliders`

### Documents & Files
`fa-file-pdf` · `fa-file-excel` · `fa-file-lines` · `fa-file-invoice-dollar` · `fa-file-shield` · `fa-folder-open` · `fa-box-open` · `fa-book-open` · `fa-clipboard-list` · `fa-floppy-disk` · `fa-upload` · `fa-download`

### Business & Analytics
`fa-chart-bar` · `fa-chart-line` · `fa-chart-pie` · `fa-gauge` · `fa-arrow-trend-up` · `fa-arrow-trend-down` · `fa-money-bill-wave` · `fa-sack-dollar` · `fa-coins` · `fa-wallet` · `fa-calculator` · `fa-percent` · `fa-fire` · `fa-medal` · `fa-trophy` · `fa-star` · `fa-ranking-star`

### User & Profile
`fa-user` · `fa-user-group` · `fa-user-pen` · `fa-user-plus` · `fa-user-gear` · `fa-user-tag` · `fa-address-card` · `fa-headset` · `fa-handshake` · `fa-shield` · `fa-shield-halved` · `fa-user-shield`

### Content & Media
`fa-camera` · `fa-photo-film` · `fa-video` · `fa-film` · `fa-clapperboard` · `fa-image` · `fa-heart` · `fa-bookmark` · `fa-comment` · `fa-comments` · `fa-note-sticky` · `fa-pen` · `fa-pen-to-square`

### Social & Brands
`fa-facebook` · `fa-instagram` · `fa-threads` · `fa-x-twitter` · `fa-tiktok` · `fa-youtube` · `fa-google-drive` · `fa-google`

### Status & Indicators
`fa-check` · `fa-circle-check` · `fa-check-double` · `fa-circle-xmark` · `fa-circle-info` · `fa-circle-question` · `fa-triangle-exclamation` · `fa-lock` · `fa-spinner` (+ `fa-spin`) · `fa-hourglass-half` · `fa-link-slash` · `fa-eye` · `fa-circle-notch`

### Commerce & Product
`fa-cart-shopping` · `fa-bag-shopping` · `fa-store` · `fa-box` · `fa-boxes-stacked` · `fa-tag` · `fa-tags` · `fa-truck` · `fa-building`

### Calendar & Time
`fa-calendar` · `fa-calendar-days` · `fa-calendar-check` · `fa-calendar-xmark` · `fa-clock` · `fa-clock-rotate-left`

### Misc
`fa-lightbulb` · `fa-layer-group` · `fa-shapes` · `fa-bullseye` · `fa-bullhorn` · `fa-microscope` · `fa-seedling` · `fa-piggy-bank` · `fa-rotate-left` · `fa-rotate-right` · `fa-trash` · `fa-trash-can` · `fa-plus` · `fa-minus` · `fa-link` · `fa-share` · `fa-share-nodes` · `fa-rectangle-ad` · `fa-mobile-screen` · `fa-tower-broadcast` · `fa-eraser` · `fa-circle` · `fa-circle-dot`

**Icon modifiers:** `fa-solid` · `fa-regular` · `fa-brands` · `fa-spin` (spinner animation)

---

## Responsive — Breakpoints

| Breakpoint | Pixel | Behavior perubahan |
|-----------|-------|-------------------|
| Mobile | < 640px | toolbar stacked, sidebar hidden, 2-col grid |
| Tablet | 640–767px | toolbar partial row, sidebar overlay |
| Desktop | ≥ 768px | sidebar fixed, toolbar row, 4-col grid |

**Mobile-specific components:**
- `.mobile-sheet` — bottom drawer, drag handle visible
- `.mobile-record-card` — card layout replacing table row
- `.mobile-data-card` — 2-col summary grid
- `.mobile-toolbar-stack` — vertical button stack

---

## Summary Frame Count untuk Figma

| Komponen | Frame |
|----------|-------|
| primary-cta-button (4 states × 4 variants) | 16 |
| secondary-cta-button (4 states × 4 variants) | 16 |
| icon-utility-button (4 states × 4 variants) | 16 |
| table-action-button (2 states × 5 variants) | 10 |
| select-trigger-button (4 states × 4 variants) | 16 |
| calendar-day-button (7 states) | 7 |
| segmented-control (6 frames) | 6 |
| form-input (6 states × 7 variants) | 42 |
| form-textarea (4 states) | 4 |
| search-select-popover (14 frames) | 14 |
| dashboard-summary-card (5 frames) | 5 |
| entity-badge (4 variants) | 4 |
| mini-stat-chip (2 variants) | 2 |
| status-pill (3 variants) | 3 |
| modal (10 frames × 4 sizes) | 40 |
| sidebar & nav (7 frames) | 7 |
| table shell + states | 8 |
| toast (3 states × 4 types) | 12 |
| skeleton loading (5 shapes) | 5 |
| **TOTAL** | **~233 frames** |

---

## Checklist Pembuatan SVG Figma

### Foundation
- [ ] Color palette (semua --ppp-* tokens)
- [ ] Typography scale (semua --fs-* tokens, contoh text)
- [ ] Spacing guide (4pt grid visual)
- [ ] Icon set (semua FA icons sebagai SVG komponen)

### Buttons
- [ ] primary-cta-button (semua states + variants)
- [ ] secondary-cta-button (semua states + variants)
- [ ] icon-utility-button (semua states + variants)
- [ ] table-action-button (semua states + variants)
- [ ] select-trigger-button (semua states + variants)
- [ ] calendar-day-button (semua states)
- [ ] segmented-control (standard + ios + equal)

### Inputs & Select
- [ ] form-input (semua states + variants)
- [ ] form-textarea (semua states)
- [ ] toolbar-native-select (semua states)
- [ ] search-select-popover (semua states + option states)

### Cards
- [ ] dashboard-summary-card (default + hover + compact)
- [ ] section-card (with body + shell variant)
- [ ] stat-card
- [ ] settings-panel-card + item
- [ ] mobile-record-card
- [ ] mobile-data-card

### Badges & Status
- [ ] entity-badge (4 variants)
- [ ] status-pill (3 variants)
- [ ] mini-stat-chip (2 variants)
- [ ] summary-counter-pill
- [ ] multi-select-chip
- [ ] metric-chip-card (2 variants)

### Table
- [ ] table shell (full anatomy)
- [ ] table header (accent bg, sortable states)
- [ ] table row (default + hover + selected)
- [ ] table empty state
- [ ] table pager bar
- [ ] table toolbar shell (desktop + mobile)

### Modal & Dialog
- [ ] overlay backdrop
- [ ] modal-header-bar (standard + sticky)
- [ ] modal-footer-bar (standard + scroll-lock)
- [ ] modal (compact + form + detail + wide)
- [ ] mobile-sheet (bottom drawer)
- [ ] form-section-card (section dalam modal)

### Navigation
- [ ] sidebar (open + collapsed)
- [ ] nav item (inactive + active + hover)
- [ ] sidebar accordion (closed + open)
- [ ] header bar

### Settings
- [ ] settings-surface
- [ ] settings-empty-state
- [ ] info-panel-soft

### Cross-cutting States
- [ ] loading skeleton (5 shapes)
- [ ] toast (4 types × 3 states)
- [ ] empty state generic
- [ ] error state generic

### Responsive
- [ ] Mobile layout (375px)
- [ ] Tablet layout (768px)
- [ ] Desktop layout (1280px)
- [ ] Desktop wide (1440px)
