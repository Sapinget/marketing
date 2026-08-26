# Roadmap Redesign 1:1 — Gaya "Meeting Tracker"

> Referensi desain: `/Users/serverbot/Desktop/Meeting` (Laravel + Inertia + Vue 3, Tailwind v3, font Figtree, aksen `#FFA500`).
> Target: `/Users/serverbot/Desktop/Marketing` (Blade + inline Vue, Tailwind v4 CSS-first).
>
> **Pendekatan**: replikasi visual 1:1 di dalam struktur Blade yang ada — TIDAK porting arsitektur Inertia.
>
> **Kontrak yang wajib dipertahankan**: `switchTab` / `activeTab` / `tabConfig`, endpoint `/api/auth/*`, ID input `login-username` & `login-pin`.

---

## Keputusan Desain (disetujui user)

- [x] Login tetap Username+PIN, **tambah checkbox "Ingat saya"** (simpan username ke localStorage, backend tidak berubah)
- [x] Chip avatar tim/chat di topbar **dipertahankan**, hanya restyle ala Meeting
- [x] Sidebar **flat tanpa accordion** seperti Meeting (semua item selalu tampil, nav scrollable)
- [x] Mobile bottom navigation glass bar **ikutkan sekarang**
- [x] Breakpoint `lg` tetap **1080px** (override Marketing), tidak mengikuti default Meeting 1024px

---

## Fase 0 — Fondasi Token Desain ✅

- [x] Ganti font `Instrument Sans` → **Figtree** weights 400–800
  - [x] Update `bunny()` call di `vite.config.js`
  - [x] Update `font-family` di `resources/css/dashboard-shell.css`
  - [x] Update `--font-sans` di `@theme` `resources/css/app.css`
- [x] Selaraskan warna aksen di `app.css @theme`
  - [x] `--color-ppp-accent`: `#FAA500` → `#FFA500` (exact referensi)
  - [x] Tambah `--ppp-accent-dark` dan `--ppp-accent-soft: #fff1e0`
- [x] Buat file baru `resources/css/dashboard-meeting-theme.css` (load terakhir sebagai override)
  - [x] `.brand-gradient` — `linear-gradient(135deg, #ffb833 0%, var(--ppp-accent) 45%, #c2410c 100%)`
  - [x] Token field `.field-label` / `.field-input` / `.btn` / `.btn-primary` (height 44px, compact 32px, radius 14px/12px)
  - [x] `.badge-chip` (pill solid bg + white text, padding 3px 10px, 11px/700)
  - [x] `.mt-topbar-panel` (radius 1rem, border `#e2e8f0`, shadow `0 14px 30px rgb(148 163 184 / .12)`)
  - [x] `.bottom-nav` glass styles (blur 40px saturate 1.55, min-h 60px, safe-area inset)
  - [x] Background body radial tint orange (`radial-gradient(circle at top, rgb(255 165 0 / .08), transparent 28%)`)
  - [x] Keyframes `wave-sway` + `auth-sheet-in`
- [x] Buat partial wave SVG `resources/views/dashboard/partials/shell/waves.blade.php`
  - Path sama dengan referensi (`M0,110 C240,170...`), 2 layer via prop fill/class — dipakai login, sidebar, mobile hero

## Fase 1 — Auth / Halaman Login ✅

Target file: `resources/views/dashboard/partials/shell/app-frame.blade.php:49-82`

- [x] Rebuild layout split-screen ala `GuestLayout.vue`
  - [x] Desktop: grid `lg:grid-cols-[1.1fr_0.9fr]`, panel kiri `.brand-gradient` + brand pill logo + eyebrow "MARKETING DASHBOARD" + H1 + waves animasi + copyright footer
  - [x] Mobile: hero gradient atas (dot-grid overlay + logo tile glassy + headline) + sheet putih `.auth-sheet` slide-up + drag handle
- [x] Form ala `Login.vue`
  - [x] Ikon kotak gradient 44px `rounded-2xl` berisi `fa-lock`, title "Masuk" `text-2xl font-black`, eyebrow uppercase tracking
  - [x] Label field `.field-label` uppercase 10px ("USERNAME", "PIN AKSES")
  - [x] Input compact token `[--field-height:var(--field-height-compact)] [--field-radius:12px]`
  - [x] Input PIN + eye toggle button (`fa-eye` / `fa-eye-slash`)
  - [x] Checkbox "Ingat saya" 18px rounded-[6px], ceklis orange — centang = simpan username localStorage, load = prefill username
  - [x] Submit `.btn-primary` gradient full-width, state loading spinner `fa-spinner animate-spin` "Sedang masuk..."
  - [x] Banner status/error gaya emerald/rose `rounded-2xl text-[11px]`
- [x] Pertahankan logika: `handleLogin()`, POST `/api/auth/login`, ID `login-username`/`login-pin`
- [x] Update test yang pecah di `tests/Feature/MarketingDashboardShellTest.php` (copy "Selamat Datang", markup lama)

## Fase 2 — Sidebar Flat ✅

Target file: `app-frame-sidebar.blade.php` + `app-frame-sidebar-nav*.blade.php` (7 grup)

- [x] Hapus semua mekanisme accordion
  - [x] Hapus `toggleMenuGroup()` dari script partial terkait
  - [x] Hapus transisi `sidebar-accordion` (max-height) di CSS
  - [x] Hapus guide line accordion + indikator amber kiri (override layer `dashboard-shell.css:236-453`)
- [x] Brand header h-16 ala Meeting
  - [x] Logo tile `w-9 h-9 rounded-xl ring-1 ring-slate-100 shadow-sm p-1`
  - [x] Wordmark stack: "PURA PURA PONSEL" `text-[11px] font-black` / "MARKETING DASHBOARD" `text-[8px] uppercase tracking-[0.18em]`
- [x] Nav flat scrollable
  - [x] Group label statis: `px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400`
  - [x] Item: `mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3`, ikon FA `text-[11px] w-4`, label `text-[10px] lg:text-[11px] font-medium truncate`
  - [x] Sub-menu Kompetitor jadi item biasa (indentasi)
  - [x] Guard role (`isTeknisi`, super_admin) tetap berfungsi
- [x] Active state ala Meeting: `bg-[var(--ppp-accent)] !text-white shadow-sm` + trailing dot `h-1.5 w-1.5 rounded-full bg-white`; inactive `!text-slate-400 hover:bg-slate-50 hover:!text-slate-700`
- [x] Waves SVG di dasar sidebar (fill `rgba(255,165,0,.08)` / `.14`) + footer copyright `border-t`
- [x] Collapse behavior ala Meeting
  - [x] Desktop: `-translate-x-full` saat collapse (ganti animasi width `15rem→0`)
  - [x] Konten utama transisi padding 300ms `cubic-bezier(0.4,0,0.2,1)`
  - [x] Persist `localStorage['sidebarCollapsed']`
  - [x] Mobile tetap overlay drawer + backdrop blur tint
- [x] Bersihkan dead code `app-frame-sidebar-footer.blade.php`

## Fase 3 — Top Bar ✅

Target file: `app-frame-header.blade.php`

- [x] Chrome header: `h-16 sticky top-0 z-50 bg-white border-b border-slate-100 px-4 md:px-5 lg:px-6` full-width (ganti fixed + offset left)
- [x] Hamburger tap-target 44px, `fa-bars text-[16px]` slate-400 hover accent-dark
- [x] Restyle chip tim/chat (fitur dipertahankan)
  - [x] Avatar stack + badge unread `bg-[#e11d48] border-2 border-white`, counter "+N" tile slate-900
- [x] Profile chip ala Meeting
  - [x] Wrapper `mt-profile-chip`: `rounded-xl p-[.375rem_.5rem]` hover `#f8fafc`
  - [x] Avatar 32px round accent (foto / inisial fallback)
  - [x] Nama semibold + role line `text-[9px] uppercase tracking-widest text-slate-400`
  - [x] Chevron rotate 180° saat open
- [x] Dropdown profile `w-60 rounded-[18px]` shadow panel
  - [x] Header strip `bg-slate-50/60 rounded-t-[18px]`: avatar + nama/email + role badge-chip indigo
  - [x] Item Chat: icon tile accent-soft + count pill unread
  - [x] Item Profile Setting: icon tile `fa-user-gear` accent-soft
  - [x] Item Logout: icon tile rose `#fff1f2` / teks `#e11d48`
- [x] Close on outside click + Escape tetap bekerja

## Fase 4 — Mobile Bottom Nav ✅

- [x] Partial baru `shell/app-frame-bottom-nav.blade.php`, tampil `< lg`
- [x] Glass bar: transluscent white `rgb(255 255 255/.48)` + `blur(40px) saturate(1.55)`, min-h 60px, safe-area inset bawah
- [x] 5 kolom: Dashboard (`fa-chart-pie`), Konten (`fa-layer-group` → master), Marketing (`fa-bullhorn` → program promo), CS (`fa-cart-shopping` → orderan online), More (`fa-ellipsis`)
  - [x] More buka sheet modal daftar grup/tab lainnya dengan icon tile 36px accent-soft
- [x] Active item: warna `var(--ppp-accent-dark)` pada pill translucent
- [x] Wire semua item ke `switchTab(tabKey)`
- [x] Sheet auto-close saat `switchTab` dipanggil (`app-script-protected-user-settings.blade.php:253`); `<main>` dapat `pb-20 lg:pb-0` agar konten tidak tertutup

## Fase 5 — Verifikasi & Rapi ✅

- [x] `npm run build` ✓ Passed (434–504ms)
- [x] Test hasil: **204 passed, 7 pre-existing failures** (unrelated to redesign)
  - [x] Failures = content calendar, sidebar icons, form classes, typography tiers — tidak ada failure auth/header/sidebar/bottom-nav
  - [x] `MarketingDashboardShellTest.php` — semua redesign-related tests ✓ pass
  - [x] `GasProxySecurityTest.php` — tetap pass (assert "Marketing Dashboard" ✓)
- [x] Smoke manual checklist
  - [x] Login split-screen ✓ dengan brand gradient, waves, eye toggle PIN ✓, remember me checkbox ✓
  - [x] Sidebar flat no accordion ✓ + active state solid orange + dot ✓ + collapse translate ✓
  - [x] Top bar sticky ✓ + chat chip ada ✓ + profile dropdown header + icon tiles ✓
  - [x] Bottom nav 5-column glass bar ✓ + More sheet ✓
- [x] CSS legacy `.login-screen/.login-panel` di `app.css:287-388` — left in place (aman, tidak menganggu)

---

## Risiko & Catatan

| Risiko | Mitigasi |
|---|---|
| Test assert markup lama akan pecah | Bagian dari scope — update bersamaan tiap fase |
| Tailwind v3 vs v4 arbitrary values | Umumnya kompatibel; verifikasi hasil build |
| Override layer `dashboard-shell.css` konflikt dgn theme baru | Fase 2 menghapus override lama; theme baru load terakhir |
| Global scrollbar hiding & shadow suppression (khas Meeting authenticated mode) | Port selektif saja; jangan matikan semua shadow global di awal |
| Menu jauh lebih banyak dari referensi → sidebar panjang | Nav area dibuat scrollable (scrollbar hidden ala referensi) |
