-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 23, 2026 at 02:48 AM
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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `action`, `entity_type`, `entity_id`, `entity_name`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Agustina Alam Anggitasari, Se., Mm', 'publish', 'KeputusanHeader', 3, '002/B.10.1/SK/UNIKA/FIKOM/II/2026', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 01:57:21'),
(2, 1, 'Agustina Alam Anggitasari, Se., Mm', 'approve', 'KeputusanHeader', 5, '', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 01:57:48'),
(3, 1, 'Agustina Alam Anggitasari, Se., Mm', 'publish', 'KeputusanHeader', 5, '', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 01:57:54'),
(4, 1, 'Agustina Alam Anggitasari, Se., Mm', 'archive', 'KeputusanHeader', 5, '', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 01:57:58');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('master_kop_surat_instance', 'O:25:\"App\\Models\\MasterKopSurat\":31:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:16:\"master_kop_surat\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:36:{s:2:\"id\";i:1;s:9:\"unit_code\";N;s:8:\"nama_kop\";s:17:\"Kop Default FIKOM\";s:4:\"unit\";N;s:15:\"background_path\";s:32:\"kop/6947aec6e09d7_1766305478.png\";s:8:\"cap_path\";s:48:\"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\";s:20:\"cap_default_width_mm\";i:30;s:11:\"cap_opacity\";i:85;s:15:\"cap_offset_x_mm\";i:0;s:15:\"cap_offset_y_mm\";i:0;s:10:\"updated_by\";i:1;s:10:\"created_at\";s:19:\"2025-08-26 10:50:41\";s:10:\"updated_at\";s:19:\"2026-01-29 22:31:13\";s:4:\"mode\";s:8:\"composed\";s:9:\"mode_type\";s:6:\"custom\";s:13:\"nama_fakultas\";s:22:\"FAKULTAS ILMU KOMPUTER\";s:14:\"alamat_lengkap\";s:54:\"Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234\";s:15:\"telepon_lengkap\";s:20:\"(024) 8441555 850500\";s:13:\"email_website\";s:35:\"unika@unika.ac.id | www.unika.ac.id\";s:10:\"text_align\";s:5:\"right\";s:9:\"logo_size\";i:155;s:15:\"font_size_title\";i:20;s:14:\"font_size_text\";i:12;s:10:\"text_color\";s:7:\"#333333\";s:14:\"header_padding\";i:80;s:18:\"background_opacity\";i:100;s:6:\"alamat\";s:52:\"Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234\";s:7:\"telepon\";s:23:\"(024) 8441555, 85050003\";s:3:\"fax\";s:25:\"(024) 8415429 – 8454265\";s:5:\"email\";s:17:\"unika@unika.ac.id\";s:7:\"website\";s:23:\"https://www.unika.ac.id\";s:14:\"logo_kiri_path\";N;s:19:\"tampilkan_logo_kiri\";i:0;s:15:\"logo_kanan_path\";s:48:\"kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg\";s:20:\"tampilkan_logo_kanan\";i:1;s:10:\"deleted_at\";N;}s:11:\"\0*\0original\";a:36:{s:2:\"id\";i:1;s:9:\"unit_code\";N;s:8:\"nama_kop\";s:17:\"Kop Default FIKOM\";s:4:\"unit\";N;s:15:\"background_path\";s:32:\"kop/6947aec6e09d7_1766305478.png\";s:8:\"cap_path\";s:48:\"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\";s:20:\"cap_default_width_mm\";i:30;s:11:\"cap_opacity\";i:85;s:15:\"cap_offset_x_mm\";i:0;s:15:\"cap_offset_y_mm\";i:0;s:10:\"updated_by\";i:1;s:10:\"created_at\";s:19:\"2025-08-26 10:50:41\";s:10:\"updated_at\";s:19:\"2026-01-29 22:31:13\";s:4:\"mode\";s:8:\"composed\";s:9:\"mode_type\";s:6:\"custom\";s:13:\"nama_fakultas\";s:22:\"FAKULTAS ILMU KOMPUTER\";s:14:\"alamat_lengkap\";s:54:\"Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234\";s:15:\"telepon_lengkap\";s:20:\"(024) 8441555 850500\";s:13:\"email_website\";s:35:\"unika@unika.ac.id | www.unika.ac.id\";s:10:\"text_align\";s:5:\"right\";s:9:\"logo_size\";i:155;s:15:\"font_size_title\";i:20;s:14:\"font_size_text\";i:12;s:10:\"text_color\";s:7:\"#333333\";s:14:\"header_padding\";i:80;s:18:\"background_opacity\";i:100;s:6:\"alamat\";s:52:\"Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234\";s:7:\"telepon\";s:23:\"(024) 8441555, 85050003\";s:3:\"fax\";s:25:\"(024) 8415429 – 8454265\";s:5:\"email\";s:17:\"unika@unika.ac.id\";s:7:\"website\";s:23:\"https://www.unika.ac.id\";s:14:\"logo_kiri_path\";N;s:19:\"tampilkan_logo_kiri\";i:0;s:15:\"logo_kanan_path\";s:48:\"kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg\";s:20:\"tampilkan_logo_kanan\";i:1;s:10:\"deleted_at\";N;}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:10:{s:20:\"tampilkan_logo_kanan\";s:7:\"boolean\";s:19:\"tampilkan_logo_kiri\";s:7:\"boolean\";s:10:\"created_at\";s:8:\"datetime\";s:10:\"updated_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";s:9:\"logo_size\";s:7:\"integer\";s:15:\"font_size_title\";s:7:\"integer\";s:14:\"font_size_text\";s:7:\"integer\";s:14:\"header_padding\";s:7:\"integer\";s:18:\"background_opacity\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:4:{i:0;s:15:\"logo_kanan_path\";i:1;s:14:\"logo_kiri_path\";i:2;s:8:\"cap_path\";i:3;s:15:\"background_path\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:4:{i:0;s:2:\"id\";i:1;s:10:\"created_at\";i:2;s:10:\"updated_at\";i:3;s:10:\"deleted_at\";}s:16:\"\0*\0forceDeleting\";b:0;}', 1771140986),
('5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1771811804;', 1771811804),
('5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1771811804);

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

--
-- Dumping data for table `jenis_tugas`
--

INSERT INTO `jenis_tugas` (`id`, `nama`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bimbingan', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(2, 'Penelitian', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(3, 'Pengabdian', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(4, 'Penunjang Almamater', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(5, 'Penunjang Administrasi dan Manajemen', '2025-07-31 15:48:26', '2026-02-08 16:35:47', NULL),
(6, 'Publikasi', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(7, 'TA di Luar Mengajar', '2025-07-31 15:48:26', '2025-07-31 15:48:26', NULL),
(8, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05', NULL),
(9, 'Test', '2026-02-23 01:56:14', '2026-02-23 01:56:18', '2026-02-23 01:56:18');

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

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"eab623fe-b007-4a75-9c6a-db89510bce22\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1771137377, 1771137377),
(2, 'mail', '{\"uuid\":\"51ea54d7-8316-4f68-b498-237856fdbabd\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-02-15 13:36:33.919298\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1771137393, 1771137388),
(3, 'mail', '{\"uuid\":\"8fd5bb0e-b22e-4202-83b2-e503f724119c\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-02-15 13:36:33.929072\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1771137393, 1771137388),
(4, 'mail', '{\"uuid\":\"3ede8060-bb7b-4256-8e2d-7cbd758e9974\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-02-15 13:36:33.930613\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1771137393, 1771137388),
(5, 'mail', '{\"uuid\":\"f823b415-7474-412c-a942-6e702c16fa61\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-02-15 13:36:33.932131\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1771137393, 1771137388),
(6, 'default', '{\"uuid\":\"b6e96158-b575-4a57-85fd-67b3c1b55997\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:9;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1771137390, 1771137390);

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

--
-- Dumping data for table `keputusan_header`
--

INSERT INTO `keputusan_header` (`id`, `nomor`, `tanggal_surat`, `tahun`, `kota_penetapan`, `signed_at`, `tentang`, `judul_penetapan`, `menimbang`, `mengingat`, `menetapkan`, `memutuskan`, `signed_pdf_path`, `tembusan`, `tembusan_formatted`, `penerima_eksternal`, `status_surat`, `dibuat_oleh`, `penandatangan`, `npp_penandatangan`, `approved_by`, `approved_at`, `tanggal_terbit`, `terbitkan_oleh`, `tanggal_arsip`, `arsipkan_oleh`, `rejected_by`, `rejected_at`, `published_by`, `published_at`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, '2026-02-13', 2026, 'Semarang', NULL, 'Penetapan Visi, Misi, dan Tujuan Fakultas Ilmu Komputer Periode 2026-2030', NULL, '[\"bahwa Fakultas Ilmu Komputer memerlukan penyesuaian visi, misi, dan tujuan sesuai perkembangan teknologi terkini\", \"bahwa berdasarkan keputusan Rapat Senat Fakultas pada tanggal 10 Januari 2026 diperlukan peninjauan kembali visi dan misi\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\", \"Statuta Universitas Katolik Soegijapranata\", \"Peraturan Menteri Pendidikan Nomor 3 Tahun 2020 tentang SN-Dikti\"]', '[{\"isi\": \"<p>Menetapkan Visi Fakultas Ilmu Komputer: Menjadi fakultas unggulan di bidang ilmu komputer yang menghasilkan lulusan berkarakter dan berdaya saing global.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> <p>Menetapkan Visi Fakultas Ilmu Komputer.</p></p>\n<p><strong>KEDUA:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', NULL, 'Yth. Rektor\nYth. Wakil Rektor I\nArsip', NULL, '[]', 'draft', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 08:00:00', '2026-02-13 08:00:00', NULL),
(2, '001/B.10.1/SK/UNIKA/FIKOM/II/2026', '2026-02-05', 2026, 'Semarang', NULL, 'Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2026', 'KEPUTUSAN DEKAN TENTANG PENGANGKATAN PANITIA SEMINAR NASIONAL TI 2026', '[\"bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2026 diperlukan pembentukan panitia pelaksana\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\", \"Statuta Universitas Katolik Soegijapranata\"]', '[{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional TI 2026 dengan susunan: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional TI 2026.</p></p>\n<p><strong>KEDUA:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', NULL, 'Yth. Dekan Fakultas Ilmu Komputer', NULL, '[]', 'pending', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 08:00:00', '2026-02-07 10:00:00', NULL),
(3, '002/B.10.1/SK/UNIKA/FIKOM/II/2026', '2026-02-01', 2026, 'Semarang', '2026-02-03 09:00:00', 'Penetapan Kurikulum Program Studi Teknik Informatika Tahun 2026', 'KEPUTUSAN DEKAN TENTANG PENETAPAN KURIKULUM PRODI TI 2026', '[\"bahwa kurikulum perlu disesuaikan dengan kebutuhan industri dan perkembangan teknologi\", \"bahwa hasil evaluasi kurikulum lama menunjukkan perlunya pembaruan\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\", \"Peraturan Menteri Pendidikan Nomor 3 Tahun 2020 tentang SN-Dikti\", \"SK Rektor tentang Pedoman Kurikulum\"]', '[{\"isi\": \"<p>Menetapkan Kurikulum Prodi TI 2026 yang berlaku mulai Semester Genap 2025/2026.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Biaya yang ditimbulkan dibebankan pada anggaran fakultas.</p>\", \"judul\": \"KEDUA\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KETIGA\"}]', '<p><strong>KESATU:</strong> Menetapkan Kurikulum Prodi TI 2026.</p>\n<p><strong>KEDUA:</strong> Biaya dibebankan pada anggaran fakultas.</p>\n<p><strong>KETIGA:</strong> Berlaku sejak tanggal ditetapkan.</p>', NULL, 'Yth. Rektor\nYth. Wakil Rektor I\nKaprodi TI\nArsip', NULL, NULL, 'terbit', 1, 10, '058.1.2002.255', 10, '2026-02-03 09:00:00', '2026-02-23 01:57:21', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37, 37, 0.95, '2026-02-01 08:00:00', '2026-02-23 01:57:21', NULL),
(4, NULL, '2026-02-10', 2026, 'Semarang', NULL, 'Penunjukan Dosen Pembimbing Kerja Praktik Semester Genap 2025/2026', NULL, '[\"bahwa pelaksanaan Kerja Praktik memerlukan pembimbing yang kompeten\", \"bahwa perlu ditetapkan dosen pembimbing secara resmi\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\", \"Pedoman Akademik UNIKA tentang Kerja Praktik\"]', '[{\"isi\": \"<p>Menunjuk dosen pembimbing KP Semester Genap 2025/2026 sesuai lampiran.</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> Menunjuk dosen pembimbing KP.</p>', NULL, 'Yth. Kaprodi TI\nYth. Kaprodi SI\nArsip', NULL, '[]', 'ditolak', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, 10, '2026-02-12 09:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-10 08:00:00', '2026-02-12 09:00:00', NULL),
(5, NULL, '2026-01-10', 2026, 'Semarang', '2026-01-15 09:00:00', 'Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026', 'KEPUTUSAN DEKAN TENTANG JADWAL UAS GANJIL 2025/2026', '[\"bahwa pelaksanaan UAS memerlukan jadwal yang terkoordinasi\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\", \"Kalender Akademik UNIKA 2025/2026\"]', '[{\"isi\": \"<p>Menetapkan jadwal UAS Ganjil 2025/2026 berlangsung 20-31 Januari 2026.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> Jadwal UAS 20-31 Januari 2026.</p>\n<p><strong>KEDUA:</strong> Berlaku sejak ditetapkan.</p>', NULL, 'Seluruh Dosen FIKOM\nArsip', NULL, NULL, 'arsip', 1, 10, '058.1.2002.255', 10, '2026-01-15 09:00:00', '2026-02-23 01:57:54', 1, '2026-02-23 01:57:58', 1, NULL, NULL, 1, '2026-01-16 10:00:00', NULL, NULL, 37, 37, 0.95, '2026-01-10 08:00:00', '2026-02-23 01:57:58', NULL),
(6, NULL, '2026-01-05', 2026, 'Semarang', '2026-01-08 09:00:00', 'Penetapan Dosen Wali Akademik Semester Genap 2025/2026', NULL, '[\"bahwa setiap mahasiswa memerlukan pendampingan akademik melalui dosen wali\"]', '[\"Pedoman Akademik UNIKA\", \"Statuta Universitas\"]', '[{\"isi\": \"<p>Menetapkan dosen wali akademik semester genap 2025/2026 sesuai lampiran.</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> Menetapkan dosen wali akademik.</p>', NULL, 'Arsip', NULL, '[]', 'arsip', 1, 10, '058.1.2002.255', 10, '2026-01-08 09:00:00', '2026-01-09 10:00:00', 1, '2026-02-10 10:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 08:00:00', '2026-02-10 10:00:00', NULL);

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

--
-- Dumping data for table `keputusan_penerima`
--

INSERT INTO `keputusan_penerima` (`id`, `keputusan_id`, `pengguna_id`, `read_at`, `created_at`, `updated_at`, `dibaca`, `deleted_at`) VALUES
(1, 2, 7, NULL, '2026-02-05 08:00:00', '2026-02-05 08:00:00', 0, NULL),
(2, 2, 8, NULL, '2026-02-05 08:00:00', '2026-02-05 08:00:00', 0, NULL),
(3, 2, 13, NULL, '2026-02-05 08:00:00', '2026-02-05 08:00:00', 0, NULL),
(4, 3, 11, NULL, '2026-02-01 08:00:00', '2026-02-01 08:00:00', 0, NULL),
(5, 3, 16, NULL, '2026-02-01 08:00:00', '2026-02-01 08:00:00', 0, NULL),
(6, 5, 7, NULL, '2026-01-10 08:00:00', '2026-01-10 08:00:00', 0, NULL),
(7, 5, 8, NULL, '2026-01-10 08:00:00', '2026-01-10 08:00:00', 0, NULL),
(8, 5, 9, NULL, '2026-01-10 08:00:00', '2026-01-10 08:00:00', 0, NULL),
(9, 5, 12, NULL, '2026-01-10 08:00:00', '2026-01-10 08:00:00', 0, NULL),
(10, 6, 11, NULL, '2026-01-05 08:00:00', '2026-01-05 08:00:00', 0, NULL);

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

--
-- Dumping data for table `keputusan_status_logs`
--

INSERT INTO `keputusan_status_logs` (`id`, `keputusan_id`, `status_dari`, `status_ke`, `diubah_oleh`, `catatan`, `created_at`) VALUES
(1, 2, 'draft', 'pending', 1, 'Pengajuan SK Panitia Seminar', '2026-02-07 10:00:00'),
(2, 3, 'draft', 'pending', 1, 'Pengajuan SK Kurikulum', '2026-02-02 10:00:00'),
(3, 3, 'pending', 'disetujui', 10, 'Disetujui Dekan', '2026-02-03 09:00:00'),
(4, 4, 'draft', 'pending', 1, 'Pengajuan SK Dosen Pembimbing KP', '2026-02-11 10:00:00'),
(5, 4, 'pending', 'ditolak', 10, 'Data lampiran dosen pembimbing belum lengkap', '2026-02-12 09:00:00'),
(6, 5, 'draft', 'pending', 1, 'Pengajuan SK Jadwal UAS', '2026-01-12 10:00:00'),
(7, 5, 'pending', 'disetujui', 10, 'Disetujui Dekan', '2026-01-15 09:00:00'),
(8, 5, 'disetujui', 'terbit', 1, 'Penerbitan SK Jadwal UAS', '2026-01-16 10:00:00'),
(9, 6, 'draft', 'pending', 1, 'Pengajuan SK Dosen Wali', '2026-01-06 10:00:00'),
(10, 6, 'pending', 'disetujui', 10, 'Disetujui', '2026-01-08 09:00:00'),
(11, 6, 'disetujui', 'terbit', 1, 'Diterbitkan', '2026-01-09 10:00:00'),
(12, 6, 'terbit', 'arsip', 1, 'Diarsipkan', '2026-02-10 10:00:00'),
(13, 5, 'terbit', 'disetujui', 1, 'Penerbitan SK dibatalkan oleh Agustina Alam Anggitasari, Se., Mm', '2026-02-23 01:57:48'),
(14, 5, 'terbit', 'arsip', 1, 'SK diarsipkan oleh Agustina Alam Anggitasari, Se., Mm', '2026-02-23 01:57:58');

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

--
-- Dumping data for table `klasifikasi_surat`
--

INSERT INTO `klasifikasi_surat` (`id`, `kode`, `deskripsi`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'A.4', 'Program Terpadu Mahasiswa Baru (PTMB)', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(3, 'B.1.1', 'Penawaran Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(4, 'B.1.2', 'Jadwal Kuliah (revisi/pengganti/tambahan)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(5, 'B.1.3', 'Pembatalan Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(6, 'B.1.4', 'Pengisian KRS', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(7, 'B.1.5', 'Kuliah Umum', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(8, 'B.1.6', 'Awal Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(9, 'B.1.7', 'Penugasan Perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(10, 'B.1.8', 'Praktikum/Laboratorium', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(11, 'B.1.9', 'Kuliah Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(12, 'B.1.10', 'Akhir Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(13, 'B.1.11', 'Pekan Teduh', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(14, 'B.1.12', 'Libur Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(15, 'B.1.13', 'Angket evaluasi perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(16, 'B.2.1', 'Ujian Tengah Semester (UTS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(17, 'B.2.2', 'Ujian Akhir Semester (UAS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(18, 'B.2.3', 'Ujian Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(19, 'B.2.4', 'Ujian Susulan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(20, 'B.2.5', 'Ujian Pembekalan KKN/KKU/KAPKI', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(21, 'B.2.6', 'Ujian Kertas Karya', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(22, 'B.2.7', 'Ujian Kerja Praktek/Seminar/Proposal/Draf', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(23, 'B.2.8', 'Ujian Skripsi/Pendadaran/Ujian Tahap Akhir/Proyek', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(24, 'B.2.9', 'Ujian Tesis', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(25, 'B.2.10', 'Ujian Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(26, 'B.3.1', 'Pendaftaran KKN/KKU/KAPKI/KKUKerja Praktek/Kertas Karya/Skripsi/Tesis/Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(27, 'B.3.2', 'Peninjauan/Survey/Data', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(28, 'B.3.3', 'Perijinan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(29, 'B.3.4', 'Pembekalan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(30, 'B.3.5', 'Bimbingan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(31, 'B.3.6', 'Pembatalan/Gugur', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(32, 'B.3.7', 'Perpanjangan', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(33, 'B.3.8', 'Perintah Kerja', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(34, 'B.4.1', 'Evaluasi semesteran', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(35, 'B.4.2', 'Evaluasi tahunan/Jumlah SKS yang telah ditempuh', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(36, 'B.4.3', 'Peringatan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(37, 'B.4.4', 'Perpanjangan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(38, 'B.4.5', 'Sanksi akademik', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(39, 'B.4.6', 'Pemberhentian Status Mahasiswa (DO)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(40, 'B.5.1', 'Pindah Fakultas/Program Studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(41, 'B.5.2', 'Pindah dari Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(42, 'B.5.3', 'Pindah ke Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(43, 'B.5.4', 'Mengundurkan diri', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(44, 'B.6.1', 'Mohon Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(45, 'B.6.2', 'Kirim Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(46, 'B.6.3', 'Revisi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(47, 'B.6.4', 'Hapus Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(48, 'B.6.5', 'Konversi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(49, 'B.6.6', 'Yudisium (Penentuan Nilai Lulus Ujian Sarjana)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(50, 'B.6.7', 'Hasil Studi (KHS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(51, 'B.6.8', 'Daftar Kumpulan Nilai (Transkrip)', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(52, 'B.6.9', 'Pedoman Penilaian', '2025-08-02 15:41:34', '2025-08-02 15:41:34', NULL),
(53, 'B.7.1', 'Informasi/Penawaran Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(54, 'B.7.2', 'Tim Peneliti/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(55, 'B.7.3', 'Ijin Penelitian/Survey/Data', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(56, 'B.7.4', 'Usulan Proyek Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(57, 'B.7.5', 'Review/Revisi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(58, 'B.7.6', 'Laporan Hasil Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(59, 'B.7.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(60, 'B.7.8', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(61, 'B.7.9', 'Penulisan Ilmiah/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(62, 'B.8.1', 'Informasi/Penawaran Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(63, 'B.8.2', 'Tim Pengabdian/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(64, 'B.8.3', 'Ijin Kegiatan Pengabdian/Survey/Data', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(65, 'B.8.4', 'Usulan Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(66, 'B.8.5', 'Review/Revisi/Presentasi Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(67, 'B.8.6', 'Laporan Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(68, 'B.8.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Kegiatan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(69, 'B.8.8', 'Ceramah/Bimbingan/Penyuluhan/Pelatihan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(70, 'B.8.9', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(71, 'B.8.10', 'Penulisan Ilmiah/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(72, 'B.9.1', 'Penetapan Keputusan (SK Kelulusan)', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(73, 'B.9.2', 'Lulusan Terbaik/Tercepat', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(74, 'B.9.3', 'Keterangan Lulus', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(75, 'B.9.4', 'Wisuda/Pelepasan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(76, 'B.9.5', 'Ijazah/Bukti Kelulusan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(77, 'B.9.6', 'Legalisasi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(78, 'B.9.7', 'Keterangan Pengganti Ijazah', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(79, 'B.9.8', 'Penggunaan Gelar', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(80, 'B.9.9', 'Kartu Alumni', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(81, 'B.10.1', 'Pengadaan Buku/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(82, 'B.10.2', 'Pengolahan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(83, 'B.10.3', 'Peminjaman', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(84, 'B.10.4', 'Tagihan Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(85, 'B.10.5', 'Bedah/Resensi Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(86, 'B.10.6', 'Pelatihan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(87, 'B.10.7', 'Pameran Buku/Bursa Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(88, 'B.10.8', 'Koleksi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(89, 'B.10.9', 'Sumbangan Koleksi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(90, 'B.10.10', 'Stock Opname', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(91, 'B.10.11', 'Statistik', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(92, 'B.10.12', 'Tata Tertib', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(93, 'B.10.13', 'Keanggotaan', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(94, 'B.11.1', 'Kalender Akademik', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(95, 'B.12.1', 'Dispensasi (Perkuliahan/Tugas/Praktikum/Pengisian KRS)', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(96, 'B.13.1', 'Heregistrasi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(97, 'B.13.2', 'Aktif Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(98, 'B.13.3', 'Mahasiswa Asing/Pendengar', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(99, 'B.13.4', 'Cuti Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(100, 'B.13.5', 'Sedang Skripsi', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(101, 'B.13.6', 'Pernah Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(102, 'B.13.7', 'Double Degree', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(103, 'B.13.8', 'Penyerahan ijazah sma/smk/paket c', '2025-08-02 15:41:34', '2025-10-11 05:21:41', NULL),
(104, 'A.1.1', 'Promosi', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(105, 'A.1.2', 'Open House', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(106, 'A.1.3', 'Pameran', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(107, 'A.1.4', 'Kunjungan/Safari ke SMU', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(108, 'A.1.5', 'Pertandingan/Lomba antar SMU', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(109, 'A.1.6', 'Diskusi/Seminar/Ceramah/Dialog', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(110, 'A.2.1', 'Jalur PMDK', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(111, 'A.2.2', 'Jalur Kerja Sama', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(112, 'A.2.3', 'Reguler/Umum', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(113, 'A.2.4', 'Materi/Soal Tes', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(114, 'A.2.5', 'Tester/Pengawas', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(115, 'A.2.6', 'Koordinasi Tugas', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(116, 'A.3.1', 'Pengumuman Tes', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(117, 'A.3.2', 'Ketetapan Diterima', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(118, 'A.3.3', 'Hasil Seleksi', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(119, 'A.3.4', 'Registrasi', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(120, 'C.1.1', 'Karya Ilmiah Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(121, 'C.1.2', 'Diskusi/Konferensi/Dialog Ilmiah Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(122, 'C.1.3', 'Simposium', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(123, 'C.2.1', 'Beasiswa Yayasan Sandjojo', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(124, 'C.2.2', 'Beasiswa Swasta (KWI, Djarum, Bank, dll)', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(125, 'C.2.3', 'Beasiswa Pemerintah (Supersemar, Dikti, Kopertis)', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(126, 'C.2.4', 'Beasiswa Luar Negeri', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(127, 'C.3.1', 'Pertukaran Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(128, 'C.3.2', 'Mahasiswa Berprestasi/Mahasiswa Teladan', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(129, 'C.3.3', 'Pertandingan/Kompetisi', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(130, 'C.3.4', 'Pentas Seni/Musik Festival', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(131, 'C.3.5', 'Pelatihan Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(132, 'C.3.6', 'Kemah Bhakti', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(133, 'C.3.7', 'Duta/Utusan/Perwakilan Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(134, 'C.4', 'Pelatihan Pengembangan Kepribadian Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41', NULL),
(189, 'A.99.1', 'Uji Klasifikasi Otomatis', '2026-02-09 14:44:14', '2026-02-09 14:44:14', NULL),
(190, 'A.11.1', 'Test', '2026-02-23 01:56:41', '2026-02-23 01:56:47', '2026-02-23 01:56:47');

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

--
-- Dumping data for table `master_kop_surat`
--

INSERT INTO `master_kop_surat` (`id`, `unit_code`, `nama_kop`, `unit`, `background_path`, `cap_path`, `cap_default_width_mm`, `cap_opacity`, `cap_offset_x_mm`, `cap_offset_y_mm`, `updated_by`, `created_at`, `updated_at`, `mode`, `mode_type`, `nama_fakultas`, `alamat_lengkap`, `telepon_lengkap`, `email_website`, `text_align`, `logo_size`, `font_size_title`, `font_size_text`, `text_color`, `header_padding`, `background_opacity`, `alamat`, `telepon`, `fax`, `email`, `website`, `logo_kiri_path`, `tampilkan_logo_kiri`, `logo_kanan_path`, `tampilkan_logo_kanan`, `deleted_at`) VALUES
(1, NULL, 'Kop Default FIKOM', NULL, 'kop/6947aec6e09d7_1766305478.png', 'kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png', 30, 85, 0, 0, 1, '2025-08-26 03:50:41', '2026-01-29 15:31:13', 'composed', 'custom', 'FAKULTAS ILMU KOMPUTER', 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234', '(024) 8441555 850500', 'unika@unika.ac.id | www.unika.ac.id', 'right', 155, 20, 12, '#333333', 80, 100, 'Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234', '(024) 8441555, 85050003', '(024) 8415429 – 8454265', 'unika@unika.ac.id', 'https://www.unika.ac.id', NULL, 0, 'kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg', 1, NULL);

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

--
-- Dumping data for table `mengingat_library`
--

INSERT INTO `mengingat_library` (`id`, `judul`, `isi`, `kategori`, `nomor_referensi`, `tanggal_referensi`, `dibuat_oleh`, `is_active`, `usage_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'UU Pendidikan Tinggi', 'Undang-Undang Republik Indonesia Nomor 12 Tahun 2012 tentang Pendidikan Tinggi', 'UU', 'UU No. 12 Tahun 2012', '2012-08-10', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(2, 'PP Penyelenggaraan Pendidikan Tinggi', 'Peraturan Pemerintah Republik Indonesia Nomor 4 Tahun 2014 tentang Penyelenggaraan Pendidikan Tinggi dan Pengelolaan Perguruan Tinggi', 'PP', 'PP No. 4 Tahun 2014', '2014-01-17', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(3, 'Statuta Unika', 'Keputusan Yayasan Sandjojo Nomor 66/PER/YS/05/VII/2013 tentang Statuta Universitas Katolik Soegijapranata', 'SK Yayasan', 'No. 66/PER/YS/05/VII/2013', '2013-07-01', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(4, 'Peraturan Akademik Unika', 'Peraturan Universitas Katolik Soegijapranata tentang Pedoman Akademik', 'Peraturan Internal', 'No. E.2/1616/UKS.01/VII/2001', '2001-07-01', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(5, 'SK SN-Dikti', 'Peraturan Menteri Pendidikan dan Kebudayaan Republik Indonesia Nomor 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi', 'Permen', 'Permendikbud No. 3 Tahun 2020', '2020-01-24', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(6, 'UU SISDIKNAS', 'Undang-Undang Republik Indonesia Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional', 'UU', 'UU No. 20 Tahun 2003', '2003-07-08', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(7, 'Permenristekdikti SNPT', 'Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi Republik Indonesia Nomor 44 Tahun 2015 tentang Standar Nasional Pendidikan Tinggi', 'Permen', 'Permenristekdikti No. 44 Tahun 2015', '2015-12-28', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL);

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

--
-- Dumping data for table `menimbang_library`
--

INSERT INTO `menimbang_library` (`id`, `judul`, `isi`, `kategori`, `tags`, `dibuat_oleh`, `is_active`, `usage_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Kelancaran Pelaksanaan Seminar', 'bahwa untuk kelancaran pelaksanaan Seminar Nasional diperlukan pembentukan panitia pelaksana yang kompeten dan berdedikasi', 'akademik', '[\"seminar\", \"panitia\", \"kegiatan\"]', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(2, 'Peningkatan Kualitas Pendidikan', 'bahwa dalam rangka peningkatan kualitas pendidikan dan pembelajaran di lingkungan Fakultas Ilmu Komputer perlu dilakukan evaluasi dan penyempurnaan kurikulum secara berkala', 'akademik', '[\"kurikulum\", \"pendidikan\", \"evaluasi\"]', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(3, 'Pengembangan Tri Dharma', 'bahwa Tri Dharma Perguruan Tinggi meliputi pendidikan, penelitian, dan pengabdian kepada masyarakat yang harus dilaksanakan secara berimbang dan berkelanjutan', 'umum', '[\"tri dharma\", \"penelitian\", \"pengabdian\"]', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(4, 'Pelaksanaan Wisuda', 'bahwa dalam rangka pelaksanaan Wisuda Periode Oktober 2025 diperlukan pembentukan panitia yang bertugas mempersiapkan dan melaksanakan acara tersebut', 'akademik', '[\"wisuda\", \"kelulusan\", \"panitia\"]', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(5, 'Akreditasi Program Studi', 'bahwa untuk mempertahankan dan meningkatkan akreditasi program studi perlu dilakukan persiapan dokumen dan evaluasi diri secara komprehensif', 'akreditasi', '[\"akreditasi\", \"borang\", \"evaluasi diri\"]', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(7, 'Testing', 'Test1', 'test', NULL, 1, 1, 0, '2026-02-23 01:58:19', '2026-02-23 01:58:29', '2026-02-23 01:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_06_02_053923_create_peran_table', 1),
(2, '2025_06_02_053924_create_pengguna_table', 1),
(3, '2025_06_02_053924_create_tugas_header_table', 1),
(4, '2025_06_02_053924_create_tugas_versi_table', 1),
(5, '2025_06_02_053925_create_tugas_log_table', 1),
(6, '2025_06_02_053925_create_tugas_penerima_table', 1),
(7, '2025_06_02_053926_create_agenda_surat_keluar_table', 1),
(8, '2025_06_02_053926_create_notifikasi_table', 1),
(9, '2025_06_02_054733_create_sessions_table', 1),
(10, '2025_06_02_054840_create_cache_table', 1),
(14, '2025_06_05_132456_create_keputusan_header_table', 2),
(15, '2025_06_05_132500_create_keputusan_versi_table', 2),
(16, '2025_06_05_132504_create_keputusan_penerima_table', 2),
(17, '2025_07_31_093533_create_jenis_tugas_table', 3),
(18, '2025_08_01_060824_create_sub_tugas_table', 4),
(19, '2025_08_01_061002_create_tugas_detail_table', 4),
(20, '2025_08_01_061108_update_tugas_header_and_penerima', 5),
(21, '2025_08_01_072249_add_kode_surat_and_bulan_to_tugas_header', 6),
(22, '2025_08_02_043447_add_detail_tugas_to_tugas_header_table', 7),
(23, '2025_08_02_125424_ubah_struktur_tugas_penerima', 8),
(24, '2025_08_02_152651_drop_tugas_versi_table', 9),
(25, '2025_08_02_153206_remove_ijin_tidak_presensi_from_tugas_header_table', 10),
(26, '2025_08_02_153501_create_klasifikasi_surats_table', 11),
(27, '2025_08_02_153851_add_klasifikasi_surat_id_to_tugas_header_table', 12),
(28, '2025_08_25_085953_fix_detail_tugas_notnull', 13),
(29, '2025_08_25_091128_create_nomor_surat_counters_table', 14),
(30, '2025_08_25_100353_add_indexes_surat_tugas', 15),
(31, '2025_08_25_131625_add_tanggal_surat_to_keputusan_header_table', 16),
(32, '2025_08_26_050351_create_jobs_table', 17),
(33, '2025_08_26_060301_create_master_kop_surat_table', 18),
(34, '2025_08_26_155428_extend_master_kop_surat_for_structured_header', 19),
(35, '2025_09_03_191500_create_user_signatures_table', 20),
(36, '2025_09_03_191502_add_signature_fields_to_tugas_and_keputusan', 20),
(37, '2025_09_03_191502_extend_master_kop_surat_add_stamp_layout', 20),
(38, '2025_09_15_025626_add_sign_dimensions_to_tugas_header_table', 21),
(39, '2025_09_26_235311_add_menetapkan_to_keputusan_header_table', 22),
(40, '2025_10_12_202432_remove_tanggal_asli_from_keputusan_header_table', 23),
(41, '2025_12_15_130000_create_surat_templates_table', 41),
(42, '2025_12_15_130001_create_audit_logs_table', 41),
(43, '2025_12_15_140000_create_recipient_imports_table', 42),
(44, '2025_12_15_140001_create_menimbang_library_table', 42),
(45, '2025_12_15_140002_create_mengingat_library_table', 42),
(46, '2025_12_15_140003_extend_master_kop_surat_multiunit', 42),
(47, '2025_12_15_140004_create_notification_preferences_table', 42),
(48, '2025_12_16_000000_add_suffix_columns_to_tugas_header', 43),
(49, '2025_12_17_132341_add_customization_fields_to_master_kop_surat', 44),
(50, '2025_12_17_172156_revert_kop_surat_columns', 45),
(51, '2025_12_19_155931_add_dual_logo_to_master_kop_surat', 46),
(53, '2026_01_30_000515_add_archive_columns_to_tugas_header', 47),
(54, '2026_01_30_003000_modify_status_surat_enum_in_tugas_header', 48),
(55, '2026_01_30_004000_update_keputusan_header_for_archiving', 49),
(57, '2026_02_09_004303_add_klasifikasi_surat_id_to_sub_tugas_table', 50),
(58, '2026_02_09_005500_drop_tugas_detail_table', 51),
(59, '2026_02_09_100000_add_foto_path_to_pengguna_table', 52),
(60, '2026_02_09_114429_add_sub_tugas_id_to_surat_templates_table', 53),
(61, '2026_02_09_234118_add_alasan_penolakan_to_tugas_header_table', 54),
(62, '2026_02_13_000001_fix_suffix_numbering_integrity', 55),
(63, '2026_02_13_165936_create_failed_jobs_table', 56),
(64, '2026_02_13_171136_add_send_email_on_approve_to_tugas_header', 57);

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

--
-- Dumping data for table `nomor_counters`
--

INSERT INTO `nomor_counters` (`id`, `tipe`, `tahun`, `prefix`, `last_number`, `updated_at`, `deleted_at`) VALUES
(1, 'SK', 2026, 'B.10.1/SK/UNIKA', 2, '2026-02-13 10:00:00', NULL);

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

--
-- Dumping data for table `nomor_surat_counters`
--

INSERT INTO `nomor_surat_counters` (`id`, `kode_surat`, `unit`, `bulan_romawi`, `tahun`, `last_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'B.7.2', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(2, 'B.3.5', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(3, 'B.8.2', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(4, 'A.1.3', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(5, 'B.1.1', 'TG', 'II', 2026, 2, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(6, 'C.3.5', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(7, 'B.10.1', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL);

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

--
-- Dumping data for table `notification_preferences`
--

INSERT INTO `notification_preferences` (`id`, `pengguna_id`, `email_on_approval_needed`, `email_on_approved`, `email_on_rejected`, `email_digest_weekly`, `inapp_notifications`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00'),
(2, 1, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00'),
(3, 3, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00');

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

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `pengguna_id`, `tipe`, `referensi_id`, `pesan`, `dibaca`, `created_at`, `updated_at`, `dibuat_pada`, `deleted_at`) VALUES
(1, 10, 'surat_tugas', 2, 'Surat Tugas 001/B.3.5/TG/UNIKA/II/2026 menunggu persetujuan Anda.', 0, '2026-02-11 09:00:00', '2026-02-11 09:00:00', '2026-02-11 09:00:00', NULL),
(2, 1, 'surat_tugas', 3, 'Surat Tugas 001/B.8.2/TG/UNIKA/II/2026 telah disetujui.', 0, '2026-02-06 09:00:00', '2026-02-06 09:00:00', '2026-02-06 09:00:00', NULL),
(3, 13, 'surat_tugas', 3, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.8.2/TG/UNIKA/II/2026.', 0, '2026-02-06 09:00:00', '2026-02-06 09:00:00', '2026-02-06 09:00:00', NULL),
(4, 14, 'surat_tugas', 3, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.8.2/TG/UNIKA/II/2026.', 0, '2026-02-06 09:00:00', '2026-02-06 09:00:00', '2026-02-06 09:00:00', NULL),
(5, 1, 'surat_tugas', 4, 'Surat Tugas 001/A.1.3/TG/UNIKA/II/2026 telah disetujui.', 0, '2026-02-04 08:30:00', '2026-02-04 08:30:00', '2026-02-04 08:30:00', NULL),
(6, 1, 'surat_tugas', 5, 'Surat Tugas 001/B.1.1/TG/UNIKA/II/2026 ditolak. Catatan: TOR dan RAB belum lengkap.', 0, '2026-02-09 09:00:00', '2026-02-09 09:00:00', '2026-02-09 09:00:00', NULL),
(7, 10, 'surat_tugas', 7, 'Surat Tugas 002A/B.1.1/TG/UNIKA/II/2026 menunggu persetujuan Anda.', 0, '2026-02-12 10:00:00', '2026-02-12 10:00:00', '2026-02-12 10:00:00', NULL),
(8, 3, 'surat_tugas', 10, 'Surat Tugas 001/B.7.2/TG/UNIKA/I/2026 menunggu persetujuan Anda.', 0, '2026-01-20 10:00:00', '2026-01-20 10:00:00', '2026-01-20 10:00:00', NULL),
(9, 10, 'surat_keputusan', 2, 'SK Pengangkatan Panitia Seminar menunggu persetujuan Anda.', 0, '2026-02-07 10:00:00', '2026-02-07 10:00:00', '2026-02-07 10:00:00', NULL),
(10, 1, 'surat_keputusan', 3, 'SK Penetapan Kurikulum Prodi TI telah disetujui.', 0, '2026-02-03 09:00:00', '2026-02-03 09:00:00', '2026-02-03 09:00:00', NULL),
(11, 1, 'surat_keputusan', 4, 'SK Penunjukan Dosen Pembimbing KP ditolak.', 0, '2026-02-12 09:00:00', '2026-02-12 09:00:00', '2026-02-12 09:00:00', NULL),
(12, 10, 'surat_tugas', 9, 'Surat Tugas 001/B.10.1/ST.IKOM/UNIKA/II/2026 menunggu persetujuan Anda.', 1, '2026-02-15 06:36:17', '2026-02-15 06:36:26', '2026-02-15 06:36:17', NULL),
(13, 1, 'surat_tugas', 9, 'Surat Tugas 001/B.10.1/ST.IKOM/UNIKA/II/2026 telah disetujui.', 0, '2026-02-15 06:36:28', '2026-02-15 06:36:28', '2026-02-15 06:36:28', NULL),
(14, 11, 'surat_tugas', 9, 'Anda menerima Surat Tugas baru: 001/B.10.1/ST.IKOM/UNIKA/II/2026', 0, '2026-02-15 06:36:28', '2026-02-15 06:36:28', '2026-02-15 06:36:28', NULL),
(15, 12, 'surat_tugas', 9, 'Anda menerima Surat Tugas baru: 001/B.10.1/ST.IKOM/UNIKA/II/2026', 0, '2026-02-15 06:36:28', '2026-02-15 06:36:28', '2026-02-15 06:36:28', NULL),
(16, 16, 'surat_tugas', 9, 'Anda menerima Surat Tugas baru: 001/B.10.1/ST.IKOM/UNIKA/II/2026', 0, '2026-02-15 06:36:28', '2026-02-15 06:36:28', '2026-02-15 06:36:28', NULL),
(17, 22, 'surat_tugas', 9, 'Anda menerima Surat Tugas baru: 001/B.10.1/ST.IKOM/UNIKA/II/2026', 0, '2026-02-15 06:36:28', '2026-02-15 06:36:28', '2026-02-15 06:36:28', NULL),
(18, 1, 'surat_tugas', 7, 'Surat Tugas 002A/B.1.1/TG/UNIKA/II/2026 telah disetujui.', 0, '2026-02-15 06:52:19', '2026-02-15 06:52:19', '2026-02-15 06:52:19', NULL),
(19, 12, 'surat_tugas', 7, 'Anda menerima Surat Tugas baru: 002A/B.1.1/TG/UNIKA/II/2026', 0, '2026-02-15 06:52:19', '2026-02-15 06:52:19', '2026-02-15 06:52:19', NULL),
(20, 9, 'surat_tugas', 7, 'Anda menerima Surat Tugas baru: 002A/B.1.1/TG/UNIKA/II/2026', 0, '2026-02-15 06:52:32', '2026-02-15 06:52:32', '2026-02-15 06:52:32', NULL),
(21, 1, 'surat_tugas', 2, 'Surat Tugas 001/B.3.5/TG/UNIKA/II/2026 telah disetujui.', 1, '2026-02-15 06:53:38', '2026-02-23 02:36:13', '2026-02-15 06:53:38', NULL),
(22, 17, 'surat_tugas', 2, 'Anda menerima Surat Tugas baru: 001/B.3.5/TG/UNIKA/II/2026', 0, '2026-02-15 06:53:38', '2026-02-15 06:53:38', '2026-02-15 06:53:38', NULL),
(23, 8, 'surat_tugas', 2, 'Anda menerima Surat Tugas baru: 001/B.3.5/TG/UNIKA/II/2026', 0, '2026-02-15 06:53:44', '2026-02-15 06:53:44', '2026-02-15 06:53:44', NULL),
(24, 9, 'surat_tugas', 2, 'Anda menerima Surat Tugas baru: 001/B.3.5/TG/UNIKA/II/2026', 0, '2026-02-15 06:53:48', '2026-02-15 06:53:48', '2026-02-15 06:53:48', NULL),
(25, 11, 'surat_keputusan', 3, 'SK &quot;Penetapan Kurikulum Program Studi Teknik Informatika Tahun 2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:21', '2026-02-23 01:57:21', '2026-02-23 01:57:21', NULL),
(26, 16, 'surat_keputusan', 3, 'SK &quot;Penetapan Kurikulum Program Studi Teknik Informatika Tahun 2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:21', '2026-02-23 01:57:21', '2026-02-23 01:57:21', NULL),
(27, 10, 'surat_keputusan', 5, 'Penerbitan SK &quot;Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026&quot; telah dibatalkan.', 0, '2026-02-23 01:57:48', '2026-02-23 01:57:48', '2026-02-23 01:57:48', NULL),
(28, 7, 'surat_keputusan', 5, 'SK &quot;Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:54', '2026-02-23 01:57:54', '2026-02-23 01:57:54', NULL),
(29, 8, 'surat_keputusan', 5, 'SK &quot;Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:54', '2026-02-23 01:57:54', '2026-02-23 01:57:54', NULL),
(30, 9, 'surat_keputusan', 5, 'SK &quot;Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:54', '2026-02-23 01:57:54', '2026-02-23 01:57:54', NULL),
(31, 12, 'surat_keputusan', 5, 'SK &quot;Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026&quot; telah diterbitkan dan berlaku efektif.', 0, '2026-02-23 01:57:54', '2026-02-23 01:57:54', '2026-02-23 01:57:54', NULL);

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

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `email`, `sandi_hash`, `nama_lengkap`, `npp`, `jabatan`, `peran_id`, `status`, `created_at`, `updated_at`, `last_activity`, `deleted_at`, `remember_token`, `foto_path`) VALUES
(1, 'agustina.anggitasari@unika.ac.id', '$2y$12$0rYDf0RqcBpaABHw3vaOxe3LV6UxLazy9R85vBmmwA8juagm6Xadq', 'Agustina Alam Anggitasari, Se., Mm', NULL, 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2026-02-15 05:41:41', '2026-02-13 18:47:11', NULL, 'MG6aVPVocGxCPC1BtgdUF9bytB0bKRY38QAUlaltKHClCLinSMp4SDM6OSlt', NULL),
(2, 'kariyani.spd@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'KARIYANI, S.Pd', NULL, 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2025-11-19 12:44:49', '2025-11-19 19:44:49', NULL, NULL, NULL),
(3, 'bernhardinus.harnadi@unika.ac.id', '$2y$12$rr.ntE7OagwdG25kLxSLwOnZwIaq72oImrbM8jXOkn6AEM62QRIY2', 'Prof. BERNARDINUS HARNADI, ST., MT., Ph.D.', NULL, NULL, 3, 'aktif', '2025-04-22 03:15:27', '2025-12-16 18:51:21', '2025-12-17 01:51:21', NULL, NULL, NULL),
(4, 'muh.khudori@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'MUH KHUDORI', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:31', NULL, NULL, NULL, NULL),
(5, 'paulus.sapto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'PAULUS SAPTO NUGROHO', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:36', NULL, NULL, NULL, NULL),
(6, 'bambang.setiawan@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'BAMBANG SETIAWAN, ST', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:39', NULL, NULL, NULL, NULL),
(7, 'erdhi.nugroho@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ERDHI WIDYARTO NUGROHO, ST., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:43', NULL, NULL, NULL, NULL),
(8, 'fx.hendra@unika.ac.id', '$2y$12$yzabxjVXeAkmIgQmvCQBuOwjSU1cUnGqowliut934gE1bnbNMZ9M.', 'FX. HENDRA PRASETYA, ST, MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-10-11 17:50:27', '2025-10-12 00:50:27', NULL, NULL, NULL),
(9, 'tecla.chandrawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. TECLA BRENDA CHANDRAWATI, S.T., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:51', NULL, NULL, NULL, NULL),
(10, 'ridwan.sanjaya@unika.ac.id', '$2y$12$VRlxXvgiT0gdC3mVx0vp6Oct3Q/VPnmvACYjDz3n.DKotAIkG1QrS', 'Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC', '058.1.2002.255', 'Dekan Fakultas Ilmu Komputer', 2, 'aktif', '2025-04-22 03:15:27', '2026-01-29 16:54:41', '2026-01-29 23:54:41', NULL, NULL, NULL),
(11, 'alb.dwiw@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ALBERTUS DWIYOGA WIDIANTORO, S.Kom., M.Kom', NULL, NULL, 4, 'aktif', '2025-04-22 03:15:27', NULL, NULL, NULL, NULL, NULL),
(12, '22n10002@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'AGUS CAHYO NUGROHO, S.Kom., M.T', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2026-02-15 05:41:09', NULL, NULL, NULL, NULL),
(13, 'andre.pamudji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ANDRE KURNIAWAN PAMUDJI, S.Kom., M.Ling', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:10', NULL, NULL, NULL, NULL),
(14, 'stephan.swastini@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'STEPHAN INGRITT SWASTINI DEWI, S.Kom., MBA', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:13', NULL, NULL, NULL, NULL),
(15, 'hironimus.leong@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HIRONIMUS LEONG, S.Kom., M.Com', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:15', NULL, NULL, NULL, NULL),
(16, 'rosita.herawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ROSITA HERAWATI, ST., MT', NULL, NULL, 4, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:41:03', NULL, NULL, NULL, NULL),
(17, 'yulianto.putranto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. YULIANTO TEDJO PUTRANTO, ST., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:21', NULL, NULL, NULL, NULL),
(18, 'shinta.wahyuningrum@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'SHINTA ESTRI WAHYUNINGRUM, S.Si., M.Cs', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:25', NULL, NULL, NULL, NULL),
(19, 'setiawan.aji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'R. SETIAWAN AJI NUGROHO, ST. M.CompIT, Ph.D', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:28', NULL, NULL, NULL, NULL),
(20, 'dwi.setianto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Y.B. DWI SETIANTO, ST., M.Cs(CCNA)', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:31', NULL, NULL, NULL, NULL),
(21, 'yonathan.santosa@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'YONATHAN PURBO SANTOSA, S.Kom., M.Sc', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:47', NULL, NULL, NULL, NULL),
(22, 'henoch.christanto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HENOCH JULI CHRISTANTO, S.Kom., M.Kom', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:52', NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `peran`
--

INSERT INTO `peran` (`id`, `nama`, `deskripsi`, `dibuat_pada`, `deleted_at`) VALUES
(1, 'admin_tu', 'Administrator Tata Usaha Fakultas', '2025-06-01 22:53:10', NULL),
(2, 'dekan', 'Dekan Fakultas', '2025-06-01 22:53:10', NULL),
(3, 'wakil_dekan', 'Wakil Dekan Fakultas', '2025-06-01 22:53:10', NULL),
(4, 'kaprodi', 'Kepala Program Studi', '2025-06-01 22:53:10', NULL),
(5, 'dosen', 'Dosen Pengajar', '2025-08-02 16:38:34', NULL),
(6, 'tendik', 'Tenaga Kependidikan', '2025-08-02 16:38:34', NULL);

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('bb3qmv3uvddLA9TRDQftiyDwNlkozREw27cyHq8I', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6IjlwSHRMcTFCdWZ3YTQyNTg4UzJSczZJZTlVWDNZV3pHNnd5SldyQ1giO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6ODoicGVyYW5faWQiO2k6MTtzOjEwOiJwZXJhbl9uYW1hIjtzOjg6ImFkbWluX3R1IjtzOjg6ImlzX2FkbWluIjtiOjE7czo4OiJpc19kb3NlbiI7YjowO3M6MTM6Imxhc3RfYWN0aXZpdHkiO086MjU6IklsbHVtaW5hdGVcU3VwcG9ydFxDYXJib24iOjM6e3M6NDoiZGF0ZSI7czoyNjoiMjAyNi0wMi0yMyAwODo1NTo0Ni4wNjU5MzIiO3M6MTM6InRpbWV6b25lX3R5cGUiO2k6MztzOjg6InRpbWV6b25lIjtzOjEyOiJBc2lhL0pha2FydGEiO31zOjk6InVzZXJfbmFtZSI7czozNDoiQWd1c3RpbmEgQWxhbSBBbmdnaXRhc2FyaSwgU2UuLCBNbSI7fQ==', 1771814174);

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

--
-- Dumping data for table `sub_tugas`
--

INSERT INTO `sub_tugas` (`id`, `jenis_tugas_id`, `klasifikasi_surat_id`, `nama`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 9, 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, NULL, NULL),
(2, 1, 9, 'Koordinator MK', NULL, NULL, NULL),
(3, 1, 9, 'Koordinator Tugas MK', NULL, NULL, NULL),
(4, 1, 30, 'Bimbingan Mahasiswa/Akademik', NULL, NULL, NULL),
(5, 7, 30, 'Pendampingan dosen dalam KKL', NULL, NULL, NULL),
(6, 1, 26, 'Koordinator Kerja Praktik/KKL', NULL, '2025-10-11 05:28:34', NULL),
(7, 5, 54, 'Reviewer Kenaikan Jabatan Fungsional Lektor Kepala', NULL, NULL, NULL),
(8, 5, 54, 'Reviewer Kenaikan Jabatan Fungsional Guru Besar', NULL, NULL, NULL),
(9, 5, 54, 'Reviewer Kenaikan Jabatan Fungsional Asisten Ahli', NULL, NULL, NULL),
(10, 5, 54, 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, NULL, NULL),
(11, 5, 54, 'Asesor BKD', NULL, '2026-02-08 16:36:01', NULL),
(12, 5, 54, 'Validator BKD', NULL, NULL, NULL),
(13, 3, 57, 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, NULL, NULL),
(14, 6, 57, 'Reviewer Jurnal Nasional', NULL, NULL, NULL),
(15, 6, 57, 'Reviewer Jurnal Internasional', NULL, NULL, NULL),
(16, 8, 1, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05', NULL),
(90, 1, 30, 'Pembimbing Akademik (PA)', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(91, 1, 30, 'Pembimbing Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(92, 1, 30, 'Penguji Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(93, 1, 30, 'Pembimbing Kerja Praktik', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(94, 2, 56, 'Ketua Penelitian (Internal/Eksternal)', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(95, 2, 56, 'Anggota Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(96, 2, 56, 'Penyusun Proposal Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(97, 3, 65, 'Ketua Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(98, 3, 65, 'Anggota Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(99, 3, 65, 'Narasumber/Pemateri Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(100, 4, 9, 'Panitia Kegiatan Fakultas/Prodi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(101, 4, 9, 'Pembina/Koordinator UKM/Komunitas', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(102, 4, 9, 'Pembicara Tamu/Kuliah Umum', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(103, 5, 9, 'Sekretaris/Koordinator Program Studi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(104, 5, 9, 'Panitia Seleksi/Asesor Internal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(105, 5, 9, 'Pengembang Kurikulum/Perangkat Akademik', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(106, 6, 61, 'Editor/Section Editor Jurnal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(107, 6, 61, 'Pemakalah Seminar Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(108, 6, 61, 'Pemakalah Seminar Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(109, 6, 61, 'Penulis Jurnal Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(110, 6, 61, 'Penulis Jurnal Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(111, 7, 63, 'Narasumber/Trainer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(112, 7, 63, 'Konsultan/Reviewer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(113, 8, 9, 'Tugas Khusus Pimpinan', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL);

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

--
-- Dumping data for table `surat_templates`
--

INSERT INTO `surat_templates` (`id`, `nama`, `deskripsi`, `jenis_tugas_id`, `sub_tugas_id`, `detail_tugas`, `tembusan`, `is_active`, `dibuat_oleh`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Template Seminar Nasional', 'Template untuk penugasan panitia atau pembicara seminar nasional', 5, NULL, '<p>Sehubungan dengan akan diselenggarakannya <strong>Seminar Nasional {{nama_kegiatan}}</strong> yang akan dilaksanakan pada:</p>\r\n<ul>\r\n<li>Hari/Tanggal: {{tanggal_pelaksanaan}}</li>\r\n<li>Waktu: {{waktu_pelaksanaan}}</li>\r\n<li>Tempat: {{tempat_pelaksanaan}}</li>\r\n</ul>\r\n<p>Maka dengan ini kami menugaskan kepada yang bersangkutan untuk:</p>\r\n<ol>\r\n<li>Menjadi {{peran_utama}} dalam kegiatan tersebut</li>\r\n<li>Mempersiapkan segala keperluan teknis dan administratif</li>\r\n<li>Menyusun laporan kegiatan setelah acara selesai</li>\r\n</ol>', 'Yth. Rektor,Yth. Wakil Rektor I,Dekan Fakultas Ilmu Komputer,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(2, 'Template Pelatihan Dosen', 'Template untuk penugasan dosen mengikuti pelatihan/workshop', 4, 100, '<p>Dalam rangka peningkatan kompetensi dosen, dengan ini ditugaskan kepada yang bersangkutan untuk mengikuti:</p><ul><li>Nama Pelatihan: {{nama_pelatihan}}</li><li>Penyelenggara: {{penyelenggara}}</li><li>Tanggal: {{tanggal_mulai}} s.d. {{tanggal_selesai}}</li><li>Lokasi: {{lokasi_pelatihan}}</li></ul><p>Yang bersangkutan berkewajiban untuk:</p><ol><li>Mengikuti seluruh rangkaian kegiatan pelatihan</li><li>Menyerahkan sertifikat/bukti kehadiran</li><li>Membuat laporan hasil pelatihan</li></ol>', 'Yth. Wakil Rektor I,Kepala Program Studi,Unit Kepegawaian,Arsip', 1, 1, '2025-12-15 07:27:29', '2026-02-09 05:09:49', NULL),
(3, 'Template Penugasan Penelitian', 'Template untuk penugasan melakukan penelitian', 1, NULL, '<p>Berdasarkan program penelitian Fakultas Ilmu Komputer Tahun Akademik {{tahun_akademik}}, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan penelitian dengan judul:</p>\r\n<p><strong>\"{{judul_penelitian}}\"</strong></p>\r\n<p>Penelitian dilaksanakan pada:</p>\r\n<ul>\r\n<li>Periode: {{periode_penelitian}}</li>\r\n<li>Sumber Dana: {{sumber_dana}}</li>\r\n</ul>\r\n<p>Dengan kewajiban:</p>\r\n<ol>\r\n<li>Melaksanakan penelitian sesuai proposal yang diajukan</li>\r\n<li>Menyusun laporan kemajuan dan laporan akhir</li>\r\n<li>Mempublikasikan hasil penelitian di jurnal terakreditasi</li>\r\n</ol>', 'Yth. Wakil Rektor I,Ketua Lembaga Penelitian,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(4, 'Template Pengabdian Masyarakat', 'Template untuk penugasan melakukan kegiatan pengabdian kepada masyarakat', 2, NULL, '<p>Dalam rangka melaksanakan Tri Dharma Perguruan Tinggi, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan kegiatan Pengabdian kepada Masyarakat dengan tema:</p>\r\n<p><strong>\"{{tema_pengabdian}}\"</strong></p>\r\n<p>Kegiatan dilaksanakan di:</p>\r\n<ul>\r\n<li>Lokasi: {{lokasi_pengabdian}}</li>\r\n<li>Mitra: {{nama_mitra}}</li>\r\n<li>Waktu: {{waktu_pelaksanaan}}</li>\r\n</ul>\r\n<p>Kewajiban yang harus dipenuhi:</p>\r\n<ol>\r\n<li>Melaksanakan kegiatan sesuai proposal</li>\r\n<li>Membuat dokumentasi kegiatan</li>\r\n<li>Menyusun laporan akhir kegiatan</li>\r\n</ol>', 'Yth. Wakil Rektor II,Ketua LPPM,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(5, 'Template Kegiatan Akademik Umum', 'Template umum untuk penugasan berbagai kegiatan akademik', 7, NULL, '<p><strong>Sehubungan dengan</strong> {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:</p><ol><li>{{tugas_1}}</li><li>{{tugas_2}}</li><li>{{tugas_3}}</li></ol><p>Pelaksanaan tugas pada:</p><ul><li>Tanggal: {{tanggal_pelaksanaan}}</li><li>Waktu: {{waktu}}</li><li>Tempat: {{tempat}}</li></ul><p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>', 'Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 16:03:32', NULL);

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

--
-- Dumping data for table `tugas_header`
--

INSERT INTO `tugas_header` (`id`, `nomor`, `suffix`, `parent_tugas_id`, `nomor_urut_int`, `tanggal_asli`, `status_surat`, `alasan_penolakan`, `nomor_surat`, `tanggal_surat`, `submitted_at`, `signed_at`, `dibuat_oleh`, `dibuat_pada`, `dikunci_pada`, `file_path`, `signed_pdf_path`, `nomor_status`, `no_bin`, `tahun`, `semester`, `no_surat_manual`, `nama_umum`, `asal_surat`, `status_penerima`, `jenis_tugas`, `tugas`, `detail_tugas`, `waktu_mulai`, `waktu_selesai`, `tempat`, `redaksi_pembuka`, `penutup`, `tembusan`, `penandatangan`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `next_approver`, `send_email_on_approve`, `created_at`, `updated_at`, `kode_surat`, `bulan`, `klasifikasi_surat_id`, `deleted_at`, `tanggal_arsip`, `arsipkan_oleh`) VALUES
(1, '001/B.7.2/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'draft', NULL, NULL, '2026-02-10', NULL, NULL, 1, '2026-02-10 08:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Koordinator MK Basis Data', 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', '<p>Ditugaskan sebagai Koordinator Mata Kuliah Basis Data untuk menyusun RPS, mengoordinasikan materi ajar, dan memastikan keseragaman penilaian antar kelas.</p>', '2026-02-17 08:00:00', '2026-06-30 17:00:00', 'Fakultas Ilmu Komputer', 'Sehubungan dengan pelaksanaan perkuliahan Semester Genap 2025/2026, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.', 'Yth. Kepala Program Studi Teknik Informatika\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-10 08:00:00', '2026-02-10 08:00:00', 'B.7.2', 'II', 54, NULL, NULL, NULL),
(2, '001/B.3.5/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-11', '2026-02-11 09:00:00', '2026-02-15 06:53:38', 1, '2026-02-11 09:00:00', '2026-02-15 06:53:38', NULL, 'private/surat_tugas/signed/2_001B35TGUNIKAII2026_920be41a3b98e837a25f4b1c22e0323a.pdf', 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Reviewer Jurnal Internal', 1, 'dosen', 'Penelitian', 'Reviewer Kenaikan Jabatan Fungsional Lektor', '<p>Ditugaskan sebagai Tim Reviewer Jurnal Internal Fakultas Ilmu Komputer untuk melakukan penilaian naskah dan menjamin kualitas publikasi.</p>', '2026-02-15 08:00:00', '2026-04-30 17:00:00', 'Ruang Rapat FIKOM', 'Sehubungan dengan kebutuhan peningkatan kualitas jurnal internal, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat agar dilaksanakan dengan sebaik-baiknya.', 'Yth. Dekan\nArsip', 10, '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"42\"}', '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"35\"}', 42, 35, 0.95, NULL, 1, '2026-02-11 09:00:00', '2026-02-15 06:53:54', 'B.3.5', 'II', 30, NULL, NULL, NULL),
(3, '001/B.8.2/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-05', '2026-02-05 10:00:00', '2026-02-06 09:00:00', 1, '2026-02-05 08:00:00', '2026-02-06 09:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Pengabdian Masyarakat Literasi Digital', 1, 'dosen', 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', '<p>Ditugaskan untuk melaksanakan pengabdian berupa pelatihan literasi digital bagi pelaku UMKM di wilayah Semarang Selatan.</p>', '2026-02-20 08:00:00', '2026-02-20 16:00:00', 'Balai Desa Tembalang, Semarang', 'Menindaklanjuti program pengabdian kepada masyarakat, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.', 'Yth. LPPM UNIKA\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-05 08:00:00', '2026-02-06 09:00:00', 'B.8.2', 'II', 63, NULL, NULL, NULL),
(4, '001/A.1.3/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-03', '2026-02-03 10:00:00', '2026-02-04 08:30:00', 1, '2026-02-03 08:00:00', '2026-02-04 08:30:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Narasumber Kuliah Umum Keamanan Siber', 10, 'dosen', 'Penunjang Almamater', 'Pembicara Tamu/Kuliah Umum', '<p>Ditugaskan sebagai narasumber Kuliah Umum: <strong>Keamanan Siber dan Etika Digital</strong>.</p>', '2026-02-15 09:00:00', '2026-02-15 12:00:00', 'Aula Albertus UNIKA', 'Sehubungan dengan pelaksanaan kuliah umum, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat agar dilaksanakan.', 'Yth. Dekan\nYth. Kaprodi TI\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-03 08:00:00', '2026-02-04 08:30:00', 'A.1.3', 'II', 106, NULL, NULL, NULL),
(5, '001/B.1.1/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'ditolak', 'Berkas pendukung (TOR dan RAB) belum lengkap. Mohon dilengkapi sebelum diajukan kembali.', NULL, '2026-02-08', '2026-02-08 14:00:00', NULL, 1, '2026-02-08 10:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Studi Banding Kurikulum ke Universitas Mitra', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Ditugaskan untuk studi banding kurikulum ke universitas mitra dalam rangka pengembangan kurikulum berbasis industri.</p>', '2026-02-25 08:00:00', '2026-02-27 17:00:00', 'Jakarta', 'Sehubungan dengan program pengembangan kurikulum.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Wakil Rektor I\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-08 10:00:00', '2026-02-09 09:00:00', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(6, '002/B.1.1/TG/UNIKA/II/2026', NULL, NULL, 2, NULL, 'disetujui', NULL, NULL, '2026-02-01', '2026-02-01 10:00:00', '2026-02-02 08:00:00', 1, '2026-02-01 08:00:00', '2026-02-02 08:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia Workshop Literasi Data Dosen', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Workshop Literasi Data untuk Dosen. Meliputi koordinasi peserta, kesiapan ruang, dokumentasi, dan laporan.</p>', '2026-02-10 08:00:00', '2026-02-10 16:00:00', 'Ruang HC Lt.8', 'Sehubungan dengan pelaksanaan Workshop Literasi Data.', 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.', 'Yth. Wakil Rektor I\nYth. Kepala Program Studi\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-01 08:00:00', '2026-02-02 08:00:00', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(7, '002A/B.1.1/TG/UNIKA/II/2026', 'A', 6, 2, NULL, 'disetujui', NULL, NULL, '2026-02-12', '2026-02-12 10:00:00', '2026-02-15 06:52:19', 1, '2026-02-12 10:00:00', '2026-02-15 06:52:19', NULL, 'private/surat_tugas/signed/7_002AB11TGUNIKAII2026_f3892c22c14d5492e16cee17bf3d5c88.pdf', 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia Workshop Literasi Data (Sesi Lanjutan)', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Surat tugas turunan untuk sesi lanjutan workshop, khususnya dukungan teknis lab dan dokumentasi video.</p>', '2026-02-17 08:00:00', '2026-02-17 16:00:00', 'Lab Komputer FIKOM', 'Sehubungan dengan pelaksanaan sesi lanjutan workshop.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Wakil Rektor I\nArsip', 10, '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"42\"}', '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"35\"}', 42, 35, 0.95, NULL, 1, '2026-02-12 10:00:00', '2026-02-15 06:52:36', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(8, '001/C.3.5/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-01-15', '2026-01-15 10:00:00', '2026-01-16 08:00:00', 1, '2026-01-15 08:00:00', '2026-01-16 08:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia UAS Semester Ganjil 2025/2026', 3, 'tendik', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Panitia pelaksana UAS Ganjil 2025/2026: koordinasi ruang, pengawasan, dan rekapitulasi administrasi ujian.</p>', '2026-01-20 08:00:00', '2026-01-31 17:00:00', 'Kampus UNIKA - Ruang Ujian FIKOM', 'Sehubungan dengan pelaksanaan UAS, dengan ini dibentuk panitia pelaksana.', 'Harap melaksanakan tugas sesuai jadwal dan ketentuan yang berlaku.', 'Yth. Wakil Rektor I\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-01-15 08:00:00', '2026-02-23 01:56:02', 'C.3.5', 'II', 131, NULL, NULL, NULL),
(9, '001/B.10.1/ST.IKOM/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-13', '2026-02-15 13:36:17', '2026-02-15 06:36:28', 1, '2026-02-13 08:00:00', '2026-02-15 06:36:28', NULL, 'private/surat_tugas/signed/9_001B101STIKOMUNIKAII2026_9a723da5053971b20e2b6f05a60fbf91.pdf', 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Penyusun Borang Akreditasi Prodi TI', 10, 'dosen', 'Penunjang Administrasi dan Manajemen', 'Sekretaris/Koordinator Program Studi', 'Tim Penyusun Borang Akreditasi Prodi TI: pengumpulan dokumen, validasi data, dan penyelarasan narasi.', '2026-03-01 08:00:00', '2026-05-31 17:00:00', 'Fakultas Ilmu Komputer', 'Sehubungan dengan persiapan akreditasi prodi.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Dekan\nYth. Kaprodi Ti\nArsip', 10, '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"42\"}', '{\"x\": \"0\", \"y\": \"0\", \"w_mm\": \"35\"}', 42, 35, 0.95, NULL, 1, '2026-02-13 08:00:00', '2026-02-15 06:36:30', 'B.10.1', 'II', 81, NULL, NULL, NULL),
(10, '001/B.7.2/TG/UNIKA/I/2026', NULL, NULL, 1, NULL, 'pending', NULL, NULL, '2026-01-20', '2026-01-20 10:00:00', NULL, 1, '2026-01-20 08:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Ganjil', NULL, 'Penugasan Penelitian Kolaboratif AI untuk Pendidikan', 1, 'dosen', 'Penelitian', 'Ketua Penelitian (Internal/Eksternal)', '<p>Penelitian kolaboratif: penerapan AI untuk pembelajaran adaptif, meliputi pengumpulan data, analisis, dan naskah publikasi.</p>', '2026-02-01 08:00:00', '2026-07-31 17:00:00', 'Kampus UNIKA', 'Dalam rangka pelaksanaan program penelitian.', 'Demikian surat tugas ini dibuat untuk dapat dipergunakan sebagaimana mestinya.', 'Yth. Kepala Pusat Penelitian\nArsip', 3, NULL, NULL, NULL, NULL, NULL, 3, 1, '2026-01-20 08:00:00', '2026-01-20 10:00:00', 'B.7.2', 'I', 54, NULL, NULL, NULL);

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

--
-- Dumping data for table `tugas_log`
--

INSERT INTO `tugas_log` (`id`, `tugas_id`, `status_lama`, `status_baru`, `user_id`, `ip_address`, `user_agent`, `created_at`, `deleted_at`) VALUES
(1, 1, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-10 08:00:00', NULL),
(2, 2, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-11 08:00:00', NULL),
(3, 2, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-11 09:00:00', NULL),
(4, 3, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-05 08:00:00', NULL),
(5, 3, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-05 10:00:00', NULL),
(6, 3, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0', '2026-02-06 09:00:00', NULL),
(7, 4, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-03 08:00:00', NULL),
(8, 4, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-03 10:00:00', NULL),
(9, 4, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0', '2026-02-04 08:30:00', NULL),
(10, 5, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-08 10:00:00', NULL),
(11, 5, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-08 14:00:00', NULL),
(12, 5, 'pending', 'ditolak', 10, '127.0.0.1', 'Mozilla/5.0', '2026-02-09 09:00:00', NULL),
(13, 6, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-01 08:00:00', NULL),
(14, 6, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-01 10:00:00', NULL),
(15, 6, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0', '2026-02-02 08:00:00', NULL),
(16, 7, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-12 10:00:00', NULL),
(17, 8, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-01-15 08:00:00', NULL),
(18, 8, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-01-15 10:00:00', NULL),
(19, 8, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0', '2026-01-16 08:00:00', NULL),
(20, 8, 'disetujui', 'arsip', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-05 10:00:00', NULL),
(21, 9, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-02-13 08:00:00', NULL),
(22, 10, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0', '2026-01-20 08:00:00', NULL),
(23, 10, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-01-20 10:00:00', NULL),
(24, 9, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-15 06:36:17', NULL),
(25, 9, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 06:36:28', NULL),
(26, 7, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 06:52:35', NULL),
(27, 2, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 06:53:53', NULL);

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
-- Dumping data for table `tugas_penerima`
--

INSERT INTO `tugas_penerima` (`id`, `tugas_id`, `pengguna_id`, `nama_penerima`, `jabatan_penerima`, `instansi`, `penerima_key`, `dibaca`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 7, '', 'Dosen Pengajar', NULL, 'I#7', 0, '2026-02-10 08:00:00', '2026-02-10 08:00:00', NULL),
(2, 1, 12, '', 'Dosen Pengajar', NULL, 'I#12', 0, '2026-02-10 08:00:00', '2026-02-10 08:00:00', NULL),
(3, 2, 8, '', 'Dosen Pengajar', NULL, 'I#8', 0, '2026-02-11 09:00:00', '2026-02-11 09:00:00', NULL),
(4, 2, 9, '', 'Dosen Pengajar', NULL, 'I#9', 0, '2026-02-11 09:00:00', '2026-02-11 09:00:00', NULL),
(5, 2, 17, '', 'Dosen Pengajar', NULL, 'I#17', 0, '2026-02-11 09:00:00', '2026-02-11 09:00:00', NULL),
(6, 3, 13, '', 'Dosen Pengajar', NULL, 'I#13', 0, '2026-02-05 08:00:00', '2026-02-05 08:00:00', NULL),
(7, 3, 14, '', 'Dosen Pengajar', NULL, 'I#14', 0, '2026-02-05 08:00:00', '2026-02-05 08:00:00', NULL),
(8, 3, 18, '', 'Dosen Pengajar', NULL, 'I#18', 0, '2026-02-05 08:00:00', '2026-02-05 08:00:00', NULL),
(9, 4, 19, '', 'Dosen Pengajar', NULL, 'I#19', 0, '2026-02-03 08:00:00', '2026-02-03 08:00:00', NULL),
(10, 5, 7, '', 'Dosen Pengajar', NULL, 'I#7', 0, '2026-02-08 10:00:00', '2026-02-08 10:00:00', NULL),
(11, 5, 15, '', 'Dosen Pengajar', NULL, 'I#15', 0, '2026-02-08 10:00:00', '2026-02-08 10:00:00', NULL),
(12, 6, 4, '', 'Tenaga Kependidikan', NULL, 'I#4', 0, '2026-02-01 08:00:00', '2026-02-01 08:00:00', NULL),
(13, 6, 5, '', 'Tenaga Kependidikan', NULL, 'I#5', 0, '2026-02-01 08:00:00', '2026-02-01 08:00:00', NULL),
(14, 6, 6, '', 'Tenaga Kependidikan', NULL, 'I#6', 0, '2026-02-01 08:00:00', '2026-02-01 08:00:00', NULL),
(15, 7, 9, '', 'Dosen Pengajar', NULL, 'I#9', 0, '2026-02-12 10:00:00', '2026-02-12 10:00:00', NULL),
(16, 7, 12, '', 'Dosen Pengajar', NULL, 'I#12', 0, '2026-02-12 10:00:00', '2026-02-12 10:00:00', NULL),
(17, 8, 4, '', 'Tenaga Kependidikan', NULL, 'I#4', 0, '2026-01-15 08:00:00', '2026-01-15 08:00:00', NULL),
(18, 8, 5, '', 'Tenaga Kependidikan', NULL, 'I#5', 0, '2026-01-15 08:00:00', '2026-01-15 08:00:00', NULL),
(19, 9, 11, '', 'Kaprodi', NULL, 'I#11', 0, '2026-02-13 08:00:00', '2026-02-15 06:36:17', '2026-02-15 06:36:17'),
(20, 9, 16, '', 'Kaprodi', NULL, 'I#16', 0, '2026-02-13 08:00:00', '2026-02-15 06:36:17', '2026-02-15 06:36:17'),
(21, 9, 22, '', 'Dosen Pengajar', NULL, 'I#22', 0, '2026-02-13 08:00:00', '2026-02-15 06:36:17', '2026-02-15 06:36:17'),
(22, 10, 20, '', 'Dosen Pengajar', NULL, 'I#20', 0, '2026-01-20 08:00:00', '2026-01-20 08:00:00', NULL),
(23, 10, 21, '', 'Dosen Pengajar', NULL, 'I#21', 0, '2026-01-20 08:00:00', '2026-01-20 08:00:00', NULL),
(24, 9, 11, '', NULL, NULL, 'I#11', 0, '2026-02-15 06:36:17', '2026-02-15 06:36:17', NULL),
(25, 9, 12, '', NULL, NULL, 'I#12', 0, '2026-02-15 06:36:17', '2026-02-15 06:36:17', NULL),
(26, 9, 16, '', NULL, NULL, 'I#16', 0, '2026-02-15 06:36:17', '2026-02-15 06:36:17', NULL),
(27, 9, 22, '', NULL, NULL, 'I#22', 0, '2026-02-15 06:36:17', '2026-02-15 06:36:17', NULL);

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
-- Dumping data for table `user_signatures`
--

INSERT INTO `user_signatures` (`id`, `pengguna_id`, `ttd_path`, `default_width_mm`, `default_height_mm`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 10, 'private/ttd/ttd_10_1769705908.png', 35, 15, '2025-09-03 13:32:08', '2026-01-29 16:58:28', NULL),
(2, 3, 'private/ttd/3.png', 35, 15, '2025-09-14 01:26:47', '2025-09-14 01:26:47', NULL);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `keputusan_attachments`
--
ALTER TABLE `keputusan_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `keputusan_status_logs`
--
ALTER TABLE `keputusan_status_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mengingat_library`
--
ALTER TABLE `mengingat_library`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menimbang_library`
--
ALTER TABLE `menimbang_library`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `nomor_counters`
--
ALTER TABLE `nomor_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `peran`
--
ALTER TABLE `peran`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `recipient_imports`
--
ALTER TABLE `recipient_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `surat_templates`
--
ALTER TABLE `surat_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tugas_attachments`
--
ALTER TABLE `tugas_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_header`
--
ALTER TABLE `tugas_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tugas_log`
--
ALTER TABLE `tugas_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tugas_logs`
--
ALTER TABLE `tugas_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_signatures`
--
ALTER TABLE `user_signatures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
