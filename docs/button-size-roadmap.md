# Button Refactor Roadmap

## Prinsip

> Semakin sedikit global button class → semakin konsisten.

Target: **12 class → 7 class**. Konsolidasi dulu, resize setelah.

---

## Inventaris Saat Ini (12 class)

| # | Class | h | px | Dipakai di |
|---|-------|---|----|-----------|
| 1 | `.primary-cta-button` | 40px | 16px | semua toolbar "Tambah" |
| 2 | `.secondary-cta-button` | 40px | 16px | toolbar Excel/PDF/Reload |
| 3 | `.modal-primary-button` | 40px | **20px** | budgeting(32×), settings, profile, auth-users, avi |
| 4 | `.modal-secondary-button` | 40px | **20px** | budgeting(32×), settings, auth-users, avi |
| 5 | `.reset-filter-button` | 40px | 16px | unit-ditanya, order-online, keep-barang, claim-garansi, dll |
| 6 | `.filter-trigger-button` | 40px | 16px | ? (perlu cek — mungkin sama dengan no.7) |
| 7 | `.select-trigger-button` | 40px | 16px | sell-out, keep-barang, meta-feed, claim-garansi, dll |
| 8 | `.select-trigger-button-compact` | 36px | 12px | nama-stock, editor-performance |
| 9 | `.date-trigger-button` (full) | **48px** | 16px | TIDAK DIPAKAI sendiri — selalu combo dgn compact ↓ |
| 10 | `.date-trigger-button-compact` | 36px | 12px | master-plan, distribution, unboxing, dll (selalu `date-trigger-button date-trigger-button-compact`) |
| 11 | `.select-trigger-button-form` | **48px** | 16px | budgeting(65×), auth-users, avi |
| 12 | `.select-trigger-button-form-tight` | **48px** | 12px | budgeting |
| 13 | `.table-action-button` standard | 44px | 12px | **TIDAK DIPAKAI** (100% pakai compact modifier) |
| 14 | `.table-action-button.table-action-compact` | 32px | 0 | semua tabel |
| 15 | `.icon-utility-button` | 32px | 0 | utility icon |

---

## Target (7 class)

| # | Class | h target | Menggantikan |
|---|-------|---------|-------------|
| 1 | `.primary-cta-button` | **36px** | 1 + **3** (`modal-primary-button`) |
| 2 | `.secondary-cta-button` | **36px** | 2 + **4** (`modal-secondary-button`) + **5** (`reset-filter-button`) |
| 3 | `.select-trigger-button` | **36px** | 6 + 7 (`filter-trigger-button` alias) |
| 4 | `.select-trigger-button-compact` | 36px ✓ | 8 + **9+10** (`date-trigger-button[-compact]`) |
| 5 | `.select-trigger-button-form` | **44px** | 11 |
| 6 | `.select-trigger-button-form-tight` | **44px** | 12 |
| 7 | `.table-action-button.table-action-compact` | 32px ✓ | 14 + **deprecate 13** |
| — | `.icon-utility-button` | 32px ✓ | 15 (bukan button action, tetap sendiri) |

---

## Fase Konsolidasi (Blade + CSS)

### Fase K1 — Hapus `modal-primary-button` → ganti `primary-cta-button`

**Analisis**: Satu-satunya perbedaan = padding 20px vs 16px. Setelah padding disamakan, class ini identik.

Blade yang berubah:
- `budgeting.blade.php` (~32 occurrences) — `modal-primary-button` → `primary-cta-button`
- `settings.blade.php` (1) — `modal-primary-button` → `primary-cta-button`
- `profile.blade.php` (2) — `modal-primary-button` → `primary-cta-button`
- `auth-users.blade.php` (1) — `modal-primary-button` → `primary-cta-button`
- `asset-vendor-inventory.blade.php` (1) — `modal-primary-button` → `primary-cta-button`

CSS: hapus `.modal-primary-button` rule setelah selesai.

---

### Fase K2 — Hapus `modal-secondary-button` → ganti `secondary-cta-button`

Blade yang berubah: sama dengan K1 (budgeting, settings, auth-users, avi).

Pengganti default: `secondary-cta-button` (abu-abu, styling paling netral). Jika ada yang perlu warna spesifik, tambahkan modifier:
- Cancel/Tutup → `secondary-cta-button` (default)
- Danger action → `secondary-cta-button secondary-cta-danger`

CSS: hapus `.modal-secondary-button` rule.

---

### Fase K3 — Hapus `reset-filter-button` → ganti `secondary-cta-button secondary-cta-neutral`

**Analisis**: `reset-filter-button` = background slate, warna muted, uppercase, font sama. Identik dengan `secondary-cta-button secondary-cta-neutral`.

Blade yang berubah:
- `unit-ditanya.blade.php` (2)
- `order-online.blade.php` (2)
- `keep-barang.blade.php` (2)
- `claim-garansi.blade.php` (2)
- Dan file lain yang punya tombol Reset di toolbar

CSS: hapus `.reset-filter-button` rule.

---

### Fase K4 — Hapus `date-trigger-button` (full) + `date-trigger-button-compact` → ganti `select-trigger-button-compact`

**Analisis**: Semua penggunaan di blade adalah `class="date-trigger-button date-trigger-button-compact"`. Class full (`date-trigger-button` 48px) tidak pernah standalone. Class compact override semua property penting dari full. → Kedua class bisa diganti 1 class.

`date-trigger-button-compact` (36px) = `select-trigger-button-compact` (36px) secara visual.

Blade yang berubah (ganti `date-trigger-button date-trigger-button-compact` → `select-trigger-button-compact`):
- `master-plan.blade.php` (2)
- `distribution.blade.php` (2)
- `unboxing.blade.php` (2)
- `top-content.blade.php` (1)
- `low-content.blade.php` (1)
- `order-online.blade.php` (2)
- `unit-ditanya.blade.php` (2)
- `meta-story.blade.php` (1)
- `meta-feed.blade.php` (1)
- `harga-kompetitor.blade.php` (1)

CSS: hapus `.date-trigger-button` + `.date-trigger-button-compact` rules.

---

### Fase K5 — Hapus `filter-trigger-button` (alias ke `select-trigger-button`)

**Analisis**: CSS sudah menggabungkan keduanya dalam 1 rule (`.filter-trigger-button, .select-trigger-button`). Perlu cek apakah ada penggunaan di blade.

```bash
grep -rn 'filter-trigger-button' resources/views/ --include="*.php"
```

Jika ada → ganti ke `select-trigger-button`. Jika tidak ada → hapus CSS rule alias saja.

---

### Fase K6 — Deprecate `.table-action-button` standard (44px)

Tidak ada penggunaan di blade tanpa `.table-action-compact`. Hapus rule base 44px, sisakan hanya compact modifier.

Atau: ubah base `table-action-button` ke 36px (selaras toolbar) dan biarkan compact tetap 32px.

- [ ] Konfirmasi: `grep -rn "table-action-button[^.]" views/` → berapa yang tidak pakai compact?

---

## Fase Resize (CSS only, dashboard-shell.css)

Setelah konsolidasi selesai, semua ukuran difix di 1 tempat.

### Hierarki Ukuran Target

```
32px — table-action-compact, icon-utility-button
36px — primary-cta-button, secondary-cta-button, select-trigger-button, select-trigger-button-compact
44px — select-trigger-button-form, select-trigger-button-form-tight
```

### R1. Toolbar buttons 40px → 36px

```css
/* primary-cta-button */
min-height: 40px → 36px
padding: 0 16px → 0 14px

/* secondary-cta-button */
min-height: 40px → 36px
padding: 0 16px → 0 12px
font-size: 11px → var(--fs-body)   /* normalize literal */

/* select-trigger-button (incl. filter-trigger-button) */
min-height: 40px → 36px
padding: 12px 16px → 0 12px
font-size: 11px → var(--fs-body)   /* normalize literal */
```

### R2. Form triggers 48px → 44px

```css
/* select-trigger-button-form */
min-height: 48px → 44px
padding: 12px 16px → 0 14px
border-radius: 16px → 12px
font-size: 11px → var(--fs-body)   /* normalize literal */

/* select-trigger-button-form-tight */
min-height: 48px → 44px
padding: 12px → 0 10px
border-radius: 16px → 12px
```

### R3. Toolbar actions grid

```css
/* .toolbar-actions */
grid-template-columns: repeat(auto-fit, minmax(88px, 1fr))
                     → repeat(auto-fit, minmax(72px, 1fr))
```

---

## Checklist Eksekusi

### Konsolidasi (blade + CSS)

- [x] **K1** — `modal-primary-button` → `primary-cta-button` (budgeting×16, profile×4, auth-users×2, settings×1, avi×1 + CSS rules dihapus, --success/--danger ditambah ke primary)
- [x] **K2** — `modal-secondary-button` → `secondary-cta-button secondary-cta-neutral` (budgeting×16, auth-users×1, settings×1, avi×1 · total 19×)
- [x] **K3** — `reset-filter-button` → `secondary-cta-button secondary-cta-neutral` (unit-ditanya×2, claim-garansi×2, keep-barang×2, order-online×2, budgeting×1 · total 9×)
- [x] **K4** — `date-trigger-button[-compact]` → `select-trigger-button-compact` (14 file, 21×) + `date-trigger-button toolbar-trigger-field` → `select-trigger-button-form toolbar-trigger-field-form` (budgeting×19, avi×1)
- [x] **K5** — `filter-trigger-button` blade = 0 usage; CSS alias dihapus dari semua selectors
- [x] **K6** — `.table-action-button` base 44px → 36px; compact 32px tetap; orphan `.date-trigger-button` CSS rules dihapus (5 lokasi)

### Resize (CSS only)

- [x] **R1** — toolbar buttons 40px → 36px (primary-cta, secondary-cta, select-trigger)
- [x] **R2** — form triggers 48px → 44px (select-trigger-button-form, form-tight), radius 16px → 12px
- [x] **R3** — toolbar grid floor 88px → 72px

### Visual check

- [ ] Modal budgeting: semua button tidak terasa terlalu lebar / tinggi
- [ ] Toolbar semua tab: button compact dan rapi
- [ ] Form dalam modal: select + date terasa sejajar dengan input field
- [ ] Tabel: action compact tetap rapi (32px)

---

## Dampak Terbesar

`budgeting.blade.php` — file dengan ~65 `select-trigger-button-form` + ~32 `modal-primary/secondary-button`. Kerjakan K1+K2 di file ini secara batch (search & replace per class).
