# Analisis Perubahan Modifikasi Lokal

Analisis ini dibuat dari kondisi working tree lokal repo `surat_siega` pada 10 April 2026. Fokus utamanya adalah menjelaskan perubahan yang berdampak ke tampilan, alur sistem, dan perbaikan perilaku aplikasi. File generated seperti `node_modules` dan `public/build` tetap dicatat, tetapi dipisahkan dari file source utama.

## Ringkasan Besar

- Dari kombinasi perubahan controller, policy, service, route, dan view, arah desain `Surat Keputusan` bergeser dari model "dokumen yang ditujukan ke penerima tertentu" menjadi model "SK global" yang setelah terbit dapat dilihat semua pengguna aktif. Ini adalah inferensi kuat dari source code.
- Alur pembuatan `Surat Keputusan` sekarang lebih matang: nomor surat bisa dipreview otomatis, tombol submit lebih konsisten, dan lampiran sudah bisa diunggah sementara sebelum record SK benar-benar tersimpan.
- Halaman approval `Surat Keputusan` dan `Surat Tugas` direfaktor menjadi pola UI yang seragam, lebih modern, dan lebih interaktif dengan drag, resize, wheel-resize, live preview, dan reset layout.
- Halaman detail `Surat Keputusan` dan `Surat Tugas` juga direfaktor ke pola shared style yang lebih rapi, responsif, dan informatif.
- Tabel daftar `Surat Keputusan` dan `Surat Tugas` dibuat lebih responsif di mobile dengan DataTables responsive, prioritas kolom, dan badge status yang lebih jelas.
- Deploy di subdirectory `/surat_siega` dibenahi melalui kombinasi `.htaccess` baru dan `index.php` root sebagai front controller wrapper.
- Toolchain frontend ikut dinaikkan versinya agar cocok dengan pola Sass `@use` dan build pipeline yang lebih baru.

## File Infrastruktur, Build, dan Layout

### `.htaccess`

- Perubahan utama:
  File ini dirombak dari pola redirect ke `/not-found` menjadi pola `403 Forbidden` langsung untuk folder/file sensitif.
- Dampak sistem:
  Akses ke `app`, `config`, `database`, `resources`, `routes`, `storage`, `vendor`, `node_modules`, `.env`, `artisan`, `composer.json`, `package.json`, `vite.config.js`, dan file `.blade.php` sekarang diblokir langsung di level Apache.
- Dampak sistem:
  Request PHP langsung di luar `/public` diblokir berdasarkan `THE_REQUEST`, sehingga internal rewrite ke `index.php` tidak ikut rusak.
- Dampak deployment:
  Ditambahkan `RewriteBase /surat_siega/`, normalisasi trailing slash, dan rewrite ke root `index.php`, sehingga URL bisa tetap bersih tanpa `/public`.
- Perbaikan yang terlihat:
  Keamanan lebih ketat, URL lebih konsisten, dan struktur deploy subdirectory jadi lebih jelas.

### `index.php` baru di root

- Perubahan utama:
  Ditambahkan front controller wrapper yang hanya memanggil `require __DIR__.'/public/index.php';`.
- Dampak sistem:
  Ini pasangan langsung dari `.htaccess` baru agar aplikasi bisa dijalankan dari subfolder tanpa mengekspos `/public` di URL.
- Perbaikan yang terlihat:
  Struktur deploy menjadi lebih praktis untuk shared hosting atau subdirectory deployment.

### `package.json`

- Perubahan utama:
  `@vitejs/plugin-vue` dinaikkan dari `^5.0.4` ke `^6.0.5` dan `laravel-vite-plugin` dari `^1.0` ke `^2.1.0`.
- Dampak build:
  Ini menandakan upgrade toolchain frontend.
- Perbaikan yang terlihat:
  Menyiapkan project ke stack Vite/Laravel plugin yang lebih baru.

### `package-lock.json`

- Perubahan utama:
  Lockfile ikut menarik versi baru `rollup`, `immutable`, `picomatch`, plugin util baru, dan dependensi lintas platform.
- Dampak build:
  Menandakan `npm install` atau upgrade dependency memang sudah dijalankan.
- Catatan penting:
  Dari metadata package yang tertulis di lockfile, plugin baru menuntut Node yang lebih baru. Ini perlu diperhatikan saat `npm run dev` atau `npm run build`.

### `vite.config.js`

- Perubahan utama:
  Ditambahkan konfigurasi `css.preprocessorOptions.scss.quietDeps = true`.
- Dampak build:
  Warning deprecation dari dependency SCSS seperti Bootstrap akan lebih tenang saat compile.
- Perbaikan yang terlihat:
  Output terminal build jadi lebih bersih dan fokus ke error yang benar-benar penting.

### `resources/sass/app.scss`

- Perubahan utama:
  Import lama berbasis `@import` diganti ke `@use`.
- Dampak tampilan:
  Bootstrap sekarang dikonfigurasi lewat variable Sass modern, bukan pola import lama.
- Dampak arsitektur:
  Import font dipindahkan keluar dari SCSS.
- Perbaikan yang terlihat:
  Cocok dengan ekosistem Sass modern dan mengurangi masalah kompatibilitas build.

### `resources/views/layouts/app.blade.php`

- Perubahan utama:
  Font `Nunito` sekarang di-load lewat `<link rel="stylesheet">`.
- Dampak tampilan:
  Font tetap terpasang meskipun import font sudah dikeluarkan dari `app.scss`.
- Perbaikan yang terlihat:
  Asset font jadi lebih eksplisit dan tidak bergantung pada pipeline SCSS.

### `resources/views/layouts/sidebar.blade.php`

- Perubahan utama:
  Label menu `Surat Keputusan Saya` diubah menjadi `Riwayat Proses SK` atau `Riwayat SK Saya` tergantung konteks.
- Dampak tampilan:
  Istilah di sidebar sekarang lebih sesuai dengan perilaku halaman aktual.
- Dampak sistem:
  Untuk role pengguna biasa (`4,5,6`), menu `surat_keputusan.mine` dihapus dari sidebar dan mereka diarahkan hanya ke `SK Terbit`.
- Perbaikan yang terlihat:
  Mengurangi kebingungan role umum yang sebelumnya melihat menu riwayat internal yang sebenarnya tidak relevan untuk mereka.

## Backend Surat Keputusan

### `app/Http/Controllers/SuratKeputusanController.php`

- Perubahan utama:
  Controller ini menerima dependency baru `NomorSuratService` dan `SuratKeputusanNotificationService`.
- Perubahan utama:
  Relasi `penerima` dihapus dari banyak eager-loading pada list, detail approval, download PDF, dan preview.
- Perubahan utama:
  `mine()` diubah total menjadi halaman riwayat proses berbasis role.
- Dampak sistem:
  Admin TU melihat SK yang ia buat atau proses, sedangkan Dekan/WD hanya melihat SK yang memang ia tandatangani atau setujui.
- Dampak sistem:
  Pengguna biasa langsung di-redirect ke daftar `SK Terbit`, artinya riwayat internal tidak lagi dianggap konsumsi umum.
- Perubahan utama:
  `create()` sekarang membangun `autoNomor`, `defaultKodeKlasifikasi`, `defaultUnitKode`, `draftToken`, dan `tempAttachments`.
- Dampak form:
  Saat membuka form buat SK, user langsung mendapat nomor kandidat dan state lampiran draft sementara.
- Perubahan utama:
  `store()` sekarang memindahkan lampiran sementara ke lampiran permanen setelah SK berhasil dibuat.
- Dampak sistem:
  User bisa upload lampiran sebelum SK punya ID, lalu lampiran otomatis dipindah ke folder final saat save berhasil.
- Dampak UX:
  Bila status hasil simpan masih `draft`, redirect diarahkan ke halaman edit, bukan langsung ke index.
- Perubahan utama:
  `update()` memberi redirect khusus ke halaman edit bila status tetap `draft`.
- Perbaikan UX:
  User bisa langsung lanjut revisi atau upload lampiran tambahan tanpa harus mencari lagi data yang barusan disimpan.
- Perubahan utama:
  `approveForm()` dan `approvePreview()` sekarang membaca konfigurasi posisi dan ukuran TTD/Cap dari `ttd_config` dan `cap_config`, termasuk offset X/Y.
- Perubahan utama:
  `approve()` menyimpan konfigurasi itu ke JSON config sekaligus ke kolom legacy.
- Dampak sistem:
  Posisi dan ukuran tanda tangan/cap approval menjadi persistent, bukan hanya preview sementara.
- Perubahan utama:
  `approve()` menampilkan pesan baru bahwa SK yang disetujui masuk ke tahap menunggu penerbitan.
- Dampak sistem:
  Status `disetujui` sekarang dipahami sebagai state transisi sebelum `terbit`.
- Perubahan utama:
  `terbitkan()` sekarang juga menyimpan `published_at` dan `published_by`, bukan hanya `tanggal_terbit` dan `terbitkan_oleh`.
- Perubahan utama:
  Notifikasi manual ke relasi `penerima` dan email langsung dipindahkan ke service notification terpusat.
- Dampak sistem:
  Logika publish menjadi lebih bersih, metadata penerbitan lebih lengkap, dan notifikasi terpusat lebih mudah dirawat.
- Perubahan utama:
  `reopen()` dan proses rollback publish juga membersihkan `published_at` dan `published_by`.
- Perbaikan integritas data:
  Metadata publish tidak tertinggal saat status ditarik mundur.
- Perubahan utama:
  Ditambahkan seluruh helper untuk lampiran sementara berbasis session: validasi payload, normalisasi token, session key, formatter, persist temp attachment, upload temp attachment, dan delete temp attachment.
- Dampak sistem:
  Ini adalah fondasi besar untuk flow draft lampiran sebelum record SK terbentuk.

### `app/Http/Requests/StoreKeputusanRequest.php`

- Perubahan utama:
  Validasi tambahan yang dulu mewajibkan minimal satu penerima saat `pending/terkirim` dihapus.
- Dampak sistem:
  SK sekarang bisa diajukan tanpa harus memiliki `penerima_internal` atau `penerima_eksternal`.
- Perbaikan atau perubahan bisnis:
  Ini konsisten dengan arah SK global. Jika memang disengaja, maka alurnya sudah sinkron. Jika tidak disengaja, ini perlu dicek lagi karena business rule lama berubah.

### `app/Http/Requests/UpdateKeputusanRequest.php`

- Perubahan utama:
  Sama seperti request store, rule tambahan yang memaksa ada penerima saat pengajuan dihapus.
- Dampak sistem:
  Edit dan submit ulang SK tidak lagi memaksa daftar penerima.

### `app/Policies/KeputusanHeaderPolicy.php`

- Perubahan utama:
  `view` sekarang mengizinkan semua user aktif melihat SK dengan status `terbit` atau `arsip`.
- Dampak sistem:
  Akses baca untuk SK publik menjadi jauh lebih longgar dan tidak lagi tergantung relasi penerima many-to-many.
- Perubahan utama:
  `approve` dan `reject` sekarang hanya boleh oleh Dekan/WD yang memang tercatat sebagai `penandatangan` SK tersebut.
- Perbaikan keamanan:
  Dekan/WD lain yang bukan penandatangan target tidak bisa lagi sembarang approve/reject.

### `app/Services/BaseNotificationService.php`

- Perubahan utama:
  Ditambahkan helper `getActiveUserIds()`.
- Dampak sistem:
  Service turunan sekarang bisa mengambil daftar semua user aktif secara konsisten.
- Perbaikan arsitektur:
  Menghindari query pengguna aktif yang duplikatif di banyak tempat.

### `app/Services/SuratKeputusanNotificationService.php`

- Perubahan utama:
  `notifyRecipients()` diganti arah menjadi `notifyPublished()` yang mengirim notifikasi ke seluruh pengguna aktif.
- Dampak sistem:
  Begitu SK terbit, notifikasi tidak lagi hanya dikirim ke penerima tertentu, melainkan diumumkan secara global.
- Perbaikan arsitektur:
  Disediakan alias backward compatibility, jadi pemanggil lama tetap bisa jalan.

### `app/Services/SuratKeputusanService.php`

- Perubahan utama:
  Rentang validasi ukuran TTD dan Cap diperlebar menjadi `10-150 mm` dan `10-100 mm`.
- Dampak sistem:
  Pengaturan approval jadi lebih fleksibel.
- Perubahan utama:
  `validateDimension()` tidak lagi me-reset nilai out-of-range ke nilai tengah; sekarang nilainya di-clamp ke batas minimum/maksimum.
- Perbaikan perilaku:
  Hasil input user jadi lebih prediktif.
- Perubahan utama:
  `penerima_eksternal` tidak lagi sekadar di-`sanitize_input`, tetapi dinormalisasi menjadi array yang aman melalui `sanitizePenerimaEksternal()`.
- Dampak sistem:
  Input string, JSON, atau array campuran bisa dibersihkan dan disimpan lebih konsisten.
- Perbaikan data:
  Mengurangi potensi format kacau pada `penerima_eksternal`.

### `app/Services/SkPdfService.php`

- Perubahan utama:
  Rentang ukuran TTD/Cap ikut diperlebar seperti service SK.
- Perubahan utama:
  `validateDimension()` diubah menjadi clamp.
- Dampak hasil PDF:
  Layout PDF lebih toleran terhadap ukuran custom dan tidak tiba-tiba kembali ke nilai tengah.

### `app/Jobs/SendSkEmail.php`

- Perubahan utama:
  Properti `public bool $afterCommit = true` diganti ke pemanggilan `$this->afterCommit()` di constructor.
- Perbaikan teknis:
  Tujuan tetap sama, yaitu memastikan job hanya benar-benar dijalankan setelah transaksi database selesai commit.

### `routes/web.php`

- Perubahan utama:
  Ditambahkan route upload dan delete lampiran sementara:
  `attachments.temp.upload` dan `attachments.temp.delete`.
- Dampak sistem:
  Frontend create SK sekarang punya endpoint resmi untuk upload lampiran sebelum SK disimpan.

## Shared UI Baru

### `resources/views/shared/approval/styles.blade.php` baru

- Perubahan utama:
  Style approval dipusatkan ke satu partial shared.
- Dampak tampilan:
  Halaman approval SK dan ST sekarang punya hero header, card info, card preview, control panel, tip, dan responsive layout yang seragam.
- Perbaikan UX:
  Tampilan approval lebih modern dan konsisten antarmodul.

### `resources/views/shared/approval/controls.blade.php` baru

- Perubahan utama:
  Kontrol input lebar TTD, lebar cap, opasitas cap, dan hidden field offset dipusatkan ke partial bersama.
- Dampak tampilan:
  UI slider dan numeric input jadi seragam antara SK dan ST.
- Perbaikan arsitektur:
  Mengurangi duplikasi komponen approval.

### `resources/views/shared/approval/scripts.blade.php` baru

- Perubahan utama:
  Seluruh interaksi approval dipusatkan ke satu script reusable.
- Dampak UX:
  User bisa drag posisi TTD/cap, resize via handle, resize via scroll wheel, live preview via fetch AJAX, auto-fit preview ke container, dan reset ke default.
- Dampak UX:
  Tombol approve akan disable saat submit untuk mencegah double submit.
- Perbaikan arsitektur:
  Inline script panjang di dua halaman approval berhasil disederhanakan menjadi satu sumber logic.

### `resources/views/shared/detail/styles.blade.php` baru

- Perubahan utama:
  Style halaman detail dipusatkan ke partial shared.
- Dampak tampilan:
  Halaman detail SK dan ST sekarang memakai pola card, chip, preview shell, sidebar sticky, attachment card, dan responsive detail list yang sama.
- Perbaikan UX:
  Tampilan detail lebih bersih dan lebih nyaman di mobile.

## Frontend Surat Keputusan

### `resources/views/surat_keputusan/create.blade.php`

- Perubahan utama:
  Partial form sekarang menerima `draftToken` dan `tempAttachments`.
- Dampak sistem:
  Halaman create siap mendukung flow lampiran sementara.

### `resources/views/surat_keputusan/edit.blade.php`

- Perubahan utama:
  Alert success, error, dan daftar error inline dihapus.
- Dampak tampilan:
  Halaman edit lebih bersih.
- Perbaikan arsitektur:
  Mengindikasikan flash/error sekarang diandalkan dari global layout, sehingga tidak dobel.

### `resources/views/surat_keputusan/partials/_form.blade.php`

- Perubahan utama:
  Ditambahkan hidden field `mode` dan `draft_token`.
- Perubahan utama:
  Nomor surat builder sekarang bisa diparse ulang dari nomor lengkap yang sudah ada.
- Dampak UX:
  Saat nomor berhasil di-reserve atau saat edit data lama, komponen builder tetap sinkron dengan string nomor final.
- Perubahan utama:
  Tombol submit tidak lagi menyimpan `name="mode"` per tombol, tetapi memakai `data-submit-mode`.
- Perbaikan perilaku:
  Submit mode jadi lebih mudah dikontrol dari JavaScript dan lebih konsisten lintas desktop/mobile button.

### `resources/views/surat_keputusan/partials/_form_scripts.blade.php`

- Perubahan utama:
  Ditambahkan `submit_mode` management, parsing nomor ke builder, dan flag `window.__skIgnoreBeforeUnload`.
- Dampak UX:
  Prompt leave-page tidak lagi mengganggu saat upload lampiran sementara atau submit form yang memang disengaja.
- Dampak UX:
  Jika backend mengembalikan nomor penuh hasil reserve/generate, field builder bisa langsung mengikuti format nomor tersebut.
- Perbaikan teknis:
  Submit mode draft/pending/revisi_dan_setujui menjadi lebih stabil.

### `resources/views/surat_keputusan/partials/attachments_section.blade.php`

- Perubahan utama:
  Section lampiran sekarang mendukung dua mode: lampiran permanen untuk SK yang sudah punya ID, dan lampiran sementara untuk draft yang belum tersimpan.
- Dampak tampilan:
  Ada informasi mode upload, tabel lampiran gabungan, empty state dinamis, total lampiran, dan badge berbeda untuk lampiran sementara.
- Dampak UX:
  Upload lampiran sementara dilakukan via AJAX dan langsung menambah row ke tabel tanpa reload halaman.
- Dampak UX:
  Hapus lampiran sementara juga bisa langsung dari tabel dengan fetch `DELETE`.
- Perbaikan sistem:
  Flow create SK menjadi jauh lebih natural karena user tidak perlu menunggu record final dulu baru bisa menambahkan lampiran.

### `resources/views/surat_keputusan/partials/_core.blade.php`

- Perubahan utama:
  Area tanda tangan diperluas dari `28mm` menjadi `36mm`.
- Perubahan utama:
  Posisi TTD dan cap sekarang absolute-positioned, TTD berada di layer depan dan cap di belakang dengan offset lebih natural.
- Dampak tampilan:
  Hasil preview dan PDF terlihat lebih realistis, terutama saat cap ditumpuk di belakang tanda tangan.

### `resources/views/surat_keputusan/index.blade.php`

- Perubahan utama:
  Tabel di-redesign dengan class `surat-table`, status badge custom, responsive DataTables, dan prioritas kolom.
- Dampak tampilan:
  Tabel lebih rapi, lebih jelas, dan lebih usable di mobile.
- Perubahan utama:
  Kolom `Status` dipindah ke posisi lebih penting dan label `disetujui` diperjelas menjadi `Disetujui / Menunggu Terbit`.
- Dampak UX:
  User lebih mudah memahami state workflow SK.
- Perubahan utama:
  Teks konfirmasi publish diubah menjadi "diumumkan ke seluruh pengguna aktif".
- Dampak sistem:
  Ini sinkron dengan perubahan notifikasi global.

### `resources/views/surat_keputusan/keputusan_saya.blade.php`

- Perubahan utama:
  Judul dan deskripsi halaman sekarang memakai `pageMeta`.
- Dampak sistem:
  Satu view bisa dipakai untuk konteks `Riwayat Proses SK` atau `Riwayat SK Saya` sesuai role.
- Perubahan utama:
  Statistik status diperluas sampai `ditolak`, `terbit`, dan `arsip`.
- Dampak tampilan:
  User melihat ringkasan proses yang lebih lengkap.
- Perubahan utama:
  Tabel ikut direfaktor ke badge status dan responsive DataTables seperti halaman index utama.

### `resources/views/surat_keputusan/approve.blade.php`

- Perubahan utama:
  Inline CSS besar dan inline script lama diganti dengan komponen shared approval.
- Dampak tampilan:
  Halaman approval terlihat jauh lebih modern, konsisten, dan memiliki panel informasi yang lebih jelas.
- Dampak UX:
  Approver sekarang mendapatkan pengalaman drag-resize-preview yang lebih enak dipakai.
- Perubahan utama:
  Modal penolakan dibuat lebih tegas dan instruktif.
- Perbaikan UX:
  Alasan revisi lebih didorong untuk ditulis jelas.

### `resources/views/surat_keputusan/show.blade.php`

- Perubahan utama:
  Halaman detail dirombak ke style shared detail.
- Dampak tampilan:
  Ada header chip status/nomor/tanggal, preview shell lebih rapi, sidebar card lebih informatif, dan lampiran tampil sebagai attachment card.
- Perubahan utama:
  Panel fokus pada metadata SK, pihak terkait, ringkas isi, tembusan, dan lampiran.
- Inferensi perubahan bisnis:
  Tidak ada lagi fokus ke daftar penerima internal sebagai unsur utama tampilan, sejalan dengan arah SK global.

## Backend dan Frontend Surat Tugas

### `app/Http/Controllers/TugasController.php`

- Perubahan utama:
  Approval preview dan approval form sekarang membaca ukuran/offset TTD dan cap dari `ttd_config` dan `cap_config`.
- Dampak sistem:
  Posisi approval ST menjadi persistent dan konsisten dengan pendekatan baru di SK.

### `resources/views/surat_tugas/approve.blade.php`

- Perubahan utama:
  Halaman ini direfaktor penuh memakai `shared.approval.styles`, `shared.approval.controls`, dan `shared.approval.scripts`.
- Dampak tampilan:
  Approval ST sekarang konsisten dengan approval SK.
- Dampak UX:
  Preview final bisa diatur dengan drag, resize, dan reset layout seperti SK.

### `resources/views/surat_tugas/partials/_core.blade.php`

- Perubahan utama:
  Area tanda tangan dinaikkan tinggi minimalnya dan cap digeser lebih ke kiri untuk overlap natural.
- Dampak tampilan:
  Hasil tanda tangan dan cap di ST lebih proporsional dan mirip hasil dokumen fisik.

### `resources/views/surat_tugas/index.blade.php`

- Perubahan utama:
  Tabel daftar ST diperbarui dengan badge status custom, prioritas kolom responsive, dan style tabel yang lebih modern.
- Dampak tampilan:
  Daftar surat lebih enak dibaca di desktop maupun mobile.

### `resources/views/surat_tugas/tugas_saya.blade.php`

- Perubahan utama:
  Halaman `tugas_saya` mengikuti pola tabel baru seperti index ST.
- Dampak tampilan:
  Status lebih jelas dan kolom mobile lebih rapi.

### `resources/views/surat_tugas/show.blade.php`

- Perubahan utama:
  Halaman detail ST direfaktor ke shared detail style.
- Dampak tampilan:
  Ringkasan isi surat, daftar penerima, info nomor turunan, metadata, dan aksi utama sekarang lebih tertata.
- Perbaikan UX:
  Informasi surat panjang menjadi jauh lebih mudah dipindai.

### `resources/views/surat_tugas/partials/_scripts_shared.blade.php`

- Perubahan utama:
  Konfigurasi DataTables shared ditingkatkan dengan responsive inline details dan prioritas kolom.
- Dampak tampilan:
  Banyak halaman ST berbasis tabel otomatis ikut membaik responsivitasnya.

## File Data, Artefak, dan Generated

### `database/omad1442_suratfikom.sql`

- Jenis perubahan:
  File dump database baru.
- Dampak sistem:
  Tidak mengubah runtime aplikasi secara langsung, tetapi berpotensi sangat besar bila ikut dipush karena memuat struktur dan data database.
- Catatan:
  Perlu dicek apakah memang sengaja ingin dijadikan backup di repo.

### `dummy.pdf`

- Jenis perubahan:
  File contoh atau file uji.
- Dampak sistem:
  Tidak ada dampak runtime kecuali memang dipakai untuk testing manual.

### `logbook_raw.txt`

- Jenis perubahan:
  Catatan historis perkembangan project.
- Dampak sistem:
  Tidak memengaruhi aplikasi, tetapi berguna sebagai jejak pekerjaan.

### `public/hot`

- Jenis perubahan:
  Marker Vite dev server.
- Dampak sistem:
  File ini biasanya muncul saat mode development dan umumnya tidak untuk production commit.

### `node_modules/**`

- Jenis perubahan:
  Regenerasi dependency lokal setelah upgrade package.
- Dampak sistem:
  Tidak perlu dianalisis satu per satu karena itu hasil install, bukan source of truth logic aplikasi.
- Dampak praktik repo:
  Biasanya file ini tidak ikut dipush.

### `public/build/**`

- Jenis perubahan:
  Artefak hasil compile frontend baru, termasuk hash asset baru dan penghapusan hash lama.
- Dampak sistem:
  Menandakan perubahan source frontend sudah dibuild ulang.
- Catatan:
  Relevan hanya jika strategi deploy repo Anda memang menyertakan hasil build.

## Kesimpulan

- Perubahan terbesar ada pada penyempurnaan `Surat Keputusan`: pergeseran ke model SK global, riwayat proses berbasis role, notifikasi global saat publish, dan lampiran sementara saat create.
- Perubahan terbesar kedua ada pada layer tampilan: approval page dan detail page untuk SK/ST sekarang jauh lebih konsisten, responsif, dan modern.
- Perubahan infrastruktur `.htaccess` + `index.php` menunjukkan fokus serius ke deploy subdirectory dan keamanan akses file internal.
- Upgrade toolchain frontend sudah dilakukan, tetapi perlu dicek kompatibilitas environment Node agar build tidak gagal di mesin lain.

## Catatan Risiko yang Perlu Dicek

- Jika business rule asli masih mengharuskan SK punya penerima tertentu sebelum diajukan, maka penghapusan validasi penerima di request perlu ditinjau ulang.
- Upgrade `laravel-vite-plugin` dan `@vitejs/plugin-vue` kemungkinan menuntut Node/Vite yang lebih baru.
- `database/omad1442_suratfikom.sql`, `public/hot`, dan isi `node_modules` sebaiknya dipastikan lagi apakah memang layak masuk commit/push.
- `.htaccess` saat ini hardcoded ke `/surat_siega/`. Bila path deploy berubah, rule rewrite juga harus ikut diubah.

## Kasus yang Ingin Diperbaiki

Bagian ini menambahkan analisis khusus untuk tiga kasus yang Anda sebutkan. Statusnya di bawah ini masih berupa analisis source code, belum berarti semua kasus sudah otomatis ter-fix.

### 1. Gagal menyimpan Surat Tugas di halaman create

URL terkait:
`http://localhost/fikomapp/surat_siega/public/surat_tugas/create`

Keluhan:
- terjadi kesalahan saat menyimpan surat tugas
- diduga bagian peran tidak terdeteksi oleh sistem / database

Indikasi akar masalah paling kuat:
- Deteksi role di modul `Surat Tugas` memang sangat bergantung pada `peran_id` hardcoded.
- Di [User.php](c:/laragon/www/surat_siega/app/Models/User.php), helper `isAdmin()`, `isDekan()`, `isWakilDekan()`, `isApprover()`, dan `canCreateSurat()` semuanya memakai asumsi tetap:
  `1 = Admin TU`, `2 = Dekan`, `3 = Wakil Dekan`.
- Di [TugasHeaderPolicy.php](c:/laragon/www/surat_siega/app/Policies/TugasHeaderPolicy.php), izin `create`, `update`, `approve`, `submit`, dan `viewApproveList` juga bergantung pada asumsi role tersebut.
- Di [AuthServiceProvider.php](c:/laragon/www/surat_siega/app/Providers/AuthServiceProvider.php), gate approval dan beberapa gate lain juga memakai asumsi yang sama.
- Di [TugasController.php](c:/laragon/www/surat_siega/app/Http/Controllers/TugasController.php), data dropdown form juga dibentuk dengan filter `peran_id`:
  pembuat diambil dari `peran_id = 1`, penandatangan dari `peran_id in [2,3]`, pengguna umum dari `peran_id != 1`.

Dampak ke sistem:
- Jika isi tabel `peran` atau `pengguna.peran_id` di database tidak sesuai mapping `1/2/3`, maka sistem akan salah mengenali siapa Admin TU, siapa approver, dan siapa penandatangan.
- Efeknya bisa muncul sebagai:
  dropdown pembuat/penandatangan salah atau kosong,
  authorize/gate gagal,
  workflow submit/approve tidak menemukan approver yang valid,
  atau penyimpanan terlihat gagal meskipun pesan ke user hanya generik.

Temuan tambahan penting:
- Di [TugasController.php](c:/laragon/www/surat_siega/app/Http/Controllers/TugasController.php), blok `catch (\Exception $e)` mengembalikan pesan umum:
  `Terjadi kesalahan saat menyimpan surat tugas. Silakan coba lagi.`
- Artinya error detail sebenarnya disembunyikan dari UI, jadi dugaan "peran tidak terdeteksi" sangat mungkin benar, tetapi saat ini tertutup oleh generic error handler.

Kesimpulan analisis:
- Ya, dugaan masalah role/peran sangat masuk akal.
- Modul `Surat Tugas` saat ini belum fleksibel terhadap perubahan mapping role di database karena banyak logic masih hardcoded ke `peran_id` 1, 2, dan 3.

### 2. Lampiran Dokumen Pendukung di Surat Keputusan belum bisa ditambahkan

URL terkait:
`http://localhost/fikomapp/surat_siega/public/surat_keputusan/create`

Keluhan:
- lampiran dokumen pendukung belum bisa ditambahkan

Indikasi teknis dari source:
- Secara arsitektur, flow lampiran sementara sebenarnya sudah disiapkan.
- Di [SuratKeputusanController.php](c:/laragon/www/surat_siega/app/Http/Controllers/SuratKeputusanController.php) sudah ada:
  `draftToken`,
  `tempAttachments`,
  `uploadTempAttachment()`,
  `deleteTempAttachment()`,
  `persistTempAttachmentsToKeputusan()`.
- Di [web.php](c:/laragon/www/surat_siega/routes/web.php) juga sudah ada route temp upload dan temp delete.
- Di [attachments_section.blade.php](c:/laragon/www/surat_siega/resources/views/surat_keputusan/partials/attachments_section.blade.php), create mode memakai upload AJAX ke route temp.

Analisis kemungkinan masalah:
- Dari source saat ini, bagian UI lampiran sementara seharusnya bisa tampil jika `draftToken` tersedia, dan controller `create()` memang sudah mengirim `draftToken`.
- Masalah yang lebih kuat justru ada di tahap setelah upload sementara, yaitu saat SK disimpan.
- Jika proses store SK gagal, maka lampiran sementara tidak akan pernah dipindahkan ke lampiran permanen melalui `persistTempAttachmentsToKeputusan()`.

Hubungan langsung dengan kasus error sanitize:
- Karena ada error fatal lain saat input `Surat Keputusan` pada proses save, user bisa merasakan bahwa "lampiran belum bisa ditambahkan", padahal kemungkinan yang gagal adalah:
  upload sementara berhasil atau sebagian berhasil,
  tetapi penyimpanan SK crash,
  sehingga lampiran tidak pernah menempel ke record final.

Kesimpulan analisis:
- Dari code saat ini, masalah lampiran kemungkinan besar terkait erat dengan gagalnya proses simpan SK, bukan semata-mata karena UI upload temp tidak ada.
- Dengan kata lain, bug lampiran dan bug `sanitize_input(array)` sangat mungkin saling berkaitan dalam pengalaman user.

### 3. Error saat input Surat Keputusan: `sanitize_input(): Argument #1 ($value) must be of type ?string, array given`

URL terkait:
`http://localhost/fikomapp/surat_siega/public/surat_keputusan/create`

Error:
- `sanitize_input(): Argument #1 ($value) must be of type ?string, array given, called in D:\laragon\www\fikomapp\surat_siega\app\Services\SuratKeputusanService.php on line 247`

Analisis akar masalah:
- Error ini sangat konsisten dengan pola lama di service `SuratKeputusan` saat field `penerima_eksternal` dikirim sebagai array.
- Helper [helpers.php](c:/laragon/www/surat_siega/app/helpers.php) mendefinisikan `sanitize_input(?string $value, ...)`, sehingga parameter array memang akan memicu `TypeError`.
- Di versi service yang sekarang, bagian ini sudah diarahkan ke normalisasi khusus:
  [SuratKeputusanService.php](c:/laragon/www/surat_siega/app/Services/SuratKeputusanService.php)
  `sanitizePenerimaEksternal(mixed $penerimaEksternal): array`
- Logic baru ini sudah benar arah desainnya karena:
  string JSON bisa di-decode,
  string list bisa di-split,
  array bisa dibersihkan item per item,
  lalu hasilnya disimpan sebagai array aman.

Makna bug ini terhadap riwayat perubahan:
- Bug yang Anda sebutkan cocok sekali dengan perubahan yang memang sudah terlihat di diff:
  sebelumnya `penerima_eksternal` diperlakukan seperti string tunggal,
  sekarang sudah diubah menjadi array-aware.
- Jadi, perubahan pada [SuratKeputusanService.php](c:/laragon/www/surat_siega/app/Services/SuratKeputusanService.php) bisa dibaca sebagai perbaikan langsung untuk bug ini.

Dampak ke sistem:
- Sebelum perbaikan ini, submit/create SK bisa gagal total saat frontend mengirim `penerima_eksternal` berbentuk array.
- Setelah diarahkan ke `sanitizePenerimaEksternal()`, format input menjadi lebih toleran dan aman.

## Keterkaitan Antar Kasus

- Kasus `Surat Tugas` lebih mengarah ke masalah role mapping dan generic error handling.
- Kasus `Surat Keputusan` lebih mengarah ke dua hal yang saling terhubung:
  crash saat sanitasi `penerima_eksternal`,
  dan akibat lanjutannya, lampiran sementara tidak pernah berhasil dipersist ke record SK final.

## Prioritas Perbaikan yang Disarankan

1. Buka dulu error detail `Surat Tugas` di backend log, karena UI saat ini terlalu cepat menyederhanakan semua exception menjadi pesan umum.
2. Audit seluruh asumsi `peran_id` hardcoded pada modul `Surat Tugas`.
3. Pastikan create `Surat Keputusan` berhasil melewati sanitasi `penerima_eksternal`, karena itu blocker utama untuk persist lampiran.
4. Setelah save SK stabil, uji ulang flow lampiran sementara:
   upload temp,
   delete temp,
   save draft,
   save submit,
   lalu cek apakah lampiran benar-benar berpindah ke `lampiran_sk/{id}`.