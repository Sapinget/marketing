# Color Palette

## Tujuan

Dokumen ini jadi referensi cepat untuk warna dashboard Marketing. Pakai file ini saat:

- bikin UI baru
- merapikan CSS lama
- mengganti utility hardcoded ke token global
- review konsistensi visual

Sumber utama token saat ini:

- [resources/css/app.css](../resources/css/app.css)
- [resources/css/dashboard-shell.css](../resources/css/dashboard-shell.css)
- [docs/UI_DESIGN_SYSTEM.md](./UI_DESIGN_SYSTEM.md)

## Core Tokens

`app.css` mendefinisikan token di dua tempat dengan tujuan berbeda:

| Blok | Nama prefix | Dipakai untuk |
|---|---|---|
| `@theme {}` | `--color-ppp-*` | Tailwind utility classes: `bg-ppp-accent`, `text-ppp-text` |
| `:root {}` | `--ppp-*` | Raw CSS: `var(--ppp-accent)`, dipakai di `dashboard-shell.css` dan inline style |

> **Penting:** Token di `@theme` dan `:root` **tidak otomatis sinkron**. Kalau tambah token baru, tambah di **keduanya**.
> Semua token `:root` sudah disinkron ke `@theme` — tidak ada yang tertinggal.

```css
/* @theme — untuk Tailwind utility class */
@theme {
  --color-ppp-accent: #4f63ff;
  --color-ppp-accent-dark: #3d4fdb;
  --color-ppp-sidebar: #f8fafc;
  --color-ppp-text: #0f172a;
  --color-ppp-muted: #94a3b8;
  --color-ppp-nav-text: #5066eb;
  --color-ppp-line: #e2e8f0;
  --color-ppp-bg: #f8fafc;
  --color-ppp-card: #ffffff;
  --color-ppp-danger: #b91c1c;
  --color-ppp-danger-fill: #dc2626;
}

/* :root — untuk raw CSS var() */
:root {
  --ppp-accent: #4f63ff;
  --ppp-accent-dark: #3d4fdb;
  --ppp-sidebar: #f8fafc;
  --ppp-text: #0f172a;
  --ppp-muted: #94a3b8;
  --ppp-nav-text: #5066eb;
  --ppp-line: #e2e8f0;
  --ppp-bg: #f8fafc;
  --ppp-card: #ffffff;
  --ppp-danger: #b91c1c;
  --ppp-danger-fill: #dc2626;
}
```

## Brand Palette

| Token | Hex | Kegunaan |
|---|---|---|
| `--ppp-accent` | `#4f63ff` | aksen utama, CTA primer, focus, active state |
| `--ppp-accent-dark` | `#3d4fdb` | hover/pressed untuk aksen utama |
| `--ppp-nav-text` | `#5066eb` | teks nav aktif, ikon state aktif |

## Neutral Palette

| Nama | Hex | Kegunaan |
|---|---|---|
| Slate 950 | `#0f172a` | teks utama, heading, dark button |
| Slate 800 | `#1e293b` | isi input / teks kuat |
| Slate 700 | `#334155` | teks body tegas |
| Slate 600 | `#475569` | subheading, helper text |
| Slate 500 | `#64748b` | secondary text |
| Slate 400 | `#94a3b8` | placeholder, muted label |
| Slate 300 | `#cbd5e1` | ikon field, border lembut |
| Slate 200 | `#e2e8f0` | line, border utama |
| Slate 100 | `#f1f5f9` | hover row, chip bg |
| Slate 50 | `#f8fafc` | page bg, sidebar bg, input bg |
| White | `#ffffff` | card, modal, dialog surface |

## Semantic Palette

| Status | Bg | Text | Contrast | Kegunaan |
|---|---|---|---|---|
| Info | `#eff6ff` | `#2563eb` | 4.75:1 ✓ | info badge, highlight ringan |
| Success | `#ecfdf5` | `#047857` | 5.21:1 ✓ | sukses, create, selesai |
| Warning | `#fffbeb` | `#b45309` | 4.84:1 ✓ | update, pending, warning |
| Danger | `#fef2f2` | `#b91c1c` | 5.91:1 ✓ | delete, error, destructive |
| Neutral | `#f1f5f9` | `#64748b` | — | status umum, badge counter |

> **Danger dua token:** `--ppp-danger` (`#b91c1c`, 5.91:1) untuk text/badge/ghost — wajib small text. `--ppp-danger-fill` (`#dc2626`, 4.62:1 white text) untuk filled button background default.
> `#059669` dan `#be185d` **tidak dipakai** — kedua gagal AA untuk small text.

## Surface Palette

| Token / Warna | Hex | Kegunaan |
|---|---|---|
| `--ppp-bg` | `#f8fafc` | background halaman utama |
| `--ppp-sidebar` | `#f8fafc` | background sidebar |
| `--ppp-card` | `#ffffff` | panel, card, modal |
| `--ppp-line` | `#e2e8f0` | border default |
| Soft surface | `#f8fafc` | input bg, subtle panel |
| Hover surface | `#f1f5f9` | hover card / row / trigger |

## Current Component Mapping

### Buttons

- Primary CTA: `--ppp-accent` + `--ppp-accent-dark`
- Secondary neutral: Slate 50 / Slate 200 / Slate 600
- Danger action: semantic danger

### Inputs

- default bg: Slate 50
- default border: Slate 200
- focus border: `--ppp-accent`
- muted icon: Slate 300 atau Slate 400

### Badges

- counter badge: Slate 100 + Slate 500
- info badge: blue bg + blue text
- success badge: green bg + green text
- warning badge: amber bg + amber text
- danger badge: rose/red bg + red text

### Overlays

- backdrop ringan: `bg-slate-900/20`
- backdrop modal normal: `bg-slate-900/40`
- backdrop modal kuat: `bg-slate-900/60`

## Existing Global Primitives Terkait

Primitive yang sudah pakai palette ini:

- `summary-counter-pill`
- `status-pill`
- `entity-badge`
- `metric-chip-card`
- `info-panel-soft`
- `date-trigger-button`
- `calendar-day-button`
- `primary-cta-button`
- `secondary-cta-button`
- `modal-primary-button`

Lihat definisi di [resources/css/dashboard-shell.css](../resources/css/dashboard-shell.css).

## Rules

- Selalu utamakan token `--ppp-*` untuk warna utama.
- Hindari hardcode hex baru kalau warna setara sudah ada.
- Kalau perlu warna baru, tambah dulu ke palette docs ini dan ke token CSS.
- Untuk status, pakai pasangan bg/text yang konsisten. Jangan campur palette warning dengan text neutral.
- Untuk selected state di calendar/date picker, teks harus putih jika background gelap/aksen.
- **CTA button background:** gunakan `--ppp-accent-dark` (`#3d4fdb`, white text 6.29:1) sebagai default. `--ppp-accent` (`#4f63ff`, 4.60:1) untuk focus ring/outline saja — lulus AA tapi tipis untuk small font.
- **Semua badge/label text kecil:** wajib lulus WCAG AA (≥4.5:1). Lihat kolom Contrast di Semantic Palette.

## Candidate Cleanup

Area yang masih layak dirapikan ke token global:

- `dashboard-shell.css` — **0 token dipakai**, semua hardcoded hex → ganti ke `var(--ppp-*)`
- `app.css` — 61 hardcoded hex di luar blok token, banyak duplikat nilai token yang sudah ada
- `@theme` block — tambah `--color-ppp-line`, `--color-ppp-bg`, `--color-ppp-card`, `--color-ppp-danger` agar bisa jadi Tailwind class
- utility `text-slate-*` yang berulang di blade — kandidat token atau Tailwind class
- badge semantik yang masih hardcoded per-menu
- gradient/header accent yang belum pakai token nama
- overlay opacity variants jika mau dibuat token class

## Referensi

- [docs/UI_DESIGN_SYSTEM.md](./UI_DESIGN_SYSTEM.md)
- [docs/date-picker-style-standardization.md](./date-picker-style-standardization.md)
