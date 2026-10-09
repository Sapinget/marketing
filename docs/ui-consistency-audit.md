# UI & Design System Consistency Audit

> Tanggal Audit: 2026-09-13  
> Scope: `resources/views/**/*.blade.php`, `resources/css/*.css`, `reference/design-system.blade.php`  
> Lead Crew: Design & Developers  

---

## Ringkasan Eksekutif

Audit ini memeriksa konsistensi visual, komponen antarmuka, dan sistem token di seluruh view Blade dan stylesheet dashboard. Ditemukan bahwa dashboard memiliki pondasi token yang cukup matang (`dashboard-shell.css`), namun terjadi **fragmentasi desain** karena 3 sistem styling berjalan bersamaan:

1. **Sistem Produksi Aktif** (`dashboard-shell.css`): Menggunakan font Figtree, aksen oranye (`#FFA500`), height 36px, radius 12px/16px/24px.
2. **Sistem Legacy / Demo Reference** (`reference/design-system.blade.php`, `app.css`): Menggunakan font Instrument Sans, aksen indigo/biru (`#4f63ff`), height 34px, radius 8px/12px/16px.
3. **Ad-Hoc Tailwind Utilities di Blade**: Campuran class manual (`bg-white border rounded-* p-*`, `text-[9px]`/`text-[10px]`, `px-4 py-2.5` vs `px-6 py-4`, inline style icon) yang melewati class komponen bersama.

Dampaknya adalah **1 fungsi komponen memiliki 2 hingga 4 gaya berbeda** tergantung menu mana yang dibuka pengguna.

---

## 1. Matriks Komponen dengan Variasi Gaya Berlebih

| Komponen / Fungsi | Varian Gaya yang Ditemukan | Masalah Konsistensi | Lokasi Sumber Utama |
| :--- | :--- | :--- | :--- |
| **Primary Action Button** | 1. `.primary-cta-button` (12px padding)<br>2. `.primary-cta-button` (14px padding)<br>3. `.primary-cta-button` reference (16px radius, dark `#0f172a`)<br>4. `.btn-primary` legacy | Duplikasi deklarasi CSS di file yang sama; perbedaan geometri dan warna default antara reference vs produksi. | `dashboard-shell.css:819-857`, `design-system.blade.php:99-124` |
| **Secondary Button** | 1. `.secondary-cta-button` (tanpa definisi CSS)<br>2. `.modal-secondary-button`<br>3. `.ghost-button`<br>4. `.small-button` | `.secondary-cta-button` dipakai di markup namun tidak memiliki rule CSS global, berpotensi unstyled. | `meta-feed.blade.php:212`, `design-system.blade.php:454` |
| **Form Input** | 1. `.form-input` (36px, r:16px)<br>2. `.form-input-compact` (36px, r:16px)<br>3. `.field-input` (44px, r:14px)<br>4. `.field-input.field-input-compact` (32px, r:10px) | Dua hierarki input yang saling bertolak belakang: `36px` di shell vs `44px`/`32px` di meeting theme. | `dashboard-shell.css:3619`, `dashboard-meeting-theme.css:59` |
| **Search Input** | 1. `.form-input-search` (h:36px, pl:40px)<br>2. `.search-shell input` (p:9px 12px)<br>3. `.input-shell input` | Ukuran icon dan padding input pencarian berbeda antara menu operasional dan modul reference. | `dashboard-shell.css:3706`, `app.css:930`, `input-claim.blade.php:20` |
| **Select Trigger** | 1. `.select-trigger-button` (r:12px, p:0 12px)<br>2. `.select-trigger-button-compact` (r:16px, p:0 14px)<br>3. `.select-trigger-button-form`<br>4. Reference select (h:34px, r:12px, fixed w:180px) | Radius tidak seragam (12px vs 16px) untuk komponen dropdown yang sama. | `dashboard-shell.css:915-1113`, `design-system.blade.php:119` |
| **Card / Panel** | 1. `.section-card` (r:24px, border-slate-100)<br>2. `.stat-card` (r:20px)<br>3. `bg-white radius-panel border p-5`<br>4. `rounded-2xl border p-2.5 md:p-3` | Banyak view mengabaikan `.section-card` dan meracik div custom dengan padding (`p-2.5` s/d `p-6`) dan radius acak. | `dashboard-shell.css:3399`, `meta-story.blade.php:48`, `budgeting.blade.php:22` |
| **Badges / Status Chips** | 1. `.badge` (p:5px 9px, text-body-sm)<br>2. `.badge-chip` (p:3px 10px, text:11px, solid)<br>3. Inline Tailwind spans (`text-[9px]`, `text-[10px]`, `px-2 py-0.5`) | Tidak ada standardisasi antara soft badge (pale) vs solid badge; ukuran font bervariasi dari 9px, 10px, hingga 12px. | `app.css:990`, `dashboard-meeting-theme.css:174`, `market-intelijen-harga.blade.php:163` |
| **Modal Dialog** | 1. Shell Modal (`.modal-sheet-surface`, `.modal-dialog-surface`)<br>2. Raw Service Modal (`bg-slate-900/60`, `radius-card`)<br>3. Meta Modal (`.glass-backdrop`, `radius-panel`) | Struktur markup modal berbeda: ada yang memakai container standar, ada yang menulis div absolute manual. | `dashboard-shell.css:3485`, `service.blade.php:114`, `meta-feed.blade.php:174` |
| **Table Header** | 1. `.table-header-cell` (h:3.75rem, font:700, var(--fs-body-sm))<br>2. Legacy `th` (font:800, p:12px 16px)<br>3. Reference `th` (font:10px, p:11px 14px, uppercase 0.12em) | Terdapat 3 ukuran header tabel yang saling bersaing: 10px vs 12px vs 13px. | `dashboard-shell.css:702`, `app.css:972`, `design-system.blade.php:155` |
| **Table Actions** | 1. `.table-action-button` (32x32, r:4px, hover stroke)<br>2. Reference `.table-action-edit/delete` (h:28px, r:8px, colored background)<br>3. Inline button icon (`w-7 h-7`) | Aksi tabel (edit/delete) memiliki dua paradigma: icon netral vs tombol berwarna terang. | `dashboard-shell.css:565`, `design-system.blade.php:121`, `proses-claim.blade.php:72` |

---

## 2. Tabrakan Token Desain (Token Collisions)

### A. Warna Aksen (`--ppp-accent`)
* **Produksi (`app.css:50`)**: `--ppp-accent: #FFA500` (Orange Amber).
* **Reference System (`design-system.blade.php:12`)**: `--ppp-accent: #4f63ff` (Indigo Blue).
* **Resiko**: Halaman demo/reference menampilkan identitas visual yang sama sekali berbeda dengan aplikasi operasional nyata.

### B. Warna Semantik Status
* Warna danger di `app.css` bernilai `#e11d48` (Rose), di reference bernilai `#dc2626` (Red), sedangkan di berbagai view Blade langsung memakai class utilitas Tailwind `bg-red-500`, `bg-red-600`, `text-red-500`.
* Status success menggunakan `#14b8a6` (Teal), `bg-emerald-500`, dan `bg-emerald-600` secara bergantian.

### C. Tipografi & Skala Font
* **Font Family**: Dashboard produksi memakai **Figtree**, sedangkan `design-system.blade.php` mengimpor dan memakai **Instrument Sans**.
* **Arbitrary Font Sizes**: Ditemukan penyebaran ukuran font acak di Blade: `text-[9px]`, `text-[10px]`, `text-[11px]`, `text-[12px]`, `text-xs`, `text-body-sm`. Microcopy tidak memiliki satu token resmi.

### D. Overlay & Backdrop Opacity
* `dashboard-shell.css:91-96` memaksa `.overlay-backdrop` dengan rule:
  ```css
  background: rgb(15 23 42 / 0.10) !important;
  ```
* Beberapa modal di view (`service.blade.php:115`, `budgeting.blade.php:287`) secara eksplisit menambahkan class `bg-slate-900/60` atau `bg-slate-900/40`. Karena adanya `!important`, intensitas gelap modal di-override menjadi sangat pudar (10%).

---

## 3. Detail Temuan per Kategori

### 1. Tombol (Buttons)
1. **Duplikasi Deklarasi CSS**:
   Pada `resources/css/dashboard-shell.css`, selector `.primary-cta-button` didefinisikan dua kali berturut-turut (baris 819 dan baris 842) dengan padding berbeda (`0 12px` vs `0 14px`).
2. **Missing Utility Class**:
   Class `.secondary-cta-button` dipanggil di `meta-feed.blade.php:212` tetapi tidak memiliki implementasi rule CSS di stylesheet mana pun.
3. **Pencampuran Utility Overrides**:
   Beberapa tombol menggunakan kombinasi class komponen bersama class utilitas warna yang menimpa style bawaan (contoh: `class="primary-cta-button ... bg-slate-900 text-white hover:bg-black"` di `settings.blade.php:106`).

### 2. Form & Dropdown
1. **Pemisahan Height Input**:
   Sebagian besar view memakai input `36px` (`.form-input-compact`), tetapi view meeting/auth memakai `.field-input` `44px` dan `32px`.
2. **Focus Rings**:
   Tidak ada token `:focus-visible` global. Sebagian input hanya mengubah `border-color`, sebagian memakai outline default browser, dan sebagian memakai ring shadow Tailwind.

### 3. Card & Kontainer
1. **Pola Padding Tidak Standar**:
   Area isi kartu bervariasi antara `p-2.5`, `p-3`, `p-4`, `p-5`, `p-6`, hingga `px-6 py-4`.
2. **Radius Inkonsisten**:
   Radius kontainer meloncat-loncat antara `rounded-xl` (12px), `rounded-2xl` (16px), `rounded-3xl` (24px), serta token `radius-panel` dan `radius-card`.

### 4. Tabel & Data Grid
1. **Header Tabel 10px vs 13px**:
   `tests/Feature/MarketingDashboardShellTest` mengharapkan tabel menggunakan header `10px` dan body `9px`, namun sebagian view tabel lain memakai `.table-header-cell` bawaan yang berukuran font `13px` (`var(--fs-body-sm)`).
2. **Table Action Button**:
   Tabel di modul Service dan Customer Service menggunakan `.table-action-button` dengan icon murni berukuran 32x32px, sedangkan di modul lain tombol aksi memakai teks label atau padding manual.

---

## 4. Rencana Tindakan & Rekomendasi Konsolidasi

### Fase 1: Perbaikan Bug & Konflik CSS Langsung (Quick Wins)
- [ ] Hapus deklarasi duplikat `.primary-cta-button` di `resources/css/dashboard-shell.css:842`.
- [ ] Buat implementasi eksplisit untuk `.secondary-cta-button` di `dashboard-shell.css`.
- [ ] Hilangkan `!important` pada `.overlay-backdrop` di `dashboard-shell.css:91` dan sediakan modifier `.overlay-backdrop--dark` (40%-60%) vs `.overlay-backdrop--light` (10%-20%).
- [ ] Selaraskan warna aksen `--ppp-accent` di `reference/design-system.blade.php` agar sama-sama `#FFA500` (Orange Amber) dan font Figtree.

### Fase 2: Standardisasi Primitif Komponen (Canonical Tokens)
- [ ] **Input & Select**: Kunci ukuran standar:
  - Default: `height: 36px`, `border-radius: 12px`, `font-size: 13px`.
  - Compact: `height: 32px`, `border-radius: 10px`, `font-size: 11px`.
- [ ] **Card System**:
  - `section-card`: Kontainer utama halaman (`border-radius: 20px`, `padding: 1.25rem` / `20px`).
  - `stat-card`: Kartu metrik/KPI (`border-radius: 16px`, `padding: 1rem` / `16px`).
- [ ] **Badge System**:
  - Standarkan 5 status semantik (`success`, `warning`, `danger`, `info`, `neutral`) dengan dua varian resmi: Soft (background 10%, teks pekat) dan Solid.
- [ ] **Focus Affordance**:
  - Terapkan satu token global focus ring: `box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.2)`.

### Fase 3: Refactoring Template Blade
- [ ] Migrasikan modal manual di `service.blade.php` dan `meta-feed.blade.php` ke komponen shell modal bersama (`.modal-sheet-surface`, `.modal-header-bar`, `.modal-footer-bar`).
- [ ] Ganti racikan div `bg-white border rounded-* p-*` di tab-tab analitik menjadi `.section-card`.
- [ ] Hapus inline style icon (`style="font-size:9px"`) dan ganti dengan utility icon terstandardisasi.

---

## 5. Ringkasan Status

Dengan mengonsolidasikan ketiga sistem styling di atas ke satu kontrak canonical di `dashboard-shell.css`, tampilan antarmuka akan konsisten di seluruh 40+ menu, menghilangkan inkonsistensi ukuran font, padding acak, serta perbedaan visual antar modul.
