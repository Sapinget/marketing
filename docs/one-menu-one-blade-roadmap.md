# Roadmap Pemisahan Arsitektur: 1 Menu 1 Blade

> **Status 2026-10-09 (lihat `docs/hash-to-url-routing-plan.md` untuk rincian):** 27 dari 28 menu sidebar sudah punya URL sendiri lewat helper `$dashboardPage` di `routes/web.php` dan peta `config/dashboard.php` `tab_urls` (menu `dashboard` tetap di `/`, menu Service dihapus). Markup tiap menu hanya dirender di halamannya (`app-frame.blade.php`: `$dedicatedMenuView` vs `$legacyMenus`). **Script/state menu baru sebagian dipisah** (`tiktok_template`, katalog pricelist/apple, meta-ig presentation, followers, img-repo, asset-vendor, market-intel); sisa script bersama (settings, kalender, content-list, summary, dll.) sengaja tidak dipecah. Kotak per menu di bawah sengaja dibiarkan kosong karena definisi "selesai" di dokumen ini mencakup pemisahan script penuh. Peta URL final ada di bagian 4 plan routing, bukan di dokumen ini.
>
> | Cluster | URL |
> |---|---|
> | Konten | `/konten/{master-plan,unboxing,ideation,distribution,analytics,calendar,story}` |
> | Marketing | `/marketing/{program-promo,sell-out,ads-log,budgeting}`, `/tools/{harga-kompetitor,laporan-event}`, `/promo-pamflet` |
> | Analisa | `/analisa/{story-ig,feed-konten,followers-ig}` |
> | Customer Service | `/cs/{order-online,unit-ditanya,claim-garansi,keep-barang}` |
> | Complain | `/complain/{input-claim,garansi-cermati,garansi-resmi}` |
> | Settings | `/settings`, `/settings/{nama-stock,users,activity-logs}` |
> | Menu tersembunyi (masih `#hash` di `/`) | Performa, Intelijen Pasar, Top/Low Konten, Insight & Tren, Proses Claim, Profile |


Dokumen ini adalah rencana kerja komprehensif untuk memigrasi dashboard monolith SPA (satu file Blade dengan hash routing) menjadi arsitektur multi-page **1 Menu 1 Blade** dengan rute server-side Laravel standar.

---

## 🏗️ 1. Pondasi & Master Layout

- [ ] **Fase 1.1: Pembuatan Layout Utama**
  - [ ] Buat layout base di `resources/views/layouts/dashboard.blade.php`.
  - [ ] Pindahkan asset runtime, CSRF token, stylesheets (`head.blade.php`, Tailwind, FontAwesome) ke layout.
  - [ ] Pindahkan Shell Chrome: Top Header, Profile Dropdown, Notification Bar, Sidebar container.
  - [ ] Siapkan placeholder `@yield('content')` atau `@section('content')` dan stack scripts `@stack('scripts')`.

- [ ] **Fase 1.2: Global Runtime & Shared Utilities**
  - [ ] Ekstrak helper global (`resolveAppUrl`, formatting tanggal, currency formatter) ke `public/js/dashboard-shared.js` atau Vite entry point `resources/js/dashboard-core.js`.
  - [ ] Buat runtime provider untuk Auth & User Session (`/api/auth/session`) agar konsisten di setiap halaman.
  - [ ] Pindahkan Mini Chat Realtime & Notification Bell ke komponen layout shared.

- [ ] **Fase 1.3: Refactor Sidebar Navigation**
  - [ ] Ganti event `@click="activeTab = '...'"` dan `href="#..."` menjadi URL Laravel standar `href="{{ route('...') }}"`.
  - [ ] Gunakan `request()->routeIs('...')` untuk class active state menu.

---

## 📑 2. Rincian Pemisahan Per Menu (Total: 43 Menu)

### 📊 Cluster 1: Dashboard & Konten
- [ ] **1. Dashboard Utama**
  - Route: `GET /dashboard` (`dashboard.index`)
  - Target Blade: `resources/views/dashboard/pages/dashboard.blade.php`
  - Source: `partials/menus/dashboard.blade.php`
  - Scripts: `app-script-summary-computed-cluster.blade.php`
- [ ] **2. Master Plan**
  - Route: `GET /konten/master-plan` (`dashboard.konten.master-plan`)
  - Target Blade: `resources/views/dashboard/pages/konten/master-plan.blade.php`
  - Source: `partials/menus/master-plan.blade.php`
  - Scripts: `app-script-master-content-operations.blade.php`
- [ ] **3. Unboxing**
  - Route: `GET /konten/unboxing` (`dashboard.konten.unboxing`)
  - Target Blade: `resources/views/dashboard/pages/konten/unboxing.blade.php`
  - Source: `partials/menus/unboxing.blade.php`
- [ ] **4. Ideation**
  - Route: `GET /konten/ideation` (`dashboard.konten.ideation`)
  - Target Blade: `resources/views/dashboard/pages/konten/ideation.blade.php`
  - Source: `partials/menus/ideation.blade.php`
- [ ] **5. Distribution**
  - Route: `GET /konten/distribution` (`dashboard.konten.distribution`)
  - Target Blade: `resources/views/dashboard/pages/konten/distribution.blade.php`
  - Source: `partials/menus/distribution.blade.php`
  - Scripts: `app-script-distribution-analytics-operations.blade.php`
- [ ] **6. Analytics**
  - Route: `GET /konten/analytics` (`dashboard.konten.analytics`)
  - Target Blade: `resources/views/dashboard/pages/konten/analytics.blade.php`
  - Source: `partials/menus/analytics.blade.php`
  - Scripts: `app-script-distribution-analytics-operations.blade.php`, `export-analytics-cluster.blade.php`
- [ ] **7. Kalender Konten**
  - Route: `GET /konten/kalender` (`dashboard.konten.calendar`)
  - Target Blade: `resources/views/dashboard/pages/konten/calendar.blade.php`
  - Source: `partials/menus/calendar.blade.php`
  - Scripts: `app-script-calendar-helpers.blade.php`
- [ ] **8. Jadwal Story**
  - Route: `GET /konten/story` (`dashboard.konten.story`)
  - Target Blade: `resources/views/dashboard/pages/konten/story.blade.php`
  - Source: `partials/menus/story.blade.php`

---

### 📈 Cluster 2: Marketing & Sales
- [ ] **9. Program Promo**
  - Route: `GET /marketing/program-promo` (`dashboard.marketing.program-promo`)
  - Target Blade: `resources/views/dashboard/pages/marketing/program-promo.blade.php`
  - Source: `partials/menus/program-promo.blade.php`
  - Scripts: `export-sales-and-promo-pdfs.blade.php`
- [ ] **10. Sell Out Target**
  - Route: `GET /marketing/sell-out` (`dashboard.marketing.sell-out`)
  - Target Blade: `resources/views/dashboard/pages/marketing/sell-out.blade.php`
  - Source: `partials/menus/sell-out.blade.php`
- [ ] **11. Ads Log**
  - Route: `GET /marketing/ads-log` (`dashboard.marketing.ads-log`)
  - Target Blade: `resources/views/dashboard/pages/marketing/ads-log.blade.php`
  - Source: `partials/menus/ads-log.blade.php`
  - Scripts: `app-script-ads-log-operations.blade.php`, `export-ads-log-pdf.blade.php`
- [ ] **12. Budgeting**
  - Route: `GET /marketing/budgeting` (`dashboard.marketing.budgeting`)
  - Target Blade: `resources/views/dashboard/pages/marketing/budgeting.blade.php`
  - Source: `partials/menus/budgeting.blade.php`
  - Scripts: `app-script-budgeting-operations.blade.php`, `export-budget-pdf.blade.php`
- [ ] **13. Laporan Event**
  - Route: `GET /marketing/laporan-event` (`dashboard.marketing.laporan-event`)
  - Target Blade: `resources/views/dashboard/pages/marketing/laporan-event.blade.php`
  - Source: `partials/menus/laporan-event.blade.php`

---

### 🔍 Cluster 3: Intelijen Pasar & Kompetitor
- [ ] **14. Pasar**
  - Route: `GET /intelijen-pasar/pasar` (`dashboard.market.pasar`)
  - Target Blade: `resources/views/dashboard/pages/market/pasar.blade.php`
  - Source: `partials/menus/market-pasar.blade.php`
  - Scripts: `app-script-market-intelligence-operations.blade.php`
- [ ] **15. Intelijen Harga**
  - Route: `GET /intelijen-pasar/intelijen-harga` (`dashboard.market.intelijen-harga`)
  - Target Blade: `resources/views/dashboard/pages/market/intelijen-harga.blade.php`
  - Source: `partials/menus/market-intelijen-harga.blade.php`
  - Scripts: `app-script-price-competitor-operations.blade.php`
- [ ] **16. Audit Harga**
  - Route: `GET /intelijen-pasar/audit-harga` (`dashboard.market.audit-harga`)
  - Target Blade: `resources/views/dashboard/pages/market/audit-harga.blade.php`
  - Source: `partials/menus/market-audit-harga.blade.php`
- [ ] **17. Semua Kompetitor**
  - Route: `GET /intelijen-pasar/kompetitor/semua` (`dashboard.market.eksternal`)
  - Target Blade: `resources/views/dashboard/pages/market/eksternal.blade.php`
  - Source: `partials/menus/market-eksternal.blade.php`
- [ ] **18. Kompetitor Good Ponsel**
  - Route: `GET /intelijen-pasar/kompetitor/goodponsel` (`dashboard.market.ext-goodponsel`)
  - Target Blade: `resources/views/dashboard/pages/market/ext-goodponsel.blade.php`
  - Source: `partials/menus/market-ext-goodponsel.blade.php`
- [ ] **19. Kompetitor Devstore**
  - Route: `GET /intelijen-pasar/kompetitor/devstore` (`dashboard.market.ext-devstore`)
  - Target Blade: `resources/views/dashboard/pages/market/ext-devstore.blade.php`
  - Source: `partials/menus/market-ext-devstore.blade.php`
- [ ] **20. Kompetitor Rumah Gadget Bali**
  - Route: `GET /intelijen-pasar/kompetitor/rumahgadget` (`dashboard.market.ext-rumahgadget`)
  - Target Blade: `resources/views/dashboard/pages/market/ext-rumahgadget.blade.php`
  - Source: `partials/menus/market-ext-rumahgadget.blade.php`
- [ ] **21. Harga & Kompetitor Legacy**
  - Route: `GET /intelijen-pasar/harga-kompetitor` (`dashboard.market.harga-kompetitor`)
  - Target Blade: `resources/views/dashboard/pages/market/harga-kompetitor.blade.php`
  - Source: `partials/menus/harga-kompetitor.blade.php`
  - Scripts: `export-price-comparison-pdf.blade.php`

---

### 📱 Cluster 4: Analisa Konten & Social Media
- [ ] **22. Top Konten**
  - Route: `GET /analisa-konten/top-konten` (`dashboard.analisa.top-content`)
  - Target Blade: `resources/views/dashboard/pages/analisa/top-content.blade.php`
  - Source: `partials/menus/top-content.blade.php`
  - Scripts: `app-script-meta-ig-analytics.blade.php`
- [ ] **23. Low Konten**
  - Route: `GET /analisa-konten/low-konten` (`dashboard.analisa.low-content`)
  - Target Blade: `resources/views/dashboard/pages/analisa/low-content.blade.php`
  - Source: `partials/menus/low-content.blade.php`
  - Scripts: `app-script-meta-ig-analytics.blade.php`
- [ ] **24. Insight & Tren**
  - Route: `GET /analisa-konten/insight` (`dashboard.analisa.insight`)
  - Target Blade: `resources/views/dashboard/pages/analisa/insight.blade.php`
  - Source: `partials/menus/analisa-insight.blade.php`
- [ ] **25. Story IG**
  - Route: `GET /analisa-konten/meta-story` (`dashboard.analisa.meta-story`)
  - Target Blade: `resources/views/dashboard/pages/analisa/meta-story.blade.php`
  - Source: `partials/menus/meta-story.blade.php`
- [ ] **26. Feed Konten IG**
  - Route: `GET /analisa-konten/meta-feed` (`dashboard.analisa.meta-feed`)
  - Target Blade: `resources/views/dashboard/pages/analisa/meta-feed.blade.php`
  - Source: `partials/menus/meta-feed.blade.php`
- [ ] **27. Followers IG**
  - Route: `GET /analisa-konten/meta-followers` (`dashboard.analisa.meta-followers`)
  - Target Blade: `resources/views/dashboard/pages/analisa/meta-followers.blade.php`
  - Source: `partials/menus/meta-followers.blade.php`
  - Scripts: `app-script-meta-followers.blade.php`

---

### 🤝 Cluster 5: Customer Service
- [ ] **28. Order Online**
  - Route: `GET /customer-service/order-online` (`dashboard.cs.order-online`)
  - Target Blade: `resources/views/dashboard/pages/cs/order-online.blade.php`
  - Source: `partials/menus/order-online.blade.php`
  - Scripts: `app-script-customer-service-crud.blade.php`
- [ ] **29. Unit Ditanya**
  - Route: `GET /customer-service/unit-ditanya` (`dashboard.cs.unit-ditanya`)
  - Target Blade: `resources/views/dashboard/pages/cs/unit-ditanya.blade.php`
  - Source: `partials/menus/unit-ditanya.blade.php`
  - Scripts: `app-script-customer-service-crud.blade.php`
- [ ] **30. Service**
  - Route: `GET /customer-service/service` (`dashboard.cs.service`)
  - Target Blade: `resources/views/dashboard/pages/cs/service.blade.php`
  - Source: `partials/menus/service.blade.php`
  - Scripts: `app-script-customer-service-crud.blade.php`
- [ ] **31. Claim Garansi & Asuransi**
  - Route: `GET /customer-service/claim-garansi` (`dashboard.cs.claim-garansi`)
  - Target Blade: `resources/views/dashboard/pages/cs/claim-garansi.blade.php`
  - Source: `partials/menus/claim-garansi.blade.php`
  - Scripts: `export-customer-service-pdfs.blade.php`
- [ ] **32. Keep Barang**
  - Route: `GET /customer-service/keep-barang` (`dashboard.cs.keep-barang`)
  - Target Blade: `resources/views/dashboard/pages/cs/keep-barang.blade.php`
  - Source: `partials/menus/keep-barang.blade.php`
  - Scripts: `app-script-customer-service-crud.blade.php`

---

### 🛡️ Cluster 6: Complain & Claim Tracker (LPJK)
- [ ] **33. Input Claim**
  - Route: `GET /complain-tracker/input-claim` (`dashboard.claim.input-claim`)
  - Target Blade: `resources/views/dashboard/pages/claim/input-claim.blade.php`
  - Source: `partials/menus/input-claim.blade.php`
  - Scripts: `app-script-claim-menus.blade.php`, `app-script-lpjk-operations.blade.php`
- [ ] **34. Garansi Cermati**
  - Route: `GET /complain-tracker/garansi-cermati` (`dashboard.claim.garansi-cermati`)
  - Target Blade: `resources/views/dashboard/pages/claim/garansi-cermati.blade.php`
  - Source: `partials/menus/garansi-cermati.blade.php`
  - Scripts: `export-lpjk-detail-pdf.blade.php`
- [ ] **35. Garansi Resmi**
  - Route: `GET /complain-tracker/garansi-resmi` (`dashboard.claim.garansi-resmi`)
  - Target Blade: `resources/views/dashboard/pages/claim/garansi-resmi.blade.php`
  - Source: `partials/menus/garansi-resmi.blade.php`

---

### 🏆 Cluster 7: Performa & Bonus
- [ ] **36. Bonus Report**
  - Route: `GET /performa/bonus-report` (`dashboard.performa.bonus-report`)
  - Target Blade: `resources/views/dashboard/pages/performa/bonus-report.blade.php`
  - Source: `partials/menus/bonus-report.blade.php`
  - Scripts: `app-script-bonus-talent-cluster.blade.php`, `export-reporting-bridge.blade.php`
- [ ] **37. Talent Bonus**
  - Route: `GET /performa/talent-bonus` (`dashboard.performa.talent-bonus`)
  - Target Blade: `resources/views/dashboard/pages/performa/talent-bonus.blade.php`
  - Source: `partials/menus/talent-bonus.blade.php`
  - Scripts: `app-script-bonus-talent-cluster.blade.php`
- [ ] **38. Editor Performance**
  - Route: `GET /performa/editor-performance` (`dashboard.performa.editor-performance`)
  - Target Blade: `resources/views/dashboard/pages/performa/editor-performance.blade.php`
  - Source: `partials/menus/editor-performance.blade.php`
  - Scripts: `app-script-bonus-talent-cluster.blade.php`

---

### 📦 Cluster 8: Katalog, Asset & Repository
- [ ] **39. Katalog Android (Pricelist)**
  - Route: `GET /katalog/android` (`dashboard.katalog.pricelist`)
  - Target Blade: `resources/views/dashboard/pages/katalog/pricelist.blade.php`
  - Source: `partials/menus/pricelist-katalog.blade.php`
  - Scripts: `app-script-pricelist-katalog-operations.blade.php`
- [ ] **40. Katalog Apple**
  - Route: `GET /katalog/apple` (`dashboard.katalog.apple`)
  - Target Blade: `resources/views/dashboard/pages/katalog/apple.blade.php`
  - Source: `partials/menus/apple-katalog.blade.php`
  - Scripts: `app-script-apple-katalog-operations.blade.php`
- [ ] **41. Template Background**
  - Route: `GET /katalog/template-background` (`dashboard.katalog.template-background`)
  - Target Blade: `resources/views/dashboard/pages/katalog/template-background.blade.php`
  - Source: `partials/menus/template-background.blade.php`
- [ ] **42. Repository Gambar**
  - Route: `GET /repository-gambar` (`dashboard.img-repo`)
  - Target Blade: `resources/views/dashboard/pages/katalog/img-repo.blade.php`
  - Source: `partials/menus/img-repo.blade.php`
  - Scripts: `app-script-img-repo-operations.blade.php`
- [ ] **43. Asset Vendor & Inventory**
  - Route: `GET /inventory/asset-vendor` (`dashboard.inventory.asset-vendor`)
  - Target Blade: `resources/views/dashboard/pages/inventory/asset-vendor.blade.php`
  - Source: `partials/menus/asset-vendor-inventory.blade.php`
  - Scripts: `app-script-asset-vendor-inventory-operations.blade.php`

---

### ⚙️ Cluster 9: Settings & Administrasi
- [ ] **44. Settings Umum**
  - Route: `GET /settings` (`dashboard.settings`)
  - Target Blade: `resources/views/dashboard/pages/settings/settings.blade.php`
  - Source: `partials/menus/settings.blade.php`
  - Scripts: `app-script-settings-cluster.blade.php`
- [ ] **45. Nama Stock**
  - Route: `GET /settings/nama-stock` (`dashboard.settings.nama-stock`)
  - Target Blade: `resources/views/dashboard/pages/settings/nama-stock.blade.php`
  - Source: `partials/menus/nama-stock.blade.php`
  - Scripts: `app-script-nama-stock-actions.blade.php`
- [ ] **46. Manajemen User**
  - Route: `GET /settings/users` (`dashboard.settings.auth-users`)
  - Target Blade: `resources/views/dashboard/pages/settings/auth-users.blade.php`
  - Source: `partials/menus/auth-users.blade.php`
  - Scripts: `app-script-protected-user-settings.blade.php`
- [ ] **47. Activity Logs**
  - Route: `GET /settings/activity-logs` (`dashboard.settings.activity-logs`)
  - Target Blade: `resources/views/dashboard/pages/settings/activity-logs.blade.php`
  - Source: `partials/menus/activity-logs.blade.php`
- [ ] **48. Profile User**
  - Route: `GET /profile` (`dashboard.profile`)
  - Target Blade: `resources/views/dashboard/pages/profile.blade.php`
  - Source: `partials/menus/profile.blade.php`
  - Scripts: `app-script-profile-user-mutations.blade.php`

---

## 🚀 3. Eksekusi Bertahap (Execution Phases)

- [ ] **Fase Alpha: Pilot Refactor (1 Cluster Terlebih Dahulu)**
  - Implementasikan Layout Utama + Routing untuk Cluster Settings & Profile.
  - Uji kesiapan navigasi tanpa merusak cluster lain.
- [ ] **Fase Beta: Migrasi Massal per Cluster**
  - Migrasikan Cluster Konten & Marketing.
  - Migrasikan Cluster Intelijen Pasar & Analisa.
  - Migrasikan Cluster Customer Service, Claim & Performa.
  - Migrasikan Cluster Katalog & Asset.
- [ ] **Fase Final: Cleanup & Testing**
  - Hapus shell monolith lama (`body-app-assembly.blade.php` & `dashboard/index.blade.php` monolithic SPA structure).
  - Jalankan test suite PHPUnit / Pest: `php artisan test`.
  - Verifikasi responsivitas mobile & desktop di setiap rute baru.
