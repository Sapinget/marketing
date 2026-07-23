# Plan Mini Chat Realtime

## Tujuan

Membuat mini chat realtime di dashboard agar user yang sedang online bisa saling kirim pesan tanpa refresh halaman.

## Scope Awal

- Chat antar user dashboard yang sudah login
- Menampilkan user online sebagai target chat
- Pesan realtime masuk otomatis
- Riwayat pesan tersimpan di database
- Badge unread per user
- Notifikasi ringan saat pesan baru masuk

## Status Singkat

Sudah jalan versi awal berbasis polling ringan. Fondasi auth session, presence user online, panel chat, unread total, unread per user, dan toast pesan baru sudah masuk. Belum pakai `chat_threads`/`chat_participants`; tahap sekarang masih direct message user-ke-user lewat `chat_messages`.

## Checklist Implementasi

### 1. Audit Fondasi Saat Ini

- [x] Cek struktur auth dashboard saat ini
- [x] Cek data `users`, `is_online`, `last_seen_at`, dan `active_session_id`
- [x] Cek apakah app sudah punya queue, broadcasting, atau websocket
- [x] Cek struktur frontend Vue dashboard
- [x] Tentukan lokasi UI chat paling cocok di header/sidebar

### 2. Desain Database

- [ ] Buat tabel `chat_threads`
- [ ] Buat tabel `chat_participants`
- [x] Buat tabel `chat_messages`
- [x] Tambah field status baca pesan
- [x] Tambah index untuk performa query chat
- [x] Pastikan relasi user aman saat user dihapus

### 3. Backend API

- [x] Endpoint list user online yang bisa diajak chat
- [x] Endpoint list thread chat user login
- [x] Endpoint ambil pesan per thread
- [x] Endpoint kirim pesan
- [x] Endpoint tandai pesan sebagai sudah dibaca
- [x] Validasi semua input pesan
- [x] Batasi panjang pesan
- [x] Pastikan user hanya bisa akses thread miliknya

### 4. Realtime Strategy

- [x] Pilih mekanisme realtime
- [ ] Opsi A: Laravel Broadcasting + Reverb/WebSocket
- [x] Opsi B: polling ringan setiap 3-5 detik
- [ ] Opsi C: hybrid polling dulu, WebSocket nanti
- [x] Untuk tahap awal, gunakan polling ringan agar cepat stabil
- [x] Siapkan struktur backend agar nanti mudah upgrade ke WebSocket

### 5. UI/UX Mini Chat

- [x] Tambah tombol chat di header dekat active users
- [x] Tampilkan badge unread total
- [x] Buat panel mini chat floating
- [x] Tampilkan daftar user online
- [x] Tampilkan daftar thread terakhir
- [x] Buat tampilan conversation
- [x] Input pesan satu baris dengan tombol kirim icon
- [x] Loading state saat kirim pesan
- [x] Empty state jika belum ada chat
- [x] Error state jika gagal kirim/ambil pesan
- [x] Auto-scroll ke pesan terbaru
- [x] Responsive untuk mobile

### 6. Realtime Polling Tahap Awal

- [x] Poll thread aktif setiap 3 detik saat panel chat terbuka
- [x] Poll unread count setiap 10-15 detik saat dashboard aktif
- [x] Pause polling saat tab browser hidden
- [x] Resume polling saat tab aktif kembali
- [x] Hentikan polling saat logout/session expired
- [x] Integrasikan dengan logic single-device session

### 7. Notifikasi

- [x] Tampilkan toast saat pesan baru masuk dari user lain
- [x] Jangan tampilkan toast jika conversation sedang dibuka
- [x] Tambah badge unread di tombol chat
- [x] Tandai pesan read saat conversation dibuka
- [x] Pastikan toast tetap satu line

### 8. Security

- [x] Validasi pesan sebagai plain text
- [x] Escape output pesan di frontend
- [x] Cegah akses thread user lain
- [x] Cegah spoofing sender dari payload
- [x] Gunakan `auth()->id()` sebagai sender
- [x] Rate limit endpoint kirim pesan
- [x] Jangan expose `active_session_id`
- [x] Pastikan session device lama tetap logout saat ada login baru

### 9. Testing

- [ ] Feature test membuat thread saat pesan pertama dikirim
- [x] Feature test user bisa kirim pesan ke user lain
- [x] Feature test user tidak bisa baca thread bukan miliknya
- [x] Feature test unread count bertambah
- [x] Feature test mark as read
- [x] Feature test pesan kosong ditolak
- [x] Feature test pesan terlalu panjang ditolak
- [x] Feature test user tidak bisa kirim pesan ke diri sendiri
- [x] Feature test user tidak login tidak bisa akses chat
- [x] Frontend shell test memastikan script chat terdaftar
- [x] Build frontend

### 10. Rollout

- [x] Implementasi polling dulu
- [ ] Uji manual dengan 2 browser/device berbeda
- [ ] Pastikan device lama logout saat akun sama login di device baru
- [ ] Pastikan chat tetap jalan antar akun berbeda
- [ ] Commit perubahan
- [x] Catat kemungkinan upgrade ke WebSocket/Reverb

## Gap Berikutnya

Prioritas berikut:
1. Uji manual dengan 2 browser/device berbeda.
2. Pertimbangkan migrasi ke `chat_threads` kalau nanti butuh histori multi-conversation yang lebih rapi.
3. Pertimbangkan search/reply/pagination kalau volume pesan mulai tinggi.
4. Upgrade ke WebSocket/Reverb kalau polling mulai terasa lambat.

## Rekomendasi

Mulai dari polling ringan dulu, karena dashboard ini sudah punya heartbeat dan session logic. Setelah stabil, upgrade ke WebSocket/Reverb kalau traffic chat mulai tinggi.
