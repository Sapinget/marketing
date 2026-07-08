# UI Design System — Marketing Dashboard
> Pura Pura Ponsel · versi 2026-06-28

Dokumen ini mendefinisikan seluruh token desain, komponen, dan pola interaksi yang digunakan di Marketing Dashboard. Jadikan ini satu-satunya referensi sebelum menulis CSS atau markup baru.

---

## Daftar Isi

1. [Design Tokens](#1-design-tokens)
2. [Typography](#2-typography)
3. [Spacing & Layout](#3-spacing--layout)
4. [Radius System](#4-radius-system)
5. [Color Palette](#5-color-palette)
6. [Komponen — Shell & Navigation](#6-komponen--shell--navigation)
7. [Komponen — Buttons & Actions](#7-komponen--buttons--actions)
8. [Komponen — Form & Input](#8-komponen--form--input)
9. [Komponen — Data Display](#9-komponen--data-display)
10. [Komponen — Feedback & Overlay](#10-komponen--feedback--overlay)
11. [Pola Interaksi](#11-pola-interaksi)
12. [Responsive Breakpoints](#12-responsive-breakpoints)
13. [Panduan Penggunaan](#13-panduan-penggunaan)

---

## 1. Design Tokens

Semua token didefinisikan sebagai CSS custom properties di `:root`. **Jangan hardcode nilai warna atau font secara langsung.**

```css
:root {
  /* Brand */
  --ppp-accent:      #4f63ff;   /* Biru aksen primer */
  --ppp-accent-dark: #3d4fdb;   /* Hover/active state aksen */

  /* Surface */
  --ppp-bg:      #f8fafc;   /* Background halaman */
  --ppp-sidebar: #f8fafc;   /* Background sidebar */
  --ppp-card:    #ffffff;   /* Background card/panel */

  /* Text */
  --ppp-text:  #0f172a;   /* Teks utama */
  --ppp-muted: #94a3b8;   /* Teks sekunder/placeholder */

  /* Border */
  --ppp-line: #e2e8f0;   /* Garis pemisah */

  /* Semantic */
  --ppp-danger: #dc2626;   /* Error/hapus */
}
```

---

## 2. Typography

### Font Family

```css
font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
```

Font Instrument Sans di-load via `@fontsource/instrument-sans` dengan weight 400, 500, 600.

### Tier Tipografi

Dashboard memakai **4 tier utama**. Hindari ukuran di luar ini kecuali ada alasan khusus.

| Tier | Class Helper | Size | Weight | Keterangan |
|------|-------------|------|--------|------------|
| micro | `.type-micro` | 9px | 600 | Label terkecil — timestamp, badge sub-info |
| meta | `.type-meta` | 10px | 600–700 | Eyebrow, table header, nav sub |
| body | `.type-body` | 11px | 400–600 | Konten utama, form label, sidebar nav |
| title | `.type-title` | 13px | 700 | Judul row/card, nilai tabel |

```css
.type-micro { font-size: 9px;  font-weight: 600; }
.type-meta  { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
.type-body  { font-size: 11px; font-weight: 400; }
.type-title { font-size: 13px; font-weight: 700; color: var(--ppp-text); }
```

### Skala Heading

| Element | Size | Weight |
|---------|------|--------|
| `h1` (hero/module) | 26px | 700 |
| `h1` (mobile) | 22px | 700 |
| `h2` (topbar) | 18px | 700 |
| `h3` (panel) | 16px | 700 |

### Eyebrow

Teks kecil uppercase di atas heading untuk konteks/grup.

```css
.eyebrow {
  color: #475569;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}
```

---

## 3. Spacing & Layout

### Skala Spacing

| Nilai | Contoh Penggunaan |
|-------|-------------------|
| 4px | Gap ikon dalam komponen |
| 6px | Padding ikon kecil |
| 8px | Gap tombol, padding badge |
| 10px | Gap avatar-teks |
| 12px | Gap nav-icon, padding card kecil |
| 14px | Padding topbar mobile, gap topbar |
| 16px | Padding panel, padding KPI card |
| 20px | Padding nav item, padding hero/module header |
| 24px | Padding page-view, gap sidebar |

### Layout Grid

```
Desktop (≥1081px):
┌────────────┬──────────────────────────────┐
│  Sidebar   │         Main Panel           │
│  240px     │     calc(100% - 240px)       │
└────────────┴──────────────────────────────┘

Mobile (≤768px):
┌──────────────────────────────────────────┐
│               Main Panel                 │
│                 100%                     │
└──────────────────────────────────────────┘
(sidebar overlay geser dari kiri, backdrop gelap)
```

### KPI Grid

```css
/* Desktop: 4 kolom */
.kpi-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }

/* ≤1080px: 2 kolom */
@media (max-width: 1080px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

/* ≤768px: 1 kolom */
@media (max-width: 768px) { .kpi-grid { grid-template-columns: 1fr; } }
```

### Content Grid

```css
/* Dashboard utama: panel kiri lebih lebar */
.content-grid { grid-template-columns: 1.1fr 0.9fr; }

/* ≤1080px: 1 kolom */
@media (max-width: 1080px) { .content-grid { grid-template-columns: 1fr; } }
```

---

## 4. Radius System

Empat token radius. **Pilih berdasarkan ukuran/peran elemen**, bukan selera.

| Token | Nilai | Dipakai Untuk |
|-------|-------|---------------|
| `r-sm` | 12px | Input kecil, tombol kecil, badge |
| `r-md` | 16px | CTA utama, modal footer tombol |
| `r-lg` | 24px | Card mobile, section card |
| `r-xl` | 28px | Panel besar, section utama |
| `r-modal` | 32px | Modal shell |

```css
.r-sm    { border-radius: 12px; }
.r-md    { border-radius: 16px; }
.r-lg    { border-radius: 24px; }
.r-xl    { border-radius: 28px; }
.r-modal { border-radius: 32px; }
```

---

## 5. Color Palette

### Warna Utama

| Nama | Hex | Penggunaan |
|------|-----|------------|
| Accent | `#4f63ff` | Tombol aktif, link, focus ring |
| Accent Dark | `#3d4fdb` | Hover state aksen |
| Slate 950 | `#0f172a` | Teks utama, tombol dark |
| Slate 700 | `#334155` | Teks tabel |
| Slate 600 | `#475569` | Teks sekunder, eyebrow |
| Slate 500 | `#64748b` | Deskripsi, keterangan |
| Slate 400 | `#94a3b8` | Placeholder, muted |
| Slate 300 | `#cbd5e1` | Ikon input |
| Slate 200 | `#e2e8f0` | Border/garis |
| Slate 100 | `#f1f5f9` | Row hover, divider tabel |
| Slate 50 | `#f8fafc` | Background halaman/sidebar |

### Warna Semantik (Badge & Status)

| Status | Background | Teks |
|--------|-----------|------|
| Progress/Active | `#eff6ff` | `#2563eb` |
| Done/Success | `#ecfdf5` | `#059669` |
| Draft/Neutral | `#f8fafc` | `#64748b` |
| Danger | `#fef2f2` | `#b91c1c` |

---

## 6. Komponen — Shell & Navigation

### 6.1 Loading Screen

Ditampilkan saat aplikasi pertama kali load sebelum auth check selesai.

```html
<div class="loading-screen" data-loading>
  <div class="loading-logo">
    <img src="..." alt="Logo">
  </div>
  <div class="loading-copy">
    <strong>Pura Pura Ponsel</strong>
    <span>Menyiapkan Dashboard...</span>
  </div>
</div>
```

- Logo animasi: `logoPulse` (scale 1 → 1.04 → 1, 1.6s infinite)
- Logo container: 80×80px, `border-radius: 28px`, border biru muda
- Hilang via `.is-hidden` setelah auth check

### 6.2 Sidebar

Fixed, lebar 240px di desktop. Slide-in overlay di mobile.

```
┌─────────────────────┐
│  [Logo]  Brand Name │  ← .brand-row (64px tinggi)
│           Subtitle  │
├─────────────────────┤
│  ▣ Dashboard        │  ← .nav-item
│  ▾ Konten           │  ← .nav-group-toggle
│    ├ Master Plan    │  ← .nav-subitem
│    └ Unboxing       │
│  ▾ Marketing        │
├─────────────────────┤
│  [A]  admin         │  ← .user-card
│       Super Admin   │
└─────────────────────┘
```

**States nav:**
- Default: `color: #64748b`, background transparent
- Hover: `background: #f1f5f9`, `color: #475569`
- Active: gradient `#4f63ff → #3d4fdb`, `color: #fff`

**Group Toggle:**
- Chevron rotate 180° saat `.nav-group.is-open`
- Transition: `transform 0.2s ease`

### 6.3 Topbar

Sticky, `height: 64px`, background `rgba(255,255,255,0.9)`.

```
┌──────────────────────────────────────────────┐
│ [☰]  Pura Pura Ponsel  [Judul]  [Profile] [Logout] │
└──────────────────────────────────────────────┘
```

- Icon burger hanya visible di mobile (`≤768px`)
- Tombol "Profile" disembunyikan di mobile

### 6.4 User Card

```html
<div class="user-card">
  <div class="avatar" data-user-initial>A</div>
  <div class="user-meta">
    <div data-user-name>admin</div>
    <span data-user-role>Super Admin</span>
  </div>
</div>
```

- Avatar: 36×36px, `border-radius: 12px`, background `#0f172a`
- Nama: 11px bold
- Role: 9px uppercase, muted

---

## 7. Komponen — Buttons & Actions

### 7.1 Hierarki Tombol

```
Primer          → .dark-button / .primary-cta-button
Sekunder        → .ghost-button / .secondary-cta-*
Aksi Baris Tabel→ .table-action-edit / .table-action-delete
Utilitas Ikon   → .icon-utility-button
Kecil / Filter  → .small-button / .filter-pills button
Modal           → .modal-primary-button / .modal-secondary-button / .modal-danger-button
Segmented       → .segmented-control + .segmented-control__item
Filter Trigger  → .filter-trigger-button
```

### 7.2 Dark Button (CTA Primer)

```html
<button type="button" class="dark-button">Tambah Data</button>
```

```css
.dark-button {
  background: #0f172a;
  border: 0;
  border-radius: 12px;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  min-height: 34px;
  padding: 0 12px;
  transition: background 0.18s ease;
}
.dark-button:hover { background: #020617; }
```

### 7.3 Ghost Button (CTA Sekunder)

```html
<button type="button" class="ghost-button">Export Excel</button>
```

```css
.ghost-button {
  background: #fff;
  border: 1px solid var(--ppp-line);
  border-radius: 12px;
  color: #475569;
  font-size: 11px;
  font-weight: 700;
  min-height: 34px;
  padding: 0 12px;
}
```

### 7.4 Primary CTA Button

Helper standar untuk aksi tambah/simpan di toolbar modul — gunakan ini, bukan dark-button inline.

```html
<button type="button" class="primary-cta-button">Tambah Plan</button>
```

### 7.5 Secondary CTA Family

Untuk aksi Export, Print, Buka Link — konsisten lintas modul.

```html
<button type="button" class="secondary-cta-button secondary-cta-excel">Export Excel</button>
<button type="button" class="secondary-cta-button secondary-cta-link">Buka Link</button>
<button type="button" class="secondary-cta-button secondary-cta-print">Print</button>
```

### 7.6 Table Action Family

Aksi per-baris di tabel (Edit, Hapus). Selalu gunakan `aria-label`.

```html
<button class="table-action-edit" aria-label="Edit">
  <i class="fa-solid fa-pen"></i>
</button>
<button class="table-action-delete" aria-label="Hapus">
  <i class="fa-solid fa-trash"></i>
</button>
```

### 7.7 Icon Utility Family

Ikon utilitas di toolbar (filter, refresh, dll).

```html
<button class="icon-utility-button" aria-label="Filter">
  <i class="fa-solid fa-filter"></i>
</button>
```

### 7.8 Filter Trigger Button

Tombol tanggal/filter bulan yang muncul di toolbar modul.

```html
<button type="button" class="filter-trigger-button">
  Juni 2026 <i class="fa-solid fa-chevron-down"></i>
</button>
```

### 7.9 Modal Button Family

**Footer modal selalu pakai helper ini — jangan inline.**

```html
<button type="button" class="modal-secondary-button">Batal</button>
<button type="submit" class="modal-primary-button">Simpan</button>
<button type="button" class="modal-danger-button">Hapus Permanen</button>
```

### 7.10 Filter Pills

```html
<div class="filter-pills">
  <button type="button" class="is-active">Semua</button>
  <button type="button">Bulan Ini</button>
  <button type="button">Aktif</button>
</div>
```

- Default: background putih, border `--ppp-line`
- Active (`.is-active`): background `#0f172a`, teks putih

### 7.11 Segmented Control

Untuk pilihan biner atau tiga pilihan (Board/List, Ganjil/Genap). Jangan pakai radio button.

```html
<div class="segmented-control" role="group">
  <button class="segmented-control__item is-active">Board</button>
  <button class="segmented-control__item">List</button>
</div>
```

### 7.12 Small Button

```html
<button type="button" class="small-button">Lihat</button>
```

Variant ghost untuk panel header — font lebih kecil, padding lebih tipis.

### 7.13 Icon Button (Mobile Hamburger)

```html
<button type="button" class="icon-button" aria-label="Buka sidebar">
  <i class="fa-solid fa-bars"></i>
</button>
```

36×36px. Hanya visible di `≤768px`.

---

## 8. Komponen — Form & Input

### 8.1 Input Shell (dengan ikon)

```html
<label class="input-shell">
  <span class="input-icon"><i class="fa-solid fa-user"></i></span>
  <input name="username" type="text" placeholder="Username" autocomplete="username">
</label>
```

```css
.input-shell input {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 16px;
  font-size: 12px;
  padding: 15px 16px 15px 40px;
  transition: border-color 0.18s, box-shadow 0.18s;
  width: 100%;
}
.input-shell input:focus {
  border-color: var(--ppp-accent);
  box-shadow: 0 0 0 3px rgba(79, 99, 255, 0.08);
}
```

### 8.2 Search Shell

```html
<label class="search-shell">
  <span>Cari</span>
  <input type="search" placeholder="Search data...">
</label>
```

- Label uppercase 10px di atas input
- Max-width: 320px

### 8.3 Select Trigger Button

Untuk dropdown custom (bukan native `<select>`).

```html
<button type="button" class="select-trigger-button">
  Pilih Brand <i class="fa-solid fa-chevron-down"></i>
</button>
```

### 8.4 Form Group

Form panjang (Master Plan, Claim Garansi) dibagi dalam grup tematik.

```html
<div class="form-group">
  <h4 class="form-group-title">Informasi Konten</h4>
  <!-- fields -->
</div>
```

---

## 9. Komponen — Data Display

### 9.1 KPI Card

```html
<div class="kpi-card">
  <span>Konten Bulan Ini</span>
  <strong>128</strong>
  <small>Master Plan</small>
</div>
```

Struktur: label atas (muted uppercase) → angka besar → sumber/konteks (muted).

### 9.2 Panel

Kontainer konten dengan header terpisah.

```html
<section class="panel">
  <div class="panel-header">
    <div>
      <span class="eyebrow">Status</span>
      <h3>Pipeline Konten</h3>
    </div>
    <button class="small-button">Lihat</button>
  </div>
  <!-- body: status-list / mini-table / data-table-wrap -->
</section>
```

### 9.3 Status List & Mini Table

Digunakan di dalam panel dashboard — list sederhana key-value.

```html
<div class="status-list">
  <div><span>NOT STARTED</span><strong>18</strong></div>
  <div><span>PROGRES</span><strong>27</strong></div>
  <div><span>DONE</span><strong>34</strong></div>
</div>
```

Row: `padding: 13px 16px`, border-top `#f1f5f9`, flex space-between.

### 9.4 Data Table

```html
<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th><th>Nama / Judul</th><th>Status</th>
        <th>Tanggal</th><th>Owner</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>001</td>
        <td>Judul Konten</td>
        <td><span class="badge progress">Progres</span></td>
        <td>28 Jun 2026</td>
        <td>admin</td>
        <td>
          <button class="table-action-edit" aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
          <button class="table-action-delete" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

Wrapper `.data-table-wrap` selalu `overflow-x: auto` — tabel punya `min-width: 760px`.

### 9.5 Badge

```html
<span class="badge progress">Progres</span>
<span class="badge done">Done</span>
<span class="badge draft">Draft</span>
```

```css
.badge { border-radius: 999px; font-size: 10px; font-weight: 800; padding: 5px 9px; text-transform: uppercase; }
.badge.progress { background: #eff6ff; color: #2563eb; }
.badge.done     { background: #ecfdf5; color: #059669; }
.badge.draft    { background: #f8fafc; color: #64748b; }
```

### 9.6 Dashboard Hero / Module Header

```html
<div class="dashboard-hero">
  <div>
    <div class="eyebrow">Marketing Dashboard</div>
    <h1>Ringkasan Operasional</h1>
    <p>Deskripsi singkat halaman.</p>
  </div>
  <div class="hero-badge">
    <span>Mode</span>
    <strong>Laravel 13</strong>
  </div>
</div>
```

Flex row, space-between. Di mobile: flex-direction column.

---

## 10. Komponen — Feedback & Overlay

### 10.1 Toast

Notifikasi singkat di bawah layar — auto-dismiss 2.2s.

```html
<div class="toast is-hidden" data-toast></div>
```

```js
// Trigger dari JS
toast.textContent = 'Data berhasil disimpan';
toast.classList.remove('is-hidden');
```

### 10.2 System Alert

Error penting yang butuh dismiss manual.

```html
<div class="system-alert is-hidden" data-alert>
  <div class="alert-icon">!</div>
  <div>
    <div class="alert-title">Sistem Error</div>
    <div class="alert-message" data-alert-message></div>
  </div>
  <button type="button" data-dismiss-alert aria-label="Tutup error">x</button>
</div>
```

- Posisi: fixed top 12px, center horizontal
- Warna: background `#fef2f2`, border `#fecaca`, teks `#b91c1c`

### 10.3 Sidebar Backdrop (Mobile)

```html
<button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Tutup sidebar"></button>
```

`background: rgba(15, 23, 42, 0.32)`, full screen, z-index 70. Ditampilkan via `.is-open`.

---

## 11. Pola Interaksi

### 11.1 Tab Navigation

```js
// Semua halaman dikontrol via data attribute
button[data-tab-button="master"] → tampilkan section[data-page="module"] dengan konten master

activateTab(key):
  - toggle .is-active di tombol nav
  - toggle .is-hidden di section halaman
  - simpan ke localStorage
```

### 11.2 Nav Group (Collapsible)

```js
group.classList.toggle('is-open');
// CSS otomatis: .nav-group.is-open .chevron { transform: rotate(180deg); }
// Items: toggle display block/none
```

### 11.3 Role Visibility

```js
// Elemen data-roles="non-teknisi" disembunyikan untuk Teknisi
const rule = element.dataset.roles;
const visible = rule === 'all'
  || (rule === 'teknisi' && isTeknisi())
  || (rule === 'non-teknisi' && !isTeknisi());
element.classList.toggle('is-hidden', !visible);
```

### 11.4 State Persisten (localStorage)

```js
localStorage.setItem('ppp_active_tab', tab);    // tab aktif
localStorage.setItem('ppp_user', JSON.stringify(user)); // session user
```

### 11.5 Utility Classes

| Class | Fungsi |
|-------|--------|
| `.is-hidden` | `display: none !important` |
| `.is-active` | State aktif (nav, filter pills, segmented) |
| `.is-open` | State terbuka (sidebar, nav group) |

---

## 12. Responsive Breakpoints

| Breakpoint | Kondisi | Perubahan |
|------------|---------|-----------|
| Desktop | `>1080px` | KPI 4 kolom, sidebar fixed 240px |
| Tablet | `769–1080px` | KPI 2 kolom, content-grid 1 kolom |
| Mobile | `≤768px` | Sidebar overlay, KPI 1 kolom, topbar compact |

### Mobile-Specific

- Burger icon visible, tombol "Profile" hidden
- Hero/module header: `flex-direction: column`
- `page-view padding: 14px` (vs 24px desktop)
- `h1`: 22px (vs 26px desktop)

---

## 13. Panduan Penggunaan

### DO ✅

- Selalu gunakan CSS variable `--ppp-*` untuk warna
- Gunakan 4 tier tipografi (micro/meta/body/title)
- Gunakan helper family: `primary-cta-button`, `secondary-cta-*`, `table-action-*`
- Semua tombol modal pakai `modal-primary-button` / `modal-secondary-button`
- Semua filter/toggle pakai `filter-trigger-button` atau `segmented-control`
- Wrap tabel dalam `.data-table-wrap` untuk overflow mobile
- Tambahkan `aria-label` di icon-only buttons

### DON'T ❌

- Jangan hardcode warna (misal `color: #4f63ff` langsung di markup)
- Jangan buat radius baru di luar 5 token radius
- Jangan tulis `px-5 py-2.5 rounded-xl font-semibold` inline di modal
- Jangan campur ukuran font 12px atau 14px tanpa alasan khusus
- Jangan pakai radio button untuk pilihan biner — gunakan segmented control

### Menambah Komponen Baru

1. Cek apakah ada komponen serupa yang bisa di-extend
2. Gunakan token warna dan radius yang sudah ada
3. Ikuti tier tipografi yang berlaku
4. Pastikan ada state hover, focus, active
5. Test di mobile 375px sebelum merge
6. Tambahkan ke `public/design-system.html` sebagai referensi visual

---

*Referensi visual interaktif: [`public/design-system.html`](../public/design-system.html)*
