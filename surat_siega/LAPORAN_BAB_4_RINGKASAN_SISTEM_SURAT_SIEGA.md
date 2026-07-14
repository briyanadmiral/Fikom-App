# BAB IV  
# HASIL DAN PEMBAHASAN

## 4.1 Deskripsi Pekerjaan

Kegiatan pengembangan sistem pada project ini berfokus pada pembuatan Sistem Informasi Surat FIKOM, yaitu aplikasi web yang digunakan untuk membantu proses administrasi surat menyurat di lingkungan Fakultas Ilmu Komputer. Sistem ini dibuat untuk mengelola dua jenis dokumen utama, yaitu Surat Tugas dan Surat Keputusan. Proses yang dikerjakan tidak hanya sebatas pembuatan halaman input surat, tetapi juga mencakup alur pengajuan, persetujuan, penomoran, penandatanganan, penerbitan, pengarsipan, notifikasi, dan pelaporan.

Pekerjaan diawali dengan memahami kebutuhan administrasi surat di lingkungan fakultas. Pada tahap ini dilakukan identifikasi alur kerja surat, mulai dari pembuatan draft oleh Admin TU, pengajuan surat kepada pejabat penandatangan, proses persetujuan atau penolakan, hingga surat dapat diunduh dalam bentuk PDF dan disimpan sebagai arsip digital. Dari hasil pemahaman tersebut, sistem dirancang agar dapat membedakan hak akses pengguna berdasarkan peran, seperti Admin TU, Dekan atau Wakil Dekan sebagai penandatangan, serta pengguna biasa sebagai penerima surat.

Tahap berikutnya adalah perancangan struktur database. Database dirancang untuk menyimpan data master, data transaksi surat, data penerima, data status, notifikasi, arsip, tanda tangan, kop surat, template surat, dan audit log. Struktur database dibuat agar setiap data surat dapat terhubung dengan pengguna pembuat, pejabat penandatangan, penerima, klasifikasi surat, serta riwayat perubahan status. Perancangan database ini menjadi dasar utama dalam pengembangan fitur Surat Tugas dan Surat Keputusan.

Setelah struktur data disiapkan, pekerjaan dilanjutkan dengan pembangunan modul master data. Modul master data meliputi pengelolaan pengguna, peran, klasifikasi surat, jenis tugas, sub tugas, template surat, kop surat, tanda tangan, serta pengaturan akun. Modul ini diperlukan agar data yang digunakan dalam pembuatan surat dapat dipilih dari sistem dan tidak selalu diketik secara manual. Dengan adanya master data, proses administrasi menjadi lebih konsisten dan mengurangi kesalahan penulisan.

Pekerjaan utama berikutnya adalah pembangunan modul Surat Tugas. Modul ini mencakup fitur pembuatan Surat Tugas, penyimpanan draft, pengajuan surat, pengelolaan penerima internal dan eksternal, preview surat, persetujuan, penolakan, pengunduhan PDF, pembuatan nomor turunan, serta pengarsipan. Dalam modul ini juga dibuat alur status surat, yaitu draft, pending, disetujui, ditolak, dan arsip. Status tersebut digunakan untuk menunjukkan posisi surat dalam proses administrasi.

Selain Surat Tugas, dilakukan juga pembangunan modul Surat Keputusan. Modul ini digunakan untuk membuat dokumen keputusan yang memiliki struktur isi seperti tentang, menimbang, mengingat, menetapkan, memutuskan, tembusan, dan penandatangan. Berbeda dari Surat Tugas yang memiliki penerima individual, Surat Keputusan berlaku untuk seluruh anggota fakultas setelah diterbitkan. Modul Surat Keputusan memiliki alur yang hampir sama dengan Surat Tugas, tetapi memiliki tambahan proses penerbitan. Status yang digunakan pada Surat Keputusan meliputi draft, pending, disetujui, ditolak, terbit, dan arsip.

Pekerjaan lain yang dilakukan adalah pembangunan sistem penomoran surat otomatis. Penomoran surat dibuat menggunakan counter database sehingga nomor surat dapat dibuat secara berurutan berdasarkan kode klasifikasi, unit, bulan romawi, dan tahun. Sistem ini dibuat untuk mengurangi risiko nomor surat ganda serta membantu Admin TU dalam menghasilkan nomor surat yang konsisten.

Pada bagian output dokumen, sistem dikembangkan agar dapat menghasilkan file PDF untuk Surat Tugas, Surat Keputusan, dan laporan. PDF dibuat dengan menampilkan kop surat, isi surat, tanda tangan, serta cap sesuai status dokumen. Tanda tangan dan cap hanya ditampilkan ketika surat sudah memenuhi status tertentu, misalnya telah disetujui atau diterbitkan. Hal ini dilakukan agar dokumen yang masih draft tidak terlihat seperti dokumen resmi.

Selain modul surat, pekerjaan juga mencakup pembuatan fitur notifikasi, email, arsip, laporan, ekspor data, dan audit log. Notifikasi digunakan untuk memberi tahu pengguna ketika ada surat yang diajukan, disetujui, ditolak, atau perlu diperiksa. Fitur laporan digunakan untuk melihat jumlah Surat Tugas dan Surat Keputusan berdasarkan tahun, bulan, serta status. Audit log digunakan untuk mencatat aktivitas penting pengguna sehingga perubahan data dapat ditelusuri kembali.

Tahap akhir pekerjaan adalah pengecekan alur sistem dan dokumentasi hasil pengembangan. Pengecekan dilakukan dengan melihat apakah modul yang dibuat sudah saling terhubung, apakah alur status surat berjalan sesuai kebutuhan, apakah dokumen dapat dihasilkan dalam bentuk PDF, serta apakah data tersimpan pada database yang sesuai. Dokumentasi kemudian disusun untuk menjelaskan sistem yang telah dibuat, cara pengguna menggunakan sistem, dan output yang dihasilkan oleh aplikasi.

## 4.2 Hasil Pekerjaan

Hasil pekerjaan berupa aplikasi web Sistem Informasi Surat FIKOM yang dapat digunakan untuk mengelola proses surat menyurat secara digital. Sistem ini dibangun menggunakan Laravel sebagai framework backend, Blade sebagai tampilan utama, AdminLTE dan Bootstrap untuk antarmuka, Vite dan Vue sebagai pendukung frontend, serta MySQL sebagai basis data. Sistem juga menggunakan DomPDF untuk membuat dokumen PDF, Laravel Mail untuk pengiriman email, dan service khusus untuk penomoran surat, notifikasi, audit log, serta pengolahan dokumen.

### Database Sistem

Database yang digunakan pada sistem bernama `surat_fikom`. Database ini menyimpan data master, data transaksi surat, data penerima, data status surat, data notifikasi, konfigurasi kop surat, tanda tangan, arsip, dan audit log. Berdasarkan struktur database project, terdapat beberapa tabel penting yang membentuk sistem surat menyurat.

Tabel `pengguna` digunakan untuk menyimpan data pengguna sistem. Data pengguna ini dipakai sebagai pembuat surat, penerima surat, pejabat penandatangan, dan aktor yang melakukan aktivitas pada sistem. Tabel `peran` digunakan untuk membedakan hak akses pengguna, seperti Admin TU, Dekan, Wakil Dekan, dan pengguna biasa.

Tabel `tugas_header` digunakan untuk menyimpan data utama Surat Tugas. Di dalam tabel ini tersimpan informasi seperti nomor surat, tahun, bulan, semester, jenis tugas, detail tugas, waktu kegiatan, tempat, status surat, pembuat, penandatangan, tanggal pengajuan, tanggal persetujuan, tanda tangan, cap, dan informasi arsip. Tabel ini menjadi pusat data untuk modul Surat Tugas.

Tabel `tugas_penerima` digunakan untuk menyimpan daftar penerima Surat Tugas. Penerima dapat berasal dari pengguna internal maupun penerima eksternal. Dengan adanya tabel ini, satu Surat Tugas dapat memiliki lebih dari satu penerima. Tabel `tugas_log` dan `tugas_logs` digunakan untuk menyimpan riwayat perubahan status Surat Tugas, seperti perubahan dari draft menjadi pending, pending menjadi disetujui, atau pending menjadi ditolak.

Tabel `keputusan_header` digunakan untuk menyimpan data utama Surat Keputusan. Data yang tersimpan meliputi nomor, tanggal surat, tahun, kota penetapan, tentang, judul penetapan, bagian menimbang, mengingat, menetapkan, memutuskan, tembusan, status surat, pembuat, penandatangan, penerbit, pengarsip, tanda tangan, cap, serta tanggal arsip. Tabel ini menjadi pusat data untuk modul Surat Keputusan.

Tabel `keputusan_attachments` digunakan untuk menyimpan lampiran SK, seperti proposal, RAB, surat pengantar, dokumentasi, atau file lain. Tabel `keputusan_status_logs` menyimpan riwayat perubahan status Surat Keputusan. Pada desain terkini, Surat Keputusan tidak lagi menggunakan tabel penerima individual karena SK yang sudah diterbitkan berlaku untuk seluruh anggota fakultas.

Tabel `klasifikasi_surat` digunakan untuk menyimpan kode klasifikasi dan deskripsi surat. Data ini digunakan dalam penomoran surat agar setiap surat memiliki kode yang sesuai. Tabel `jenis_tugas` dan `sub_tugas` digunakan sebagai master kategori pada Surat Tugas. Tabel `surat_templates` digunakan untuk menyimpan template surat yang dapat dipakai kembali saat membuat dokumen baru.

Tabel `master_kop_surat` digunakan untuk menyimpan konfigurasi kop surat, seperti nama fakultas, alamat, telepon, email, website, logo, background, cap, ukuran cap, dan pengaturan tampilan lainnya. Tabel `user_signatures` digunakan untuk menyimpan tanda tangan pengguna. Kedua tabel ini berperan dalam pembentukan dokumen PDF resmi.

Tabel `notifikasi` dan `notification_preferences` digunakan untuk mengatur notifikasi sistem. Tabel `audit_logs` digunakan untuk menyimpan catatan aktivitas pengguna, seperti pembuatan, perubahan, penghapusan, pengajuan, persetujuan, penolakan, penerbitan, dan pengarsipan surat. Tabel `nomor_surat_counters` digunakan untuk menyimpan counter nomor surat agar nomor yang dihasilkan tidak duplikat.

### Modul Login dan Integrasi Akses

Sistem memiliki fitur login dan integrasi akses dengan dashboard utama FIKOM. Route login pada aplikasi diarahkan ke dashboard utama, sedangkan route `/entry` digunakan sebagai pintu masuk dari sistem eksternal. Dengan alur tersebut, pengguna dapat masuk ke aplikasi surat melalui dashboard FIKOM dan sistem akan memeriksa sesi serta peran pengguna.

Hasil dari modul ini adalah proses akses yang lebih terpusat. Pengguna tidak hanya masuk ke aplikasi surat secara terpisah, tetapi dapat diarahkan dari sistem utama sesuai hak aksesnya.

### Modul Dashboard

Dashboard merupakan halaman awal setelah pengguna masuk ke sistem. Halaman ini menampilkan ringkasan informasi surat, antrean pekerjaan, status surat, dan notifikasi. Dashboard membantu pengguna melihat kondisi surat secara cepat, misalnya jumlah surat yang masih draft, pending, disetujui, ditolak, atau masuk arsip.

Bagi Admin TU, dashboard membantu memantau pekerjaan administrasi surat. Bagi pejabat penandatangan, dashboard membantu melihat surat yang membutuhkan persetujuan. Bagi pengguna penerima, dashboard membantu melihat informasi surat yang berkaitan dengan dirinya.

### Modul Manajemen Pengguna dan Peran

Modul manajemen pengguna digunakan untuk mengelola akun pengguna yang dapat mengakses sistem. Admin dapat menambahkan pengguna, mengubah data pengguna, menghapus pengguna, mengatur peran, serta memperbarui data akun. Data pengguna yang dikelola meliputi nama lengkap, email, jabatan, peran, status, foto, dan informasi pendukung lainnya.

Hasil dari modul ini adalah data pengguna yang dapat digunakan pada proses pembuatan surat. Pengguna dapat dipilih sebagai pembuat surat, penerima surat, atau penandatangan sesuai perannya.

### Modul Klasifikasi Surat

Modul klasifikasi surat digunakan untuk mengelola kode dan deskripsi klasifikasi surat. Klasifikasi ini digunakan pada proses pembuatan nomor surat. Admin dapat menambahkan, mengubah, dan menghapus klasifikasi surat. Sistem juga menyediakan fitur AJAX untuk mengambil kode berikutnya dan golongan klasifikasi yang tersedia.

Hasil dari modul ini adalah daftar klasifikasi surat yang dapat digunakan secara konsisten pada Surat Tugas dan Surat Keputusan.

### Modul Jenis Surat Tugas dan Sub Tugas

Modul jenis surat tugas dan sub tugas digunakan untuk mengelola kategori penugasan. Admin dapat membuat jenis tugas, kemudian menambahkan sub tugas pada jenis tersebut. Data ini kemudian digunakan pada form pembuatan Surat Tugas.

Hasil dari modul ini adalah proses input Surat Tugas menjadi lebih mudah karena pengguna dapat memilih jenis tugas dan sub tugas yang sudah tersedia. Selain itu, istilah yang digunakan dalam surat menjadi lebih seragam.

### Modul Surat Tugas

Modul Surat Tugas merupakan salah satu modul utama yang dibuat. Modul ini digunakan untuk membuat, menyimpan, mengajukan, menyetujui, menolak, mengunduh, dan mengarsipkan Surat Tugas. Admin TU dapat membuat surat baru dengan mengisi klasifikasi surat, tanggal surat, tahun, bulan, semester, jenis tugas, detail tugas, waktu, tempat, penerima, pembuat, asal surat, dan penandatangan.

Pada saat pembuatan surat, pengguna dapat menyimpan data sebagai draft atau langsung mengajukan surat. Jika surat diajukan, status berubah menjadi pending dan sistem mengirim notifikasi kepada pejabat penandatangan. Pejabat kemudian dapat membuka daftar persetujuan, melihat preview surat, lalu menyetujui atau menolak surat. Jika disetujui, surat dapat menampilkan tanda tangan dan cap. Jika ditolak, pembuat surat dapat memperbaiki data sesuai alasan penolakan.

Modul Surat Tugas juga menyediakan fitur PDF. Setelah surat disetujui, pengguna yang memiliki akses dapat mengunduh dokumen Surat Tugas dalam bentuk PDF. Dokumen tersebut berisi kop surat, nomor surat, isi penugasan, penerima, waktu, tempat, tembusan, tanda tangan, dan cap.

Hasil dari modul ini adalah dokumen Surat Tugas digital yang dapat diproses dari tahap draft sampai arsip tanpa harus dikelola sepenuhnya secara manual.

### Modul Nomor Surat Otomatis

Sistem penomoran surat dibuat untuk menghasilkan nomor surat secara otomatis. Format nomor menggunakan susunan nomor urut, kode klasifikasi, unit, identitas UNIKA, bulan romawi, dan tahun. Sistem menyimpan counter pada tabel `nomor_surat_counters` agar nomor surat dapat dibuat berurutan.

Proses penomoran dilakukan menggunakan transaksi database dan penguncian baris agar tidak terjadi nomor ganda ketika beberapa pengguna membuat surat pada waktu yang berdekatan. Pada Surat Tugas, sistem juga mendukung nomor turunan atau suffix dari surat utama.

Hasil dari modul ini adalah nomor surat yang lebih konsisten, rapi, dan aman dari duplikasi.

### Modul Surat Keputusan

Modul Surat Keputusan digunakan untuk membuat dan mengelola dokumen keputusan. Struktur Surat Keputusan berbeda dengan Surat Tugas karena memuat bagian tentang, menimbang, mengingat, menetapkan, memutuskan, dan tembusan. Admin TU dapat membuat SK baru, menyimpannya sebagai draft, atau mengajukannya kepada pejabat penandatangan.

Pejabat penandatangan dapat membuka daftar SK yang menunggu persetujuan. Setelah melihat preview, pejabat dapat menyetujui atau menolak SK. Jika disetujui, sistem dapat menghasilkan nomor, menyimpan data tanda tangan, dan menyiapkan dokumen untuk diterbitkan. SK yang sudah diterbitkan dapat diunduh dan kemudian dimasukkan ke arsip.

Hasil dari modul ini adalah dokumen Surat Keputusan digital yang memiliki alur mulai dari draft, pending, disetujui, terbit, sampai arsip.

### Modul Template Surat

Modul template surat digunakan untuk menyimpan pola atau format isi surat yang dapat digunakan kembali. Admin dapat membuat template baru, mengubah template, menghapus template, dan melihat preview template. Template ini membantu mempercepat pembuatan surat karena pengguna dapat menggunakan isi dasar yang sudah disiapkan.

Hasil dari modul ini adalah redaksi surat yang lebih konsisten dan proses pembuatan dokumen yang lebih cepat.

### Modul Kop Surat, Tanda Tangan, dan Cap

Modul kop surat digunakan untuk mengatur tampilan identitas resmi pada dokumen PDF. Data yang dikelola mencakup nama fakultas, alamat, telepon, email, website, logo, background, serta konfigurasi cap. Modul tanda tangan digunakan untuk menyimpan tanda tangan pengguna yang berwenang.

Pada dokumen PDF, tanda tangan dan cap tidak langsung ditampilkan pada surat yang masih draft. Sistem hanya menampilkan tanda tangan dan cap jika surat sudah berada pada status yang sesuai, misalnya disetujui atau terbit. Dengan demikian, dokumen yang belum final tidak terlihat sebagai dokumen resmi.

Hasil dari modul ini adalah PDF surat yang memiliki tampilan resmi dan sesuai dengan kebutuhan administrasi fakultas.

### Modul Notifikasi dan Email

Modul notifikasi digunakan untuk memberi informasi kepada pengguna mengenai aktivitas surat. Notifikasi dapat muncul ketika surat diajukan, menunggu persetujuan, disetujui, ditolak, atau direvisi. Pengguna dapat membuka daftar notifikasi, menandai notifikasi sebagai dibaca, menandai semua notifikasi sebagai dibaca, dan membersihkan notifikasi lama.

Selain notifikasi di dalam sistem, project juga memiliki fitur email untuk Surat Tugas final dan Surat Keputusan. Email digunakan untuk mengirim informasi atau dokumen kepada pihak terkait.

Hasil dari modul ini adalah proses komunikasi administrasi yang lebih cepat karena pengguna dapat mengetahui perubahan status surat tanpa harus mengecek semua menu secara manual.

### Modul Import Penerima

Modul import penerima digunakan untuk membantu pengguna memasukkan banyak penerima sekaligus. Fitur ini berguna ketika surat ditujukan kepada banyak pihak. Pengguna dapat melakukan import data penerima, melihat preview, kemudian menggunakan data yang valid sebagai penerima surat.

Hasil dari modul ini adalah proses input penerima menjadi lebih efisien dan tidak perlu dilakukan satu per satu.

### Modul Arsip Surat

Modul arsip digunakan untuk menyimpan Surat Tugas dan Surat Keputusan yang telah selesai diproses. Surat yang sudah masuk arsip dapat ditampilkan kembali, difilter, dan diekspor. Pada Surat Tugas juga tersedia fitur buka arsip untuk mengembalikan dokumen dari status arsip jika diperlukan.

Hasil dari modul ini adalah arsip digital yang memudahkan pencarian dokumen lama dan membantu penyimpanan riwayat surat secara lebih teratur.

### Modul Laporan dan Ekspor

Modul laporan digunakan untuk menampilkan rekap data Surat Tugas dan Surat Keputusan. Sistem dapat menghitung total surat berdasarkan tahun, jumlah surat bulan berjalan, jumlah surat pending, dan jumlah surat yang disetujui. Data juga dapat ditampilkan dalam bentuk tren bulanan.

Sistem menyediakan fitur ekspor laporan ke CSV dan PDF. Selain itu, arsip Surat Tugas dan Surat Keputusan juga dapat diekspor ke file CSV yang dapat dibuka melalui aplikasi spreadsheet.

Hasil dari modul ini adalah laporan administrasi surat yang dapat digunakan untuk monitoring, evaluasi, dan kebutuhan rekap data fakultas.

### Modul Audit Log

Modul audit log digunakan untuk mencatat aktivitas penting pengguna pada sistem. Data yang dicatat meliputi pengguna yang melakukan aksi, jenis aksi, tipe entitas, ID entitas, nama entitas, nilai sebelum perubahan, nilai sesudah perubahan, alamat IP, user agent, dan waktu aktivitas.

Aksi yang dapat dicatat meliputi pembuatan data, perubahan data, penghapusan data, pengajuan surat, persetujuan, penolakan, penerbitan, dan pengarsipan. Hasil dari modul ini adalah riwayat aktivitas yang dapat digunakan untuk monitoring dan penelusuran apabila terjadi kesalahan data.

### Output Sistem

Output utama yang dihasilkan dari sistem ini adalah dokumen Surat Tugas PDF, dokumen Surat Keputusan PDF, daftar surat berdasarkan status, notifikasi sistem, email pemberitahuan, arsip digital, laporan rekap surat, file ekspor CSV, file laporan PDF, dan audit log aktivitas pengguna.

Dengan adanya output tersebut, sistem tidak hanya berfungsi sebagai tempat input data surat, tetapi juga menjadi media pengelolaan administrasi surat dari awal sampai akhir. Sistem membantu proses pembuatan surat menjadi lebih cepat, alur persetujuan menjadi lebih jelas, penomoran surat menjadi lebih konsisten, dan penyimpanan dokumen menjadi lebih tertata.
