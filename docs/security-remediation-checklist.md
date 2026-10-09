# Security Remediation Checklist

Tanggal audit: 2026-07-10

## Critical

- [x] Hapus auto-login admin dari `GET /api/auth/session`.
- [x] Batasi `DashboardAuth::bootstrapConfiguredAdminSession()` agar hanya bisa berjalan di environment `local` atau `testing`.
- [x] Batasi `DashboardAuth::ensureConfiguredAdminUser()` agar hanya bisa berjalan di environment `local` atau `testing`.
- [x] Pastikan `TEST_ADMIN_USERNAME` dan `TEST_ADMIN_PIN` tidak dipakai di production.
- [x] Tambahkan test yang memastikan `/api/auth/session` tidak pernah membuat session admin otomatis di production.

## High

- [x] Hapus middleware dan bypass `gas_proxy` karena integrasi Google Apps Script sudah tidak dipakai.
- [x] Hapus akses `X-GAS-PROXY-SECRET` dari `dashboard.auth`; API sekarang hanya menerima authenticated session.
- [x] Lepas route utama dari wrapper `gas.proxy` legacy.
- [x] Samakan akses `__db` menjadi local-only; jangan baca `env('GAS_PROXY_SECRET')` langsung di route.
- [x] Tambahkan test yang memastikan header legacy GAS tidak lagi memberi akses API atau `__db`.

- [x] Pisahkan endpoint create user dan update user; jangan pakai `updateOrCreate()` untuk operasi admin user.
- [x] Larang reset password user lain hanya dengan mengirim `username` yang sama.
- [x] Tambahkan authorization check untuk endpoint `/api/auth/users`.
- [x] Definisikan role yang nyata, bukan semua user dianggap `Super Admin`.
- [x] Tambahkan test yang memastikan user biasa tidak bisa membuat, mengubah, atau reset akun user lain.

## Medium

- [x] Batasi endpoint `__db/tables` dan `__db/tables/{table}` hanya untuk local debugging.
- [x] Jika endpoint `__db` tetap dibutuhkan, tambah authorization terpisah yang lebih kuat dari shared secret.
- [x] Blok preview untuk tabel sensitif seperti `users`, `sessions`, `cache`, dan tabel credential lain.
- [x] Tambahkan audit log untuk akses ke endpoint `__db`.
- [x] Tambahkan test yang memastikan endpoint `__db` tidak bisa diakses dari remote production request.

## Hardening Tambahan

- [x] Review seluruh endpoint yang hanya dilindungi `dashboard.auth` untuk memastikan ada authorization per aksi, bukan sekadar authenticated session.
- [x] Review penggunaan header `X-App-User` agar tidak dipakai sebagai sumber identitas utama tanpa verifikasi kuat.
- [x] Tambahkan regression tests untuk auth, proxy secret, dan user-management sebelum deploy perubahan security.
- [x] Jalankan ulang audit setelah perbaikan untuk memastikan tidak ada jalur bypass baru.

## Catatan Implementasi

- [x] Tambahkan kolom `users.role` dengan default `operasional` dan migrasi data user lama ke role yang eksplisit.
- [x] Batasi akses manajemen user agar hanya `super_admin` yang bisa membuka menu, memuat daftar user, membuat user, mengubah user, dan menghapus user.
- [x] Tambahkan kontrol role di UI Manajemen User untuk create dan edit user.
- [x] Batasi endpoint sensitif non-operasional seperti `activity_logs`, `settings`, `raw-sheets`, dan import Meta analytics ke role `admin` / `super_admin`.
- [x] Hentikan fallback identitas dari header `X-App-User`; activity log sekarang hanya mengambil actor dari authenticated session.
- [x] Rebuild asset frontend setelah perubahan auth dan user-management agar bundle runtime sesuai dengan backend terbaru.

## Verifikasi Yang Sudah Dijalankan

- [x] `php artisan migrate --force`
- [x] `php artisan test tests/Feature/GasProxySecurityTest.php tests/Feature/DashboardUserManagementTest.php tests/Feature/DashboardAuthenticationTest.php`
- [x] `npm run build`

## Audit 127.0.0.1:8090 (2026-10-09)

Diperbaiki:

- [x] **phpMyAdmin terbuka di `/pma/`**: `public/pma` adalah symlink ke `/opt/homebrew/share/phpmyadmin`, dan tunnel Cloudflare meneruskan semua path ke origin. Symlink dihapus (target tidak disentuh). Dijaga `PublicDirectoryHygieneTest`. Jalankan phpMyAdmin di port lokal terpisah bila perlu, mis. `php -S 127.0.0.1:8091 -t /opt/homebrew/share/phpmyadmin`.
- [x] **XSS lewat tautan di `/print-job`**: sebelumnya publik, tanpa CSRF/throttle, sanitizer regex meloloskan `onerror=`/`onmouseover=` tanpa tanda kutip, dan halaman disajikan satu origin dengan CSP `unsafe-inline`. Sekarang: wajib login (`dashboard.auth`) dengan CSRF, throttle 30/60 per menit, token terikat pada akun pembuatnya, sanitasi lewat DOM (`App\Support\PrintHtmlSanitizer`), dan halaman cetak memakai CSP ber-nonce (hanya skrip cetak yang boleh jalan). `SetSecurityHeaders` tidak lagi menimpa CSP yang sudah diset route.

Belum (dari audit yang sama), urut prioritas:

- [ ] `APP_ENV=local` pada instance yang di-tunnel (membuka `/__db/*`; guard hanya mengandalkan IP loopback + `X-Forwarded-For`). Pakai `production`.
- [ ] Layar login mengirim seluruh aplikasi (±650 KB HTML + semua path API); pisahkan halaman login yang ringan.
- [ ] Dependensi: Laravel 10.50.2 sudah EOL (4 advisory, 1 tinggi), `league/commonmark` (2), `league/flysystem` (1); `npm audit` melaporkan `vue`/`@vue/server-renderer` dan `source-map-js` (dev tooling).
- [ ] `/design-system*` dan `/marketing-dashboard.html` masih publik; `logo.png` 960×960 (96 KB).

Paket kecil, diperbaiki:

- [x] `public/.DS_Store` dan stub `public/adminer.php` dihapus (keduanya gitignored); dijaga `SecurityHeadersTest`.
- [x] `X-Powered-By` dihapus di `SetSecurityHeaders` (cek langsung di `:8090`).
- [x] `robots.txt` kini `Disallow: /`, plus header `X-Robots-Tag: noindex, nofollow, noarchive` di semua respons.
- [x] CSP `font-src` tidak lagi memuat `frontend-cdn.perplexity.ai` (tidak dipakai di mana pun).
- [x] Header cache untuk aset statis ditambahkan di `worker-proxy/src/index.js` (`/build/assets/*` immutable setahun; `/vendor/dashboard/*` dan `/asset/*` sehari; tidak menimpa `Cache-Control` dari origin). **Berlaku setelah worker di-deploy** (`wrangler deploy`).
