# Marketing Template & Editor Roadmap

Dokumen ini memuat status implementasi yang telah selesai dikerjakan serta peta jalan (*roadmap*) pengembangan modul **Marketing Template / Template Studio** untuk platform Android dan iPhone.

---

## I. Apa yang Sudah Diterapkan (Implemented)

### 1. Konsolidasi Kontrol Warna Menjadi 1 Dropdown Field
- Mengubah 8 baris input warna terpisah (Warna header latar, teks header, teks produk, harga normal, harga spesial, garis coret, row ganjil, dan row genap) menjadi **1 field dropdown terpadu** menggunakan pola `search-select-popover`.
- Di dalam dropdown, setiap elemen memiliki swatch warna dan tombol pemilih warna (*color picker*) interaktif.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

### 2. Standardisasi Layout Grid Input Template & Format
- Merapikan input **Nama template** dan dropdown **Format template** menjadi grid 2 kolom seimbang (50% : 50%).
- Membersihkan class utilitas yang saling tumpang tindih (*redundant styles*) seperti bentrokan `select-trigger-button-compact` dengan aturan media query breakpoint desktop yang sebelumnya mengecilkan ukuran field dropdown.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

### 3. Pilihan Format Template Horizontal
- Mengubah susunan opsi preset format template (**Story 1080x1920**, **Feed 1080x1350**, dan **A4 1240x1754**) dari daftar vertikal memanjang menjadi tata letak horizontal 1 baris yang sejajar dan mudah diakses.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

### 4. Modal Editor Full-Screen
- Mengubah modal Edit/Tambah Template menjadi tampilan **Full Screen** penuh layar (100vw × 100vh) tanpa batasan `max-w` maupun batas sudut sheet, sehingga ruang kerja kanvas preview dan panel kontrol lebih leluasa.
- Header aksi dan footer tombol simpan terkunci (*sticky*), sedangkan area kerja kanvas dan form dapat digulir dengan nyaman.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

### 5. Pop-up Konfirmasi Penutupan (Warning on Close)
- Saat menekan tombol tutup (`X`), modal tidak langsung tertutup melainkan memunculkan dialog konfirmasi:
  - **Simpan**: Menyimpan seluruh konfigurasi layout dan background, lalu menutup modal.
  - **Tutup tanpa simpan**: Membatalkan perubahan yang belum disimpan dan menutup modal.
  - **Batal**: Menutup popup peringatan dan tetap berada di dalam editor.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php` & `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`.

### 6. Persistensi Draft & Status Modal saat Browser Reload
- Status modal terbuka dan seluruh isi form draf disimpan secara reaktif ke dalam `localStorage` (`ppp_catalog_template_modal_open`, `ppp_catalog_template_form`).
- Jika halaman di-*refresh* atau ter-reload saat sedang mendesain template, editor akan langsung terbuka kembali dengan seluruh nilai draf warna, posisi, dan teks yang sama tanpa hilang.
- Data lokal otomatis dibersihkan saat template berhasil disimpan atau saat pengguna sengaja keluar tanpa menyimpan.
- Proses penyimpanan didebounce (400ms) dan dinonaktifkan saat dragging aktif agar pergerakan kursor tetap ringan di 60/120 FPS.
- Lokasi: `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`.

### 7. Interaktivitas Klik Langsung di Canvas Preview (Direct Color Picking)
- **Teks Header**: Mengklik label kolom (`TYPE`, `RAM`, `STORAGE`, `NORMAL PRICE`, `SPECIAL PRICE`) langsung membuka color picker untuk warna teks header (`headerTextColor`).
- **Teks Baris Produk**: Mengklik nama model, RAM, atau storage langsung membuka pemilih warna teks produk (`textColor`).
- **Harga Normal & Spesial**: Mengklik angka harga langsung membuka color picker `normalPriceColor` atau `specialPriceColor`.
- **Card Mode**: Mengklik nama produk atau harga pada katalog card langsung membuka kontrol warna masing-masing.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

### 8. Zebra Tabel & Judul Brand Interaktif
- **Zebra Baris Tabel**: Mengklik area kosong pada baris tabel otomatis membuka pemilih warna latar baris ganjil (`rowOddColor`) atau baris genap (`rowEvenColor`) sesuai indeks baris.
- **Judul Brand**: Teks judul brand dapat di-double-click langsung untuk memilih warna judul (`titleColor`).
- **Pemisahan Drag vs Click**: Logika drag posisi diperbarui dengan ambang batas pergeseran pointer (minimal 3px). Pointer down tidak lagi memblokir event `click` bawaan browser dengan `preventDefault()`, sehingga pergeseran kanvas dan klik warna berjalan harmonis tanpa konflik layer.
- Lokasi: `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`.

### 9. Sistem Multi-Asset & Stiker Interaktif (Upload, Drag, Resize, Rotate & Mirror)
- **Multi-File Upload**: Mendukung upload banyak gambar sekaligus (`multiple`) untuk stiker, logo, watermark, atau credit badge.
- **Bebas Posisi (Free Placement)**: Pembatasan minimum koordinat dihilangkan sehingga aset gambar dapat ditempatkan di mana saja di seluruh kanvas.
- **Pergerakan Halus Tanpa Redundansi**: Menghilangkan konflik event drag antara elemen kanvas parent dengan elemen anak, serta menonaktifkan native image drag browser (`draggable="false"`).
- **Simetris Live Resize di Preview**: Handle bundar di sudut kanan bawah setiap stiker memungkinkan resize ukuran langsung di kanvas dengan rasio aspek terkunci (*aspect ratio preserved*), border pembungkus presisi mengikuti dimensi asli gambar (bukan kotak persegi kosong), mencegah gambar gepeng/terdistorsi.
- **Live Rotate di Preview**: Handle putar di atas stiker memungkinkan rotasi bebas 0°–360° dengan *magnetic snapping* otomatis di sudut istimewa (0°, 45°, 90°, 135°, 180°, 225°, 270°, 315°).
- **Flip, Mirror & Clone**: Tombol quick action untuk membalik stiker secara horizontal (`flipX`), vertikal (`flipY`), serta **Clone / Duplikat** aset instan dengan offset otomatis ke dalam daftar layer.
- **Penataan Layer Anti-Menimpa Komponen Default**:
  - Di kanvas preview, semua stiker dikunci pada `z-index: 1`, sedangkan tabel dan card berada pada `z-index: 20`, dan judul pada `z-index: 30`. Stiker tidak akan pernah menutupi teks, tabel, atau harga produk.
  - Pada canvas generator (`drawCatalogAssets`), aset selalu digambar **sebelum** tabel dan teks dirender.
- **Panel Layer List Compact & Drag Reorder**:
  - Menghilangkan input manual koordinat X/Y yang memakan ruang agar tampilan bersih dan fokus.
  - Urutan layer dapat diubah langsung dengan **drag-and-drop** baris item ke atas atau ke bawah dalam daftar.
  - Setiap item layer menampilkan grip handle, mini thumbnail gambar, nomor layer, label dimensi ringkas (`W×H px · Deg°`), tombol flip, dan tombol hapus.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php` & `resources/views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php`.

### 10. Checklist Pilihan Produk Android Sebelum Generate
- Fitur bagi editor untuk memilih/mencentang produk mana saja yang akan dimasukkan ke dalam katalog gambar/PDF.
- Tersedia kontrol *Select All*, *Deselect*, dan filter khusus produk terpilih.
- Lokasi: `resources/views/dashboard/partials/menus/pricelist-katalog.blade.php`.

---

## II. Peta Jalan Pengembangan (Development Roadmap)

Berikut adalah status tahapan pengembangan modul Marketing Template:

```
[Phase 1] Pemisahan Menu Template Studio              [SELESAI]
    │
    ▼
[Phase 2] Unifikasi Pipeline Android & iPhone         [DALAM PROGRES]
    │
    ▼
[Phase 3] Text Engine, Custom Title, Font & Stroke     [DALAM PROGRES]
    │
    ▼
[Phase 4] Image/Asset System, Layering & Stiker       [SELESAI]
    │
    ▼
[Phase 5] Pricing Engine, Sanitasi & Komparasi iBox   [DALAM PROGRES]
    │
    ▼
[Phase 6] Seleksi Kustom Editor & Personalisasi Card   [SELESAI]
    │
    ▼
[Phase 7] Stabilisasi, Testing & Optimasi Ekspor       [BERKELANJUTAN]
```

---

### Phase 1: Pemisahan Menu Template Studio — SELESAI
- [x] **Menu Mandiri**: Antarmuka pembuatan/pengelolaan template sudah dipisahkan dari menu operasional harian *Katalog Android*.
- [x] Menu baru **Template Background** sudah tersedia di sidebar melalui `activeTab === 'template_background'`.
- [x] Daftar template, upload background, buat manual, edit, dan hapus template sudah dipindahkan ke menu terpisah.
- [x] Katalog Android tetap menangani pemilihan produk, filter brand, output, preview, dan unduhan.
- Catatan: Nama menu saat ini adalah **Template Background**. Jika diperlukan, label dapat diganti menjadi **Marketing Template** tanpa mengubah fungsi.

---

### Phase 2: Unifikasi Template Android & iPhone (List & Card Mode)
- [ ] Standardisasi model template agar dapat digunakan lintas platform (Android dan Apple).
- [ ] Menyamakan kapabilitas format:
  - **Mode List (Tabel)**: Tersedia untuk Android dan iPhone.
  - **Mode Katalog (Card Gambar)**: Tersedia untuk Android dan iPhone.
- [ ] Skema database dan API disesuaikan agar `layout_config` mendukung kedua tipe output secara konsisten.

---

### Phase 3: Text Engine, Font Selector, Custom Title & Stroke — DALAM PROGRES
- [x] **Title Bisa Diketik**: Judul brand/banner di kanvas mendukung teks kustom fleksibel.
- [x] **Stroke pada Title**: Mendukung pengaturan stroke/outline judul agar kontras di latar belakang apa pun.
- [x] **Font Switcher**: Pilihan jenis font (Inter, Poppins, Arial) untuk judul template.
- [x] **Text Box Dinamis Bebas**:
  - Menambah dan menghapus elemen text box promosi dari panel editor.
  - Pilihan pembungkus *plain*, *pill*, *rounded*, dan *tag*.
  - Posisi bebas via drag & drop langsung di kanvas, tampil di preview dan hasil ekspor.
- [ ] Kontrol lanjutan text box per elemen: ukuran font, padding, font family, serta warna teks/latar melalui panel editor.

---

### Phase 4: Sistem Aset, Layering & Stiker Promosi — SELESAI
- [x] **Input Aset & Gambar Bebas**: Upload multi-file elemen grafis (logo, icon garansi, badge resmi, foto aksesori) ke atas kanvas.
- [x] **Pergerakan Posisi Halus**: Drag langsung di kanvas ke posisi mana saja tanpa batas koordinat.
- [x] **Resize Simetris di Preview**: Handle tarik sudut kanan bawah dengan rasio aspek terkunci.
- [x] **Rotate di Preview**: Handle putar atas 360° dengan magnetic snapping.
- [x] **Flip & Mirror**: Mirror horizontal (`flipX`) dan vertical (`flipY`) di preview dan render kanvas.
- [x] **Urutan/Layering Drag-and-Drop**: Reorder urutan layer stiker langsung via drag-and-drop pada daftar layer di sidebar.
- [x] **Stiker Anti-Menimpa Komponen Default**: Stiker otomatis berada di layer bawah tabel, kartu, dan judul (baik di preview maupun saat render ekspor).
- [x] **Tampilan Panel Layer Compact**: Menghilangkan input koordinat X/Y, menampilkan informasi dimensi ringkas dan sudut rotasi.

---

### Phase 5: Pricing Engine, Sanitasi Data & Komparasi iBox — DALAM PROGRES
- [x] **Filter Harga Kosong**: Menghilangkan tanda strip (`-`) atau harga `0`/kosong dari tabel agar baris katalog selalu menampilkan nominal valid tanpa simbol strip.
- [x] **Shape di Harga iPhone**: Opsi *plain*, *pill*, dan *rounded* untuk membungkus nominal harga pada card iPhone.
- [x] **Stiker "Hemat" Otomatis**: Badge otomatis pada card iPhone ketika harga iBox (`EXIBOX`) lebih tinggi dari harga katalog aktif.
- [x] **Komparasi Harga iBox**:
  - Menggunakan `EXIBOX` sebagai harga pembanding.
  - Menghitung dan menampilkan selisih nominal penghematan secara otomatis di kanvas card.
- [ ] Perluasan badge hemat dan komparasi resmi ke seluruh kategori Apple serta mode tabel/list.

---

### Phase 6: Personalisasi Card Android & Seleksi Kustom Editor — SELESAI
- [x] **Checklist Pilihan Editor**: Fitur bagi editor konten untuk mencentang/memilih secara spesifik produk mana saja yang ingin dimasukkan ke dalam katalog sebelum proses generate dijalankan.
- [x] **Tombol Aksi Massal**: Tersedia *Select All*, *Deselect*, dan filter cepat produk terpilih.
- [x] **Personalisasi Card Android**: Opsi custom untuk card produk (jumlah kolom, tinggi gambar, ukuran font model & harga, warna font model & harga).

---

### Phase 7: Stabilisasi, Visual QA & Ekspor Berkualitas Tinggi — BERKELANJUTAN
- [x] Menjaga seluruh automated feature tests (`MarketingDashboardShellTest`) tetap hijau dan passing.
- [x] Build aset CSS/JS produksi (`npm run build`) berjalan bersih tanpa error.
- [ ] Visual smoke test untuk ekspor PDF dan gambar resolusi tinggi (Story 1080x1920, Feed 1080x1350, A4).
- [ ] Audit performa memori canvas untuk multi-halaman pada browser mobile dan desktop.
