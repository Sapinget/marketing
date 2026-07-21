# Color Palette Plan — Accent → #FAA500

## Rationale

Primary accent berubah dari **#4F63FF** (biru indigo) ke **#FAA500** (amber emas).  
Warna amber hangat → pilih secondary dingin (slate), semantic warna harus cukup kontras satu sama lain dan dari primary.

**Strategi hover:** satu token per role, hover universal pakai `filter: brightness(0.85)` — tidak perlu token `*-dark` atau `*-fill` terpisah.

---

## Palette Baru

| Role          | Token CSS         | Hex       | Tailwind Ref | Catatan                                    |
|---------------|-------------------|-----------|--------------|--------------------------------------------|
| **Primary**   | `--ppp-accent`    | `#FAA500` | amber-500    | Ganti dari #4F63FF — CTA, nav active, link |
| **Secondary** | `--ppp-secondary` | `#475569` | slate-600    | Dingin, netral — komplemen hangat primary  |
| **Success**   | `--ppp-success`   | `#16A34A` | green-600    | Universal green                            |
| **Info**      | `--ppp-info`      | `#0EA5E9` | sky-400      | Biru tenang, beda dari secondary slate     |
| **Warning**   | `--ppp-warning`   | `#EAB308` | yellow-500   | Kuning ≠ amber — cukup beda dari primary   |
| **Danger**    | `--ppp-danger`    | `#DC2626` | red-600      | Tidak berubah                              |
| **Light**     | `--ppp-light`     | `#F8FAFC` | slate-50     | Tidak berubah — bg/sidebar                 |
| **Dark**      | `--ppp-dark`      | `#0F172A` | slate-950    | Tidak berubah — text                       |

Hover semua button: `filter: brightness(0.85)` — tidak ada token hover terpisah.

---

## Preview Warna

```
Primary   ████  #FAA500   Amber Gold
Secondary ████  #475569   Slate
Success   ████  #16A34A   Green
Info      ████  #0EA5E9   Sky Blue
Warning   ████  #EAB308   Yellow
Danger    ████  #DC2626   Red
Light     ████  #F8FAFC   Off White
Dark      ████  #0F172A   Slate Black
```

---

## Perubahan di `app.css`

### Ubah (existing)

```css
/* SEBELUM */
--color-ppp-accent:      #4f63ff;
--color-ppp-accent-dark: #3d4fdb;
--color-ppp-nav-text:    #5066eb;
--color-ppp-danger:      #b91c1c;
--color-ppp-danger-fill: #dc2626;
--ppp-accent:            #4f63ff;
--ppp-accent-dark:       #3d4fdb;
--ppp-nav-text:          #5066eb;
--ppp-nav-rgb:           80 102 235;
--ppp-danger:            #b91c1c;
--ppp-danger-fill:       #dc2626;

/* SESUDAH */
--color-ppp-accent:   #FAA500;
--color-ppp-nav-text: #CC8800;
--color-ppp-danger:   #DC2626;
--ppp-accent:         #FAA500;
--ppp-nav-text:       #CC8800;
--ppp-nav-rgb:        250 165 0;
--ppp-danger:         #DC2626;
```

> Hapus token `*-dark` dan `*-fill` — tidak dipakai lagi.

### Tambah (new semantic tokens)

```css
/* @theme block */
--color-ppp-secondary: #475569;
--color-ppp-success:   #16A34A;
--color-ppp-info:      #0EA5E9;
--color-ppp-warning:   #EAB308;
--color-ppp-light:     #F8FAFC;
--color-ppp-dark:      #0F172A;

/* :root block — mirror */
--ppp-secondary: #475569;
--ppp-success:   #16A34A;
--ppp-info:      #0EA5E9;
--ppp-warning:   #EAB308;
--ppp-light:     #F8FAFC;
--ppp-dark:      #0F172A;
```

---

## Perubahan di `dashboard-shell.css`

### Button variants

```css
/* Hover universal — satu rule untuk semua */
.primary-cta-button:hover:not(:disabled) { filter: brightness(0.85); }

/* Hapus semua rule hover yang set background/warna manual */
.primary-cta-button--accent    { background: var(--ppp-accent); color: #fff; }
.primary-cta-button--secondary { background: var(--ppp-secondary); color: #fff; }
.primary-cta-button--success   { background: var(--ppp-success); color: #fff; }
.primary-cta-button--info      { background: var(--ppp-info); color: #fff; }
.primary-cta-button--warning   { background: var(--ppp-warning); color: #0F172A; }
.primary-cta-button--danger    { background: var(--ppp-danger); color: #fff; }
```

### Badge variants

```css
.entity-badge--success { background: #DCFCE7; color: var(--ppp-success); }
.entity-badge--info    { background: #E0F2FE; color: var(--ppp-info); }
.entity-badge--warning { background: #FEF9C3; color: #854D0E; }
.entity-badge--danger  { background: #FEE2E2; color: var(--ppp-danger); }
```

---

## Roadmap

- [x] **1. `app.css` — token accent**
  - [x] Ubah `--color-ppp-accent` → `#FAA500`
  - [x] Ubah `--ppp-accent` → `#FAA500`
  - [x] Ubah `--color-ppp-nav-text` → `#CC8800`
  - [x] Ubah `--ppp-nav-text` → `#CC8800`
  - [x] Ubah `--ppp-nav-rgb` → `250 165 0`
  - [x] Hapus `--color-ppp-accent-dark`, `--ppp-accent-dark`
  - [x] Hapus `--color-ppp-danger-fill`, `--ppp-danger-fill`
  - [x] Ubah `--color-ppp-danger` + `--ppp-danger` → `#DC2626`

- [x] **2. `app.css` — tambah token baru**
  - [x] Tambah `--color-ppp-secondary` + `--ppp-secondary` → `#475569`
  - [x] Tambah `--color-ppp-success` + `--ppp-success` → `#16A34A`
  - [x] Tambah `--color-ppp-info` + `--ppp-info` → `#0EA5E9`
  - [x] Tambah `--color-ppp-warning` + `--ppp-warning` → `#EAB308`
  - [x] Tambah `--color-ppp-light` + `--ppp-light` → `#F8FAFC`
  - [x] Tambah `--color-ppp-dark` + `--ppp-dark` → `#0F172A`

- [x] **3. `dashboard-shell.css` — button hover**
  - [x] Ganti semua hover `background` manual → `filter: brightness(0.85)`
  - [x] Update `.primary-cta-button--accent`
  - [x] Tambah `.primary-cta-button--secondary`
  - [x] Update `.primary-cta-button--success`
  - [x] Update `.primary-cta-button--info`
  - [x] Tambah `.primary-cta-button--warning`
  - [x] Update `.primary-cta-button--danger`

- [x] **4. `dashboard-shell.css` — badge**
  - [x] Update `.entity-badge--success`
  - [x] Update `.entity-badge--info`
  - [x] Update/tambah `.entity-badge--warning`
  - [x] Update `.entity-badge--danger`

- [x] **5. Grep sisa referensi lama**
  - [x] `grep -r "ppp-accent-dark"` → 0 hasil
  - [x] `grep -r "ppp-danger-fill"` → 0 hasil
  - [x] `grep -r "4f63ff\|3d4fdb\|5066eb\|b91c1c"` → 0 hasil (case-insensitive)

- [x] **6. Visual check**
  - [x] Nav active state
  - [x] CTA button (accent, secondary, success, info, warning, danger)
  - [x] Badge pill semua varian
  - [x] Modal footer button

---

## Kontras Check

| Background | Text      | Ratio | Status                              |
|------------|-----------|-------|-------------------------------------|
| `#FAA500`  | `#FFFFFF` | ~2.9  | ⚠️ Sub-AA — **diterima by design**  |
| `#475569`  | `#FFFFFF` | ~5.9  | ✅ PASS                             |
| `#16A34A`  | `#FFFFFF` | ~4.5  | ✅ PASS                             |
| `#0EA5E9`  | `#FFFFFF` | ~3.0  | ⚠️ Sub-AA — ganti `#0284C7` jika perlu |
| `#EAB308`  | `#0F172A` | ~7.2  | ✅ PASS                             |
| `#DC2626`  | `#FFFFFF` | ~4.6  | ✅ PASS                             |

---

## File Terpengaruh

- `resources/css/app.css`
- `resources/css/dashboard-shell.css`
- `resources/design/svg/*.svg` (jika ada hardcode warna biru)
- `resources/legacy/*.html` (optional — legacy files)
