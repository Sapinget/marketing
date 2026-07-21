# Roadmap: Rename `secondary-cta-*` → `primary-cta-*`

## Mapping
| Before | After |
|--------|-------|
| `secondary-cta-button` | `primary-cta-button` |
| `secondary-cta-success` | `primary-cta-button--success` (→ merged ke solid) |
| `secondary-cta-danger` | `primary-cta-button--danger` (→ merged ke solid) |
| `secondary-cta-neutral` | Dihapus (fallback ke base `.primary-cta-button`) |
| `secondary-cta-link` | Dihapus (fallback ke base `.primary-cta-button`) |

## Catatan
- Ghost CSS (ex-secondary success/danger/neutral/link) dihapus dari `dashboard-shell.css`
- Button pakai `--success` dan `--danger` sekarang pakai style solid (sama dgn primary yg sudah ada)
- Button pakai `--neutral` dan `--link` hanya fallback ke base style (tidak ada CSS khusus)
- `--neutral` dan `--link` dipertahankan di HTML (sebagai class saja, tanpa CSS rules)

## Files (36 total)

### CSS
- [x] `resources/css/dashboard-shell.css` — rename + hapus ghost CSS

### Blade Views
- [x] `resources/views/reference/design-system.blade.php`
- [x] `resources/views/dashboard/partials/shell/app-frame-sidebar-footer.blade.php`
- [x] `resources/views/dashboard/partials/menus/ads-log.blade.php`
- [x] `resources/views/dashboard/partials/menus/analytics.blade.php`
- [x] `resources/views/dashboard/partials/menus/asset-vendor-inventory.blade.php`
- [x] `resources/views/dashboard/partials/menus/auth-users.blade.php`
- [x] `resources/views/dashboard/partials/menus/bonus-report.blade.php`
- [x] `resources/views/dashboard/partials/menus/budgeting.blade.php`
- [x] `resources/views/dashboard/partials/menus/claim-garansi.blade.php`
- [x] `resources/views/dashboard/partials/menus/distribution.blade.php`
- [x] `resources/views/dashboard/partials/menus/harga-kompetitor.blade.php`
- [x] `resources/views/dashboard/partials/menus/ideation.blade.php`
- [x] `resources/views/dashboard/partials/menus/keep-barang.blade.php`
- [x] `resources/views/dashboard/partials/menus/laporan-event.blade.php`
- [x] `resources/views/dashboard/partials/menus/low-content.blade.php`
- [x] `resources/views/dashboard/partials/menus/master-plan.blade.php`
- [x] `resources/views/dashboard/partials/menus/meta-feed.blade.php`
- [x] `resources/views/dashboard/partials/menus/meta-story.blade.php`
- [x] `resources/views/dashboard/partials/menus/nama-stock.blade.php`
- [x] `resources/views/dashboard/partials/menus/order-online.blade.php`
- [x] `resources/views/dashboard/partials/menus/program-promo.blade.php`
- [x] `resources/views/dashboard/partials/menus/sell-out.blade.php`
- [x] `resources/views/dashboard/partials/menus/settings.blade.php`
- [x] `resources/views/dashboard/partials/menus/story.blade.php`
- [x] `resources/views/dashboard/partials/menus/top-content.blade.php`
- [x] `resources/views/dashboard/partials/menus/unboxing.blade.php`
- [x] `resources/views/dashboard/partials/menus/unit-ditanya.blade.php`

### Tests
- [x] `tests/Feature/MarketingDashboardShellTest.php`

### Docs
- [x] `docs/button-size-roadmap.md` (hanya histori, not changed)
- [x] `docs/component-catalog.md`
- [x] `docs/figma-component-map.md`
- [x] `docs/responsive-typography-refactor.md`
- [x] `docs/ui-spec.md`

### SVG Design
- [x] `resources/design/svg/02-buttons-secondary.svg`
- [x] `resources/design/svg/13-modal-anatomy.svg`

### Legacy (archive)
- [x] `resources/legacy/design-system-archive.html`
- [x] `resources/legacy/marketing-dashboard-source.html`

## Verification
- [x] `rg "secondary-cta"` — 0 hasil (hanya histori di button-size-roadmap.md:204)
- [x] Ghost CSS compound selectors dihapus dari `dashboard-shell.css`
- [x] Solid modifiers (`--accent`, `--info`, `--success`, `--danger`) masih utuh
- [x] SVGs title/label sudah "PRIMARY CTA"
