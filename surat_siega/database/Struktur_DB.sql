-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 23, 2026 at 03:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `surat_fikom`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'User yang melakukan aksi',
  `user_name` varchar(100) DEFAULT NULL COMMENT 'Nama user (snapshot)',
  `action` varchar(50) NOT NULL COMMENT 'Jenis aksi: create, update, delete, approve, reject, submit, publish, archive',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'Tipe entitas: TugasHeader, KeputusanHeader, dll',
  `entity_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID entitas',
  `entity_name` varchar(255) DEFAULT NULL COMMENT 'Nama/nomor entitas untuk display',
  `old_values` json DEFAULT NULL COMMENT 'Nilai sebelum perubahan',
  `new_values` json DEFAULT NULL COMMENT 'Nilai setelah perubahan',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address user',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Browser user agent',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log aktivitas user untuk audit trail';

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_tugas`
--

CREATE TABLE `jenis_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_attachments`
--

CREATE TABLE `keputusan_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file original dari user',
  `nama_file_sistem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file hasil rename sistem',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path storage file',
  `file_size` int UNSIGNED NOT NULL COMMENT 'Ukuran file dalam bytes',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe MIME file',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ekstensi file',
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi/keterangan file',
  `kategori` enum('proposal','rab','surat_pengantar','dokumentasi','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya' COMMENT 'Kategori dokumen',
  `download_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah download',
  `last_downloaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_header`
--

CREATE TABLE `keputusan_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(100) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `tahun` int DEFAULT NULL COMMENT 'Tahun surat untuk filtering',
  `kota_penetapan` varchar(100) DEFAULT 'Semarang' COMMENT 'Kota penetapan surat',
  `signed_at` timestamp NULL DEFAULT NULL,
  `tentang` varchar(255) NOT NULL,
  `judul_penetapan` varchar(500) DEFAULT NULL COMMENT 'Judul keputusan (contoh: KEPUTUSAN DEKAN TENTANG...)',
  `menimbang` json NOT NULL DEFAULT (json_array()),
  `mengingat` json NOT NULL DEFAULT (json_array()),
  `menetapkan` json DEFAULT NULL,
  `memutuskan` longtext NOT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `tembusan` text,
  `tembusan_formatted` text,
  `penerima_eksternal` json DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui','ditolak','terbit','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `penandatangan` bigint UNSIGNED DEFAULT NULL,
  `npp_penandatangan` varchar(50) DEFAULT NULL COMMENT 'NPP/NIP penandatangan (opsional)',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `tanggal_terbit` timestamp NULL DEFAULT NULL,
  `terbitkan_oleh` bigint UNSIGNED DEFAULT NULL,
  `tanggal_arsip` timestamp NULL DEFAULT NULL,
  `arsipkan_oleh` bigint UNSIGNED DEFAULT NULL,
  `rejected_by` bigint UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `published_by` bigint UNSIGNED DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `ttd_config` json DEFAULT NULL,
  `cap_config` json DEFAULT NULL,
  `ttd_w_mm` smallint UNSIGNED DEFAULT NULL,
  `cap_w_mm` smallint UNSIGNED DEFAULT NULL,
  `cap_opacity` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_penerima`
--

CREATE TABLE `keputusan_penerima` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_status_logs`
--

CREATE TABLE `keputusan_status_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `status_dari` enum('draft','pending','disetujui','ditolak','terbit','arsip') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_ke` enum('draft','pending','disetujui','ditolak','terbit','arsip') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `diubah_oleh` bigint UNSIGNED NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_surat`
--

CREATE TABLE `klasifikasi_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `kode` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_kop_surat`
--

CREATE TABLE `master_kop_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `unit_code` varchar(50) DEFAULT NULL,
  `nama_kop` varchar(100) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `background_path` varchar(255) DEFAULT NULL,
  `cap_path` varchar(255) DEFAULT NULL,
  `cap_default_width_mm` smallint UNSIGNED NOT NULL DEFAULT '30',
  `cap_opacity` tinyint UNSIGNED NOT NULL DEFAULT '85',
  `cap_offset_x_mm` int NOT NULL DEFAULT '0',
  `cap_offset_y_mm` int NOT NULL DEFAULT '0',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mode` varchar(255) NOT NULL DEFAULT 'image',
  `mode_type` enum('custom','upload') DEFAULT 'custom',
  `nama_fakultas` varchar(255) DEFAULT 'FAKULTAS ILMU KOMPUTER',
  `alamat_lengkap` varchar(500) DEFAULT 'Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234',
  `telepon_lengkap` varchar(255) DEFAULT 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265',
  `email_website` varchar(255) DEFAULT 'e-mail: unika@unika.ac.id http://www.unika.ac.id/',
  `text_align` enum('left','right','center') DEFAULT 'right',
  `logo_size` int DEFAULT '100',
  `font_size_title` int DEFAULT '14',
  `font_size_text` int DEFAULT '10',
  `text_color` varchar(7) DEFAULT '#000000',
  `header_padding` int DEFAULT '15',
  `background_opacity` int DEFAULT '100',
  `alamat` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_kiri_path` varchar(255) DEFAULT NULL,
  `tampilkan_logo_kiri` tinyint(1) NOT NULL DEFAULT '0',
  `logo_kanan_path` varchar(255) DEFAULT NULL,
  `tampilkan_logo_kanan` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mengingat_library`
--

CREATE TABLE `mengingat_library` (
  `id` bigint UNSIGNED NOT NULL,
  `judul` varchar(200) NOT NULL COMMENT 'Judul dasar hukum',
  `isi` text NOT NULL COMMENT 'Isi lengkap referensi',
  `kategori` varchar(50) DEFAULT NULL COMMENT 'Kategori: UU, PP, Permen, SK Rektor, dll',
  `nomor_referensi` varchar(100) DEFAULT NULL COMMENT 'Nomor UU/PP/SK',
  `tanggal_referensi` date DEFAULT NULL COMMENT 'Tanggal penetapan',
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `usage_count` int DEFAULT '0' COMMENT 'Jumlah penggunaan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Library dasar hukum untuk SK';

-- --------------------------------------------------------

--
-- Table structure for table `menimbang_library`
--

CREATE TABLE `menimbang_library` (
  `id` bigint UNSIGNED NOT NULL,
  `judul` varchar(200) NOT NULL COMMENT 'Judul singkat poin menimbang',
  `isi` text NOT NULL COMMENT 'Isi lengkap poin menimbang',
  `kategori` varchar(50) DEFAULT NULL COMMENT 'Kategori: akademik, kepegawaian, keuangan, dll',
  `tags` json DEFAULT NULL COMMENT 'Tag pencarian',
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `usage_count` int DEFAULT '0' COMMENT 'Jumlah penggunaan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Library poin menimbang untuk SK';

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nomor_counters`
--

CREATE TABLE `nomor_counters` (
  `id` bigint UNSIGNED NOT NULL,
  `tipe` enum('ST','SK') NOT NULL,
  `tahun` int NOT NULL,
  `prefix` varchar(50) NOT NULL,
  `last_number` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat_counters`
--

CREATE TABLE `nomor_surat_counters` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_surat` varchar(50) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `bulan_romawi` varchar(10) NOT NULL,
  `tahun` int NOT NULL,
  `last_number` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `email_on_approval_needed` tinyint(1) DEFAULT '1' COMMENT 'Notifikasi email saat ada dokumen perlu disetujui',
  `email_on_approved` tinyint(1) DEFAULT '1' COMMENT 'Notifikasi email saat dokumen disetujui',
  `email_on_rejected` tinyint(1) DEFAULT '1' COMMENT 'Notifikasi email saat dokumen ditolak',
  `email_digest_weekly` tinyint(1) DEFAULT '0' COMMENT 'Kirim digest mingguan dokumen pending',
  `inapp_notifications` tinyint(1) DEFAULT '1' COMMENT 'Notifikasi in-app',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Preferensi notifikasi per user';

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `tipe` varchar(255) NOT NULL,
  `referensi_id` int NOT NULL,
  `pesan` varchar(255) NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `sandi_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `npp` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `peran_id` bigint UNSIGNED NOT NULL,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL COMMENT 'Path foto profile user di storage'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peran`
--

CREATE TABLE `peran` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipient_imports`
--

CREATE TABLE `recipient_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `total_rows` int DEFAULT '0',
  `success_count` int DEFAULT '0',
  `error_count` int DEFAULT '0',
  `errors` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tracking bulk recipient import jobs';

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_tugas`
--

CREATE TABLE `sub_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `jenis_tugas_id` bigint UNSIGNED NOT NULL,
  `klasifikasi_surat_id` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surat_templates`
--

CREATE TABLE `surat_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL COMMENT 'Nama template',
  `deskripsi` varchar(500) DEFAULT NULL COMMENT 'Deskripsi singkat template',
  `jenis_tugas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Kategori jenis tugas (opsional)',
  `sub_tugas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'FK ke sub_tugas',
  `detail_tugas` text NOT NULL COMMENT 'Isi template dengan placeholder',
  `tembusan` text COMMENT 'Tembusan default',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status aktif template',
  `dibuat_oleh` bigint UNSIGNED NOT NULL COMMENT 'User yang membuat template',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Template surat tugas untuk reuse';

-- --------------------------------------------------------

--
-- Table structure for table `tugas_attachments`
--

CREATE TABLE `tugas_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL COMMENT 'FK ke tugas_header',
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file original dari user',
  `nama_file_sistem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file hasil rename sistem',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path storage file',
  `file_size` int UNSIGNED NOT NULL COMMENT 'Ukuran file dalam bytes',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe MIME file',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ekstensi file',
  `uploaded_by` bigint UNSIGNED NOT NULL COMMENT 'User yang upload',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi/keterangan file',
  `kategori` enum('proposal','rab','surat_pengantar','dokumentasi','tor','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya' COMMENT 'Kategori dokumen',
  `download_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah download',
  `last_downloaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lampiran dokumen pendukung surat tugas';

-- --------------------------------------------------------

--
-- Table structure for table `tugas_header`
--

CREATE TABLE `tugas_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(255) NOT NULL,
  `suffix` char(1) DEFAULT NULL COMMENT 'Suffix letter untuk nomor turunan (A-Z)',
  `parent_tugas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'FK ke tugas_header induk untuk nomor turunan',
  `nomor_urut_int` smallint UNSIGNED DEFAULT NULL COMMENT 'Nomor urut integer untuk sorting yang benar',
  `tanggal_asli` datetime DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui','ditolak','arsip') NOT NULL DEFAULT 'draft',
  `alasan_penolakan` text,
  `nomor_surat` varchar(255) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dikunci_pada` timestamp NULL DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `nomor_status` enum('reserved','locked') NOT NULL DEFAULT 'reserved',
  `no_bin` varchar(255) DEFAULT NULL,
  `tahun` int DEFAULT NULL,
  `semester` varchar(255) DEFAULT NULL,
  `no_surat_manual` varchar(255) DEFAULT NULL,
  `nama_umum` varchar(255) DEFAULT NULL,
  `asal_surat` bigint UNSIGNED NOT NULL,
  `status_penerima` enum('dosen','tendik','mahasiswa') DEFAULT NULL,
  `jenis_tugas` varchar(255) DEFAULT NULL,
  `tugas` varchar(255) NOT NULL,
  `detail_tugas` text,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `redaksi_pembuka` text,
  `penutup` varchar(255) DEFAULT NULL,
  `tembusan` text,
  `penandatangan` bigint UNSIGNED DEFAULT NULL,
  `ttd_config` json DEFAULT NULL,
  `cap_config` json DEFAULT NULL,
  `ttd_w_mm` smallint UNSIGNED DEFAULT NULL COMMENT 'Lebar TTD dalam mm',
  `cap_w_mm` smallint UNSIGNED DEFAULT NULL COMMENT 'Lebar Cap dalam mm',
  `cap_opacity` decimal(3,2) DEFAULT NULL COMMENT 'Opacity Cap (0.00 - 1.00)',
  `next_approver` bigint UNSIGNED DEFAULT NULL,
  `send_email_on_approve` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Apakah kirim email ke penerima setelah surat disetujui',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kode_surat` varchar(255) DEFAULT NULL,
  `bulan` varchar(255) DEFAULT NULL,
  `klasifikasi_surat_id` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tanggal_arsip` timestamp NULL DEFAULT NULL,
  `arsipkan_oleh` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_log`
--

CREATE TABLE `tugas_log` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL,
  `status_lama` varchar(255) DEFAULT NULL,
  `status_baru` varchar(255) DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_logs`
--

CREATE TABLE `tugas_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL COMMENT 'FK ke tugas_header',
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'FK ke pengguna (user yang melakukan action)',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action: created, updated, submitted, approved, rejected, deleted',
  `old_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status sebelum perubahan',
  `new_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status setelah perubahan',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Catatan tambahan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_penerima`
--

CREATE TABLE `tugas_penerima` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED DEFAULT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `jabatan_penerima` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `penerima_key` varchar(300) DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Triggers `tugas_penerima`
--
DELIMITER $$
CREATE TRIGGER `trg_tugas_penerima_bi` BEFORE INSERT ON `tugas_penerima` FOR EACH ROW BEGIN
  -- Normalisasi internal/eksternal
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.nama_penerima = '';  -- internal tak perlu nama manual
  ELSE
    IF NEW.nama_penerima IS NULL OR TRIM(NEW.nama_penerima) = '' THEN
      SET NEW.nama_penerima = 'TANPA NAMA';
    END IF;
  END IF;

  -- Bangun penerima_key
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.penerima_key = CONCAT('I#', NEW.pengguna_id);
  ELSE
    SET NEW.penerima_key = CONCAT('E#', LOWER(TRIM(NEW.nama_penerima)));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_tugas_penerima_bu` BEFORE UPDATE ON `tugas_penerima` FOR EACH ROW BEGIN
  -- Normalisasi internal/eksternal
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.nama_penerima = '';
  ELSE
    IF NEW.nama_penerima IS NULL OR TRIM(NEW.nama_penerima) = '' THEN
      SET NEW.nama_penerima = 'TANPA NAMA';
    END IF;
  END IF;

  -- Bangun penerima_key
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.penerima_key = CONCAT('I#', NEW.pengguna_id);
  ELSE
    SET NEW.penerima_key = CONCAT('E#', LOWER(TRIM(NEW.nama_penerima)));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_signatures`
--

CREATE TABLE `user_signatures` (
  `id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `ttd_path` varchar(255) NOT NULL,
  `default_width_mm` smallint UNSIGNED NOT NULL DEFAULT '35',
  `default_height_mm` smallint UNSIGNED NOT NULL DEFAULT '15',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_entity_type` (`entity_type`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`),
  ADD KEY `idx_nama` (`nama`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`),
  ADD KEY `idx_jobs__queue_time` (`queue`,`reserved_at`,`available_at`),
  ADD KEY `idx_jobs__created` (`created_at`);

--
-- Indexes for table `keputusan_attachments`
--
ALTER TABLE `keputusan_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attachment_keputusan` (`keputusan_id`),
  ADD KEY `idx_attachment_uploader` (`uploaded_by`),
  ADD KEY `idx_attachment_kategori` (`kategori`),
  ADD KEY `idx_attachment_created` (`created_at`);

--
-- Indexes for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keputusan_header_nomor_unique` (`nomor`),
  ADD KEY `keputusan_header_dibuat_oleh_foreign` (`dibuat_oleh`),
  ADD KEY `keputusan_header_penandatangan_foreign` (`penandatangan`),
  ADD KEY `idx_keputusan_status` (`status_surat`),
  ADD KEY `idx_keputusan_approved_at` (`approved_at`),
  ADD KEY `idx_keputusan_published_at` (`published_at`),
  ADD KEY `idx_keph_approved_by` (`approved_by`),
  ADD KEY `idx_keph_rejected_by` (`rejected_by`),
  ADD KEY `idx_keph_published_by` (`published_by`),
  ADD KEY `idx_keph_tanggal` (`tanggal_surat`),
  ADD KEY `idx_keph__status_tgl` (`status_surat`),
  ADD KEY `idx_keph__penandatangan` (`penandatangan`),
  ADD KEY `idx_keph__nomor` (`nomor`),
  ADD KEY `idx_tahun` (`tahun`),
  ADD KEY `idx_status_tahun` (`status_surat`,`tahun`),
  ADD KEY `idx_keph_search_tentang` (`tentang`),
  ADD KEY `idx_keph_search_created_status` (`created_at`,`status_surat`),
  ADD KEY `idx_keph_filter_combo` (`tahun`,`status_surat`,`created_at`),
  ADD KEY `fk_keputusan_terbitkan_oleh` (`terbitkan_oleh`),
  ADD KEY `fk_keputusan_arsipkan_oleh` (`arsipkan_oleh`);
ALTER TABLE `keputusan_header` ADD FULLTEXT KEY `idx_keph_fulltext_search` (`tentang`,`nomor`);

--
-- Indexes for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_kepp_unique` (`keputusan_id`,`pengguna_id`,`deleted_at`),
  ADD KEY `keputusan_penerima_keputusan_id_foreign` (`keputusan_id`),
  ADD KEY `keputusan_penerima_pengguna_id_foreign` (`pengguna_id`),
  ADD KEY `idx_keputusan_id` (`keputusan_id`),
  ADD KEY `idx_pengguna_id` (`pengguna_id`),
  ADD KEY `idx_kepp__keputusan` (`keputusan_id`),
  ADD KEY `idx_kepp__pengguna` (`pengguna_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `keputusan_status_logs`
--
ALTER TABLE `keputusan_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_keputusan_id` (`keputusan_id`),
  ADD KEY `idx_diubah_oleh` (`diubah_oleh`);

--
-- Indexes for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `klasifikasi_surat_kode_unique` (`kode`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_kop_unit` (`unit_code`);

--
-- Indexes for table `mengingat_library`
--
ALTER TABLE `mengingat_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_nomor` (`nomor_referensi`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_creator` (`dibuat_oleh`),
  ADD KEY `idx_deleted` (`deleted_at`);
ALTER TABLE `mengingat_library` ADD FULLTEXT KEY `ft_search` (`judul`,`isi`,`nomor_referensi`);

--
-- Indexes for table `menimbang_library`
--
ALTER TABLE `menimbang_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_creator` (`dibuat_oleh`),
  ADD KEY `idx_deleted` (`deleted_at`);
ALTER TABLE `menimbang_library` ADD FULLTEXT KEY `ft_search` (`judul`,`isi`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nomor_counters`
--
ALTER TABLE `nomor_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tipe_tahun_prefix` (`tipe`,`tahun`,`prefix`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_counter_scope` (`kode_surat`,`unit`,`bulan_romawi`,`tahun`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_user` (`pengguna_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasi_pengguna_id_foreign` (`pengguna_id`),
  ADD KEY `idx_notif_dibaca` (`dibaca`),
  ADD KEY `idx_notif_tipe_ref` (`tipe`,`referensi_id`),
  ADD KEY `idx_notif_user_read_created` (`pengguna_id`,`dibaca`,`created_at`),
  ADD KEY `idx_notif__user_baca_tipe_waktu` (`pengguna_id`,`dibaca`,`tipe`,`dibuat_pada`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengguna_email_unique` (`email`),
  ADD UNIQUE KEY `pengguna_npp_unique` (`npp`),
  ADD KEY `pengguna_peran_id_foreign` (`peran_id`);

--
-- Indexes for table `peran`
--
ALTER TABLE `peran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `peran_nama_unique` (`nama`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `recipient_imports`
--
ALTER TABLE `recipient_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`),
  ADD KEY `idx_sessions__user` (`user_id`);

--
-- Indexes for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_tugas_jenis_tugas_id_foreign` (`jenis_tugas_id`),
  ADD KEY `sub_tugas_klasifikasi_surat_id_foreign` (`klasifikasi_surat_id`);

--
-- Indexes for table `surat_templates`
--
ALTER TABLE `surat_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_jenis` (`jenis_tugas_id`),
  ADD KEY `idx_template_creator` (`dibuat_oleh`),
  ADD KEY `idx_template_active` (`is_active`),
  ADD KEY `idx_template_nama` (`nama`),
  ADD KEY `idx_template_deleted` (`deleted_at`),
  ADD KEY `surat_templates_sub_tugas_id_foreign` (`sub_tugas_id`);

--
-- Indexes for table `tugas_attachments`
--
ALTER TABLE `tugas_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tugas_id` (`tugas_id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tugas_header_nomor_unique` (`nomor`),
  ADD UNIQUE KEY `uq_parent_suffix` (`parent_tugas_id`,`suffix`),
  ADD KEY `tugas_header_asal_surat_foreign` (`asal_surat`),
  ADD KEY `tugas_header_klasifikasi_surat_id_foreign` (`klasifikasi_surat_id`),
  ADD KEY `idx_tugas_status` (`status_surat`),
  ADD KEY `idx_tugas_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_tugas_next_approver` (`next_approver`),
  ADD KEY `idx_tugas_penandatangan` (`penandatangan`),
  ADD KEY `idx_tugas_created_status` (`created_at`,`status_surat`),
  ADD KEY `idx_tugas__status_tgl` (`status_surat`,`tanggal_surat`),
  ADD KEY `idx_tugas__waktu` (`waktu_mulai`,`waktu_selesai`),
  ADD KEY `idx_tugas__klasifikasi` (`klasifikasi_surat_id`),
  ADD KEY `idx_tugas__kode_bulan_tahun` (`kode_surat`,`bulan`,`tahun`),
  ADD KEY `idx_tugas_approval_queue` (`next_approver`,`status_surat`,`created_at`),
  ADD KEY `idx_tugas_creator_status` (`dibuat_oleh`,`status_surat`,`created_at`),
  ADD KEY `tugas_header_parent_tugas_id_foreign` (`parent_tugas_id`),
  ADD KEY `idx_nomor_sorting` (`tahun`,`bulan`,`kode_surat`,`nomor_urut_int`,`suffix`),
  ADD KEY `tugas_header_arsipkan_oleh_foreign` (`arsipkan_oleh`);

--
-- Indexes for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_log_tugas_id_foreign` (`tugas_id`),
  ADD KEY `tugas_log_user_id_foreign` (`user_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `tugas_logs`
--
ALTER TABLE `tugas_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_logs_tugas_id_foreign` (`tugas_id`),
  ADD KEY `tugas_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_tugas_penerima_unique_per_surat` (`tugas_id`,`penerima_key`,`deleted_at`),
  ADD KEY `idx_penerima_tugas` (`tugas_id`),
  ADD KEY `idx_penerima_pengguna` (`pengguna_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_signatures_pengguna_id_unique` (`pengguna_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_attachments`
--
ALTER TABLE `keputusan_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_status_logs`
--
ALTER TABLE `keputusan_status_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mengingat_library`
--
ALTER TABLE `mengingat_library`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menimbang_library`
--
ALTER TABLE `menimbang_library`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nomor_counters`
--
ALTER TABLE `nomor_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peran`
--
ALTER TABLE `peran`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipient_imports`
--
ALTER TABLE `recipient_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surat_templates`
--
ALTER TABLE `surat_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_attachments`
--
ALTER TABLE `tugas_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_header`
--
ALTER TABLE `tugas_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_log`
--
ALTER TABLE `tugas_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_logs`
--
ALTER TABLE `tugas_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_signatures`
--
ALTER TABLE `user_signatures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `keputusan_attachments`
--
ALTER TABLE `keputusan_attachments`
  ADD CONSTRAINT `fk_attachment_keputusan` FOREIGN KEY (`keputusan_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attachment_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  ADD CONSTRAINT `fk_keph__approved_by` FOREIGN KEY (`approved_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph__dibuat` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_keph__published_by` FOREIGN KEY (`published_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph__rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph__ttd` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph_published_by` FOREIGN KEY (`published_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keph_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_keputusan_arsipkan_oleh` FOREIGN KEY (`arsipkan_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keputusan_terbitkan_oleh` FOREIGN KEY (`terbitkan_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `keputusan_header_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_header_penandatangan_foreign` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  ADD CONSTRAINT `fk_kepp__header` FOREIGN KEY (`keputusan_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kepp__user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_penerima_keputusan_id_foreign` FOREIGN KEY (`keputusan_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_penerima_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keputusan_status_logs`
--
ALTER TABLE `keputusan_status_logs`
  ADD CONSTRAINT `fk_status_log_keputusan` FOREIGN KEY (`keputusan_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_status_log_user` FOREIGN KEY (`diubah_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mengingat_library`
--
ALTER TABLE `mengingat_library`
  ADD CONSTRAINT `fk_mengingat_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menimbang_library`
--
ALTER TABLE `menimbang_library`
  ADD CONSTRAINT `fk_menimbang_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `fk_notifpref_user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `fk_pengguna__peran` FOREIGN KEY (`peran_id`) REFERENCES `peran` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `pengguna_peran_id_foreign` FOREIGN KEY (`peran_id`) REFERENCES `peran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipient_imports`
--
ALTER TABLE `recipient_imports`
  ADD CONSTRAINT `fk_recipient_imports_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions__user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD CONSTRAINT `fk_sub_tugas__jenis` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sub_tugas_jenis_tugas_id_foreign` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_tugas_klasifikasi_surat_id_foreign` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `surat_templates`
--
ALTER TABLE `surat_templates`
  ADD CONSTRAINT `fk_template_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_template_jenis_tugas` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `surat_templates_sub_tugas_id_foreign` FOREIGN KEY (`sub_tugas_id`) REFERENCES `sub_tugas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tugas_attachments`
--
ALTER TABLE `tugas_attachments`
  ADD CONSTRAINT `fk_tugas_attachments_tugas` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tugas_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD CONSTRAINT `fk_tugas_header__klasifikasi` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_header_arsipkan_oleh_foreign` FOREIGN KEY (`arsipkan_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_header_asal_surat_foreign` FOREIGN KEY (`asal_surat`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_header_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_klasifikasi_surat_id_foreign` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`),
  ADD CONSTRAINT `tugas_header_next_approver_foreign` FOREIGN KEY (`next_approver`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_parent_tugas_id_foreign` FOREIGN KEY (`parent_tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_header_penandatangan_foreign` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD CONSTRAINT `tugas_log_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tugas_logs`
--
ALTER TABLE `tugas_logs`
  ADD CONSTRAINT `tugas_logs_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `tugas_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD CONSTRAINT `tugas_penerima_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_penerima_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD CONSTRAINT `user_signatures_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
