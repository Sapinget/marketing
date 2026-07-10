# Section Card Toolbar Roadmap

## Tujuan

Merapikan tab yang berorientasi tabel agar tidak memakai `section-card-body` hanya sebagai header dekoratif. Toolbar tabel harus konsisten, ringkas, dan selalu ditempatkan tepat di atas tabel.

## Aturan Layout Target

### Struktur

- Summary / KPI card tetap boleh ada di atas jika memang penting.
- Toolbar tabel ditempatkan di dalam `section-card section-card-shell`, tetapi sebagai area kontrol yang terpisah dari canvas tabel.
- `table-toolbar-shell` tidak boleh terasa menyatu sebagai 1 canvas visual dengan tabel.
- Tabel desktop ditempatkan langsung di bawah toolbar yang sama, namun tetap sebagai area terpisah:
  - toolbar = area kontrol
  - tabel = area data
- Jika perlu, pemisahan visual bisa ditegaskan dengan:
  - border bawah pada toolbar
  - background toolbar yang jelas terpisah
  - radius / spacing yang tidak membuat toolbar terlihat seperti baris pertama tabel
- `section-card-body` hanya dipakai untuk:
  - ringkasan,
  - hero informasi yang benar-benar dibutuhkan,
  - konten non-tabel.
- Jika seluruh pola toolbar + tabel sudah stabil, rapi, dan tidak lagi membutuhkan framing dekoratif, wrapper `section-card` pada area tabel harus dihapus.
- Target akhirnya:
  - toolbar tetap ada sebagai area kontrol terpisah
  - tabel tetap ada sebagai area data terpisah
  - tetapi keduanya tidak dipaksa tetap berada dalam dekorasi `section-card` jika itu justru membuat canvas terasa berat
- Dengan kata lain, `section-card` untuk area tabel adalah solusi transisi, bukan asumsi final.

### Urutan Toolbar Desktop

- Paling kiri: `search`
- Kanan setelah `search`: `date`
- Kanan setelah `date`: `export`
- Kanan setelah `export`: `filter lain`
- Paling kanan: `add/reset`

### Detail Prioritas Kanan Toolbar

Urutan visual dari kiri ke kanan pada grup kanan:

1. `date`
2. `export`
3. `filter lain`
4. `add`
5. `reset`

### Catatan Praktis

- `search` berdiri sendiri di sisi kiri.
- `date`, `export`, `filter lain`, `add/reset` berada dalam grup kanan.
- Jika `add` dan `reset` tampil bersamaan:
  - `add` di kiri `reset`
  - `reset` paling kanan
- Jika ada lebih dari satu `export`:
  - `Excel` di kiri `PDF`
- Jika ada lebih dari satu `filter lain`:
  - urutkan dari yang paling sering dipakai ke yang paling spesifik
  - contoh: `status` lalu `platform` lalu `kategori`

### Aturan Header Tabel Sortable

- Jika judul kolom tabel membungkus ke 2 baris atau lebih, ikon panah sort naik/turun harus tetap berada di samping teks.
- Ikon sort tidak boleh turun ke bawah teks header.
- Header sortable harus memakai layout inline yang menjaga:
  - teks tetap bisa wrap
  - ikon tetap sejajar secara horizontal
  - alignment tetap stabil antar kolom

### Aturan Warna Header Tabel

- Header tabel wajib memakai warna biru template secara global, bukan styling lokal per view.
- Teks header wajib putih.
- Jika ada class lokal seperti `bg-slate-50`, `bg-slate-50/50`, atau teks abu-abu di `thead` / `th`, hasil akhirnya tetap harus tertimpa oleh CSS global.
- Implementasi harus dipastikan benar-benar terlihat di runtime, bukan hanya ada rule yang kalah prioritas.

### Aturan Ukuran dan Pembagian Kolom Tabel

- Tabel desktop harus dibagi agar isi terbaca penuh dan tidak terasa berat di satu sisi.
- Ukuran kolom header tidak boleh sepenuhnya bergantung pada fixed value yang kaku.
- Lebar kolom boleh memakai fixed width sebagai guardrail, tetapi tetap harus responsif terhadap canvas tabel.
- Gunakan pendekatan campuran yang lebih aman:
  - kolom kecil boleh fixed
  - kolom utama boleh memakai proporsi `%`
  - jika perlu gunakan `min-width` + `max-width` + `table-fixed` secara seimbang
- Hindari kondisi semua kolom memakai pixel width keras karena itu membuat tabel terasa kaku saat canvas berubah.
- Saat viewport desktop melebar atau menyempit, header tetap harus mengikuti distribusi ruang tabel secara wajar.
- Kolom paling kiri:
  - rata kiri
  - biasanya untuk nama, judul, program, customer, atau deskripsi utama
- Kolom paling kanan:
  - rata kanan
  - dipakai untuk angka, nominal, views, quantity, persentase, atau total
- Kolom tengah:
  - default prefer rata kiri
  - hanya pakai rata tengah jika memang status kecil, nomor urut, atau badge pendek
- Header dan isi kolom harus mengikuti alignment yang sama agar rapi.
- Hindari campuran alignment yang tidak perlu antara header dan isi pada kolom yang sama.
- Lebar kolom harus dibagi dengan sengaja:
  - kolom identitas utama diberi ruang paling besar
  - kolom angka ringkas diberi ruang paling kecil
  - kolom status / badge diberi ruang secukupnya dan tidak memaksa kolom lain menyempit
- Jika tabel lebar, gunakan `min-width` yang masuk akal, bukan membiarkan semua kolom berebut lebar otomatis.
- Jika perlu, tetapkan prioritas seperti ini:
  - prioritas lebar terbesar: nama / judul / deskripsi
  - prioritas menengah: kategori / platform / editor / tanggal
  - prioritas terkecil: angka, badge, aksi, nomor

### Aturan Responsif Header dan Kolom

- Header tabel harus responsif terhadap perubahan lebar canvas desktop.
- Jangan mengunci semua `th` hanya dengan pixel width tetap.
- Prioritaskan kombinasi:
  - `table-fixed`
  - `min-width` tabel yang masuk akal
  - sebagian kolom fixed kecil
  - sebagian kolom proporsional
- Jika satu tab butuh tabel lebar, responsifnya dicapai dengan distribusi kolom yang adaptif, bukan dengan semua kolom dipaksa angka tetap.
- Kolom identitas utama harus ikut menyerap sisa ruang yang tersedia.
- Kolom kecil seperti:
  - nomor
  - aksi
  - badge status
  - icon/link pendek
  boleh tetap sempit dan stabil.
- Saat dilakukan audit, cek dua kondisi:
  - desktop sempit namun masih dalam breakpoint desktop
  - desktop lebar dengan ruang horizontal besar
- Di kedua kondisi itu, header dan isi harus tetap sejajar, rapi, dan tidak terlihat seperti layout yang kaku.

### Prinsip Alignment Tabel

- Default prefer rata kiri untuk mayoritas header dan isi.
- Rata kanan hanya untuk data numerik dan nilai akumulatif.
- Rata tengah hanya untuk:
  - nomor urut
  - badge status pendek
  - tombol aksi kecil
- Jangan membuat kolom kiri rata kiri tetapi isi kolomnya rata tengah atau rata kanan tanpa alasan jelas.

## Komponen yang Tidak Perlu Diubah dengan Pola Ini

- [ ] `dashboard`
- [ ] `calendar`
- [ ] `analisa-insight`
- [ ] `profile`
- [ ] `settings`
- [ ] `budgeting` form dan modal
- [ ] `talent-bonus`
- [ ] `editor-performance`

## Batch Implementasi

### Batch 1

- [x] `master-plan`
- [x] `distribution`
- [x] `analytics`
- [x] `unboxing`

### Batch 2

- [x] `order-online`
- [x] `unit-ditanya`
- [x] `claim-garansi`
- [x] `keep-barang`

### Batch 3

- [x] `harga-kompetitor`
- [x] `laporan-event`
- [x] `ads-log`
- [x] `sell-out`

### Batch 4

- [x] `program-promo`
- [x] `auth-users`
- [x] `top-content`
- [x] `low-content`
- [x] `meta-story`
- [x] `meta-feed`

## Checklist Per Tab

Untuk tiap tab di atas, target selesai bila semua poin ini terpenuhi:

- [x] Toolbar pindah tepat di atas tabel desktop
- [x] Toolbar desktop terasa sebagai area kontrol terpisah, bukan bagian dari canvas tabel — `border-bottom: 1px solid var(--ppp-line)` + `background: var(--ppp-card)` sudah ada di CSS
- [x] `search` berada di kiri
- [x] `date` berada di grup kanan
- [x] `export` berada di kanan setelah `date`
- [x] `filter lain` berada di kanan setelah `export`
- [x] `add/reset` berada paling kanan
- [x] Tabel desktop berada dalam `section-card-shell`
- [~] Evaluasi final: jika wrapper `section-card` sudah tidak dibutuhkan, wrapper tersebut dihapus — hold, masih transisi
- [x] Header dekoratif yang tidak perlu sudah dihapus atau diperkecil
- [x] Header sortable menjaga ikon panah tetap di samping teks saat judul wrap — global CSS rule diperkuat
- [x] Header tabel benar-benar tampil biru template di runtime — `#app table thead { background: var(--ppp-accent) !important }` confirmed, dead local bg-slate classes removed dari 7 file
- [x] Teks header tabel benar-benar tampil putih — `#app table th { color: rgb(255 255 255) !important }` confirmed, dead text-slate-500 classes removed dari auth-users
- [~] Ukuran kolom header responsif dan tidak terasa kaku — mayoritas `table-fixed` + campuran %, butuh runtime check
- [x] Kolom paling kiri rata kiri
- [x] Kolom paling kanan rata kanan
- [x] Header dan isi tiap kolom memakai alignment yang sama
- [~] Lebar kolom dibagi sengaja dan tetap terbaca penuh — kolom utama sudah proporsional, butuh runtime check visual
- [x] Mobile layout tetap aman — toolbar + cards + pagination
- [ ] Tidak merusak test existing — N/A, tidak ada test suite terdeteksi

## Catatan Review

- [x] Pastikan toolbar tidak pindah ke bawah tabel pada desktop
- [x] Pastikan `table-toolbar-shell` tidak terbaca seperti header baris pertama tabel — `border-bottom` + background berbeda dari tabel sudah di CSS global
- [x] Pastikan wrapper `section-card` tidak dipertahankan hanya karena pola lama — konsisten pakai `section-card-shell`
- [x] Pastikan urutan tombol tetap konsisten lintas tab — search → date → export → filter → add → reset
- [x] Pastikan summary cards, jika ada, tetap terpisah dari toolbar tabel
- [x] Pastikan tab non-tabel tidak ikut dipaksa ke pola ini
- [x] Pastikan ikon sort di header tabel tidak jatuh ke bawah saat label kolom membungkus — global rule: `display: inline-flex; align-items: center;`
- [x] Pastikan rule header biru menang terhadap class lokal di setiap tabel — dead bg-slate classes sudah dihapus dari 7 file, `!important` menang
- [~] Pastikan kombinasi fixed width dan proporsional masih responsif saat canvas desktop berubah — butuh runtime check
- [x] Pastikan alignment kiri/kanan kolom konsisten antara header dan isi
- [x] Pastikan kolom angka di kanan tidak merusak keterbacaan kolom teks di kiri

## Progress Notes

- [x] Batch 1 selesai: `master-plan`, `distribution`, `analytics`, `unboxing` — decorative header dihapus, wrapper konsisten `section-card-shell`, mobile toolbar (`table-toolbar-shell`) ditambahkan di mobile section
- [x] `distribution` dan `analytics`: wrapper desktop diperbaiki dari `md:bg-white md:radius-panel` menjadi `hidden md:block section-card section-card-shell`
- [x] `unboxing`: decorative header + mobile toolbar lama dihapus, mobile toolbar baru ditambahkan
- [x] Batch 2 selesai: `order-online`, `unit-ditanya`, `claim-garansi`, `keep-barang` — decorative header dihapus, mobile toolbar (`table-toolbar-shell`) ditambahkan di mobile section
- [x] `keep-barang`: summary cards dipindahkan ke atas (sebelumnya terperangkap di dalam `section-card-body`)
- [x] Header tabel sudah diarahkan ke rule biru global di CSS shell
- [x] Rule global sortable header diperkuat agar ikon tetap di samping teks
- [x] `master-plan`, `unboxing`, dan semua tab Batch 2 sudah memakai lebar kolom desktop yang disengaja (`table-fixed`)
- [x] Batch 3 selesai: toolbar dipindahkan ke `table-toolbar-shell` dalam `section-card-shell`, mobile toolbar ditambahkan
- [x] Batch 4 selesai: toolbar dipindahkan ke `table-toolbar-shell` dalam `section-card-shell`, mobile toolbar ditambahkan
- [x] Final pass: pemisahan visual toolbar desktop vs tabel — `border-bottom` + `background: var(--ppp-card)` di `.table-toolbar-shell` sudah ada, konsisten global
- [x] Final pass: biru header — dead bg-slate dihapus dari 7 file (master-plan, distribution, analytics, program-promo, auth-users, meta-story, meta-feed), `!important` menang; skeleton section-card-body dihapus dari 10 file
- [~] Final pass: alignment kolom dan lebar responsif — butuh runtime check visual di browser
