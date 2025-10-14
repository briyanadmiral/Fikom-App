-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 13, 2025 at 04:06 PM
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
-- Database: `surat_siega`
--

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
('356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1760278887;', 1760278887),
('356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1760278887);

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
-- Table structure for table `jenis_tugas`
--

CREATE TABLE `jenis_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jenis_tugas`
--

INSERT INTO `jenis_tugas` (`id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'Bimbingan', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(2, 'Penelitian', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(3, 'Pengabdian', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(4, 'Penunjang Almamater', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(5, 'Penunjang Administrasi & Manajemen', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(6, 'Publikasi', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(7, 'TA di Luar Mengajar', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(8, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05');

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
(17, 'default', '{\"uuid\":\"3ead451a-b5b4-4d81-99fb-d1d7669090bc\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:10;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1758703523, 1758703523),
(18, 'default', '{\"uuid\":\"b613900f-8c39-4810-b6a2-5eb76ce78492\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:14;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1759464879, 1759464879),
(19, 'default', '{\"uuid\":\"800672d2-9eda-428a-a442-19197f1af981\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:15;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760179425, 1760179425),
(20, 'default', '{\"uuid\":\"9cf4a431-ff1c-43da-ac23-f6ff84a31067\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:18;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760179498, 1760179498),
(21, 'default', '{\"uuid\":\"a7b4e7f5-446b-40e6-98bb-2c6a96e5434b\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:20;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760250865, 1760250865),
(22, 'default', '{\"uuid\":\"2ec988ee-f2c9-4ee3-be03-ec84729dd7e8\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:13;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760251140, 1760251140),
(23, 'default', '{\"uuid\":\"49b1d890-f101-4657-9ebf-458409b05249\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:21;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760289519, 1760289519);

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_header`
--

CREATE TABLE `keputusan_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(100) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `tentang` varchar(255) NOT NULL,
  `menimbang` json NOT NULL DEFAULT (json_array()),
  `mengingat` json NOT NULL DEFAULT (json_array()),
  `menetapkan` json DEFAULT NULL,
  `memutuskan` longtext NOT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `tembusan` text,
  `tembusan_formatted` text,
  `penerima_eksternal` json DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui','ditolak','terbit','arsip') NOT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `penandatangan` bigint UNSIGNED DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `keputusan_header` (`id`, `nomor`, `tanggal_surat`, `signed_at`, `tentang`, `menimbang`, `mengingat`, `menetapkan`, `memutuskan`, `signed_pdf_path`, `tembusan`, `tembusan_formatted`, `penerima_eksternal`, `status_surat`, `dibuat_oleh`, `penandatangan`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `published_by`, `published_at`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'SK-TEST/001/FIKOM/2025', NULL, NULL, 'Draft: Penetapan Tim Kebersihan', '[\"Perlu penataan kebersihan area fakultas.\", \"Menjamin kenyamanan kegiatan akademik.\"]', '[\"UU 12/2012 tentang Pendidikan Tinggi\", \"Kebijakan internal Fakultas\"]', '[{\"isi\": \"<p>Laksanakan</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Laksanakan</p></p>', NULL, NULL, NULL, NULL, 'ditolak', 1, 10, NULL, NULL, 10, '2025-09-29 08:06:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-09-29 08:06:00', NULL),
(2, 'SK-TEST/002/FIKOM/2025', NULL, NULL, 'Pending: Pembentukan Panitia Webinar', '[\"Meningkatkan literasi AI bagi sivitas.\", \"Butuh kepanitiaan lintas prodi.\"]', '[\"SN Dikti terkait kegiatan akademik\", \"Keputusan Rektor tentang kegiatan kemahasiswaan\"]', '[{\"isi\": \"Membentuk panitia.\", \"judul\": \"KESATU\"}, {\"isi\": \"Masa kerja 2 bulan.\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> Membentuk panitia webinar AI.</p>\r\n   <p><strong>KEDUA:</strong> Masa kerja 2 bulan.</p>', NULL, 'Rektor; Arsip', NULL, NULL, 'pending', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', NULL),
(3, 'SK-TEST/003/FIKOM/2025', '2025-09-27', '2025-09-27 15:56:03', 'Disetujui: Penetapan Tata Tertib Laboratorium', '[\"Meningkatkan keselamatan kerja di laboratorium.\", \"Menindaklanjuti evaluasi semester lalu.\"]', '[\"Kebijakan K3 Universitas\", \"Standar Operasional Prosedur Lab\"]', '[{\"isi\": \"Menetapkan tata tertib lab.\", \"judul\": \"KESATU\"}, {\"isi\": \"Berlaku sejak tanggal ditetapkan.\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> Menetapkan tata tertib laboratorium.</p>\r\n   <p><strong>KEDUA:</strong> Berlaku sejak tanggal ditetapkan.</p>', NULL, 'Kaprodi TI; Ka. Lab', NULL, NULL, 'disetujui', 1, 10, 10, '2025-09-27 15:46:03', NULL, NULL, NULL, NULL, '{\"w_mm\": 42}', '{\"w_mm\": 35, \"opacity\": 0.95}', NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', NULL),
(4, 'SK-TEST/004/FIKOM/2025', NULL, NULL, 'Ditolak: Penetapan Subsidi Kegiatan Ekstrakurikuler', '[\"Keterbatasan anggaran.\", \"Perlu prioritas program.\"]', '[\"Pedoman Keuangan Internal\"]', NULL, '<p><strong>KESATU:</strong> Usulan skema subsidi (DITOLAK).</p>', NULL, NULL, NULL, NULL, 'ditolak', 1, 10, NULL, NULL, 3, '2025-09-26 16:16:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', NULL),
(5, 'SK-TEST/005/FIKOM/2025', '2025-09-20', '2025-09-20 00:10:00', 'Terbit: Penetapan Kurikulum 2025', '[\"Kurikulum lama perlu pemutakhiran.\", \"Rekomendasi akreditasi 2024.\"]', '[\"Permendikbud 3/2020\", \"Panduan MBKM\"]', '[{\"isi\": \"Menetapkan kurikulum 2025.\", \"judul\": \"KESATU\"}, {\"isi\": \"Berlaku semester ganjil 2025/2026.\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> Menetapkan kurikulum 2025.</p>\r\n   <p><strong>KEDUA:</strong> Berlaku mulai Semester Ganjil 2025/2026.</p>', NULL, 'Rektor; Arsip', NULL, NULL, 'arsip', 1, 10, 10, '2025-09-20 00:05:00', NULL, NULL, 1, '2025-09-21 02:00:00', '{\"w_mm\": 40}', '{\"w_mm\": 32, \"opacity\": 0.9}', NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-10-12 14:53:14', NULL),
(6, 'SK-TEST/006/FIKOM/2025', '2025-07-05', '2025-07-04 19:00:00', 'Arsip: Panitia Yudisium Juli 2025', '[\"Menjamin kelancaran yudisium.\", \"Kebutuhan kepanitiaan.\"]', '[\"Kalender Akademik 2025\", \"Pedoman Akademik Fakultas\"]', '[{\"isi\": \"Menetapkan panitia yudisium.\", \"judul\": \"KESATU\"}, {\"isi\": \"Tugas & wewenang ada di lampiran.\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> Menetapkan panitia yudisium Juli 2025.</p>\r\n   <p><strong>KEDUA:</strong> Tugas dan wewenang terlampir.</p>', NULL, 'BAAK; Arsip', NULL, NULL, 'arsip', 1, 10, 10, '2025-07-04 18:50:00', NULL, NULL, 1, '2025-07-05 20:00:00', '{\"w_mm\": 42}', '{\"w_mm\": 35, \"opacity\": 0.85}', NULL, NULL, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', NULL),
(7, '001/B.10.1/TG/UNIKA/IX/2025', NULL, NULL, 'Test', '[\"Test\"]', '[\"Test\", \"hasi\"]', '[{\"isi\": \"<p>twe</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>test</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> <p>twe</p></p>\n<p><strong>KEDUA:</strong> <p>test</p></p>', NULL, NULL, NULL, NULL, 'draft', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-28 07:39:22', '2025-09-28 08:04:12', NULL),
(8, '2644/F.1.2/FIKOM/XI/2023', '2025-10-03', '2025-10-02 19:19:53', 'Penetapan Visi, Misi, Tujuan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dan seluruh Program Studi yang bernaung di bawahnya', '[\"bahwa Fakultas Ilmu Komputer menaungi 2 (dua) program studi, yaitu Teknik Informatika dan Sistem Informasi sejak 25 Juni 2013 dengan kekhasan dan sumber daya masing-masing;\", \"bahwa Fakultas Ilmu Komputer memerlukan media untuk menyatakan tujuan, arah serta sasaran sebagai landasan program studi guna memanfaatkan dan mengalokasikan sumber daya yang mereka miliki beserta proses pengendaliannya serta untuk membentuk serta membangun budaya institusi;\", \"bahwa berdasarkan keputusan Rapat Senat Fakultas Ilmu Komputer pada tanggal 31 Oktober 2023 yang menetapkan perlunya penyesuaian dan peninjauan Visi dan Misi Fakultas Ilmu Komputer dan seluruh Program Studi yang bernaung di bawahnya serta keputusan Rapat Kerja Fakultas Ilmu Komputer Tahun 2023;\", \"bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a, huruf b, dan huruf c, perlu diterbitkan Surat Keputusan Dekan Fakultas Ilmu Komputer tentang Visi dan Misi Fakultas Ilmu Komputer;\"]', '[\"Undang-Undang No. 20 tahun 2013 tentang Pendidikan Tinggi;\", \"Undang-Undang Republik Indonesia Nomor 12 Tahun 2012 tentang Pendidikan Tinggi;\", \"Peraturan Pemerintah No. 14 tahun 2014 tentang Penyelenggaraan Pendidikan Tinggi dan Pengelolaan Perguruan Tinggi;\", \"Keputusan Yayasan Sandjojo No. 66/PER/YS/05/VII/2013 tentang Statuta Universitas Katolik Soegijapranata;\", \"Peraturan Universitas No. E.2/1616/UKS.01/VII/2001 tentang Organisasi dan Tata Laksana Universitas Katolik Soegijapranata;\"]', '[{\"isi\": \"<p>KEPUTUSAN DEKAN TENTANG PENETAPAN VISI, MISI, DAN TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Misi Fakultas Ilmu Komputer adalah sebagai berikut:</p>\\r\\n\\r\\n<ol><li>Menyelenggarakan kegiatan pendidikan yang bermutu, terencana, dan konsisten secara akademis dalam lingkungan yang mendukung pengembangan versi terbaik dari masing-masing pribadi di masyarakat.</li><li>Melakukan penelitian untuk mengembangkan Teknologi Informasi terkini yang sesuai dengan kebutuhan masyarakat dan ilmu pengetahuan.</li><li>Menerapkan Teknologi Informasi dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Menjalin kerjasama dengan berbagai instansi untuk meningkatkan kualitas Tri Dharma Perguruan Tinggi.</li></ol>\", \"judul\": \"KEDUA\"}, {\"isi\": \"<p>Tujuan Fakultas Ilmu Komputer adalah sebagai berikut:</p>\\r\\n\\r\\n<ol><li>Menghasilkan lulusan yang jujur, adaptif, kreatif, dan peduli kepada masyarakat melalui kompetensinya di bidang Teknologi Informasi.</li><li>Mewujudkan mutu pendidikan yang paripurna berdasar pada standar nasional pendidikan.</li><li>Menghasilkan penelitian di bidang Teknologi Informasi yang bermanfaat bagi masyarakat dan mampu bersaing di tingkat nasional dan internasional.</li><li>Menghasilkan publikasi ilmiah dalam bidang teknologi informasi yang dapat meningkatkan kapasitas dosen, mahasiswa, dan masyarakat di tingkat nasional dan internasional.</li><li>Menerapkan Teknologi Informasi yang dapat menjadi solusi atas kebutuhan-kebutuhan masyarakat dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Berjejaring dengan institusi pendidikan, industri dan pemerintah untuk meningkatkan kualitas pendidikan, penelitian dan pengabdian di bidang Teknologi Informasi.</li></ol>\", \"judul\": \"KETIGA\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEEMPAT\"}]', '<p><strong>KESATU:</strong> <p>KEPUTUSAN DEKAN TENTANG PENETAPAN VISI, MISI, DAN TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA.</p></p>\n<p><strong>KEDUA:</strong> <p>Misi Fakultas Ilmu Komputer adalah sebagai berikut:</p>\r\n\r\n<ol><li>Menyelenggarakan kegiatan pendidikan yang bermutu, terencana, dan konsisten secara akademis dalam lingkungan yang mendukung pengembangan versi terbaik dari masing-masing pribadi di masyarakat.</li><li>Melakukan penelitian untuk mengembangkan Teknologi Informasi terkini yang sesuai dengan kebutuhan masyarakat dan ilmu pengetahuan.</li><li>Menerapkan Teknologi Informasi dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Menjalin kerjasama dengan berbagai instansi untuk meningkatkan kualitas Tri Dharma Perguruan Tinggi.</li></ol></p>\n<p><strong>KETIGA:</strong> <p>Tujuan Fakultas Ilmu Komputer adalah sebagai berikut:</p>\r\n\r\n<ol><li>Menghasilkan lulusan yang jujur, adaptif, kreatif, dan peduli kepada masyarakat melalui kompetensinya di bidang Teknologi Informasi.</li><li>Mewujudkan mutu pendidikan yang paripurna berdasar pada standar nasional pendidikan.</li><li>Menghasilkan penelitian di bidang Teknologi Informasi yang bermanfaat bagi masyarakat dan mampu bersaing di tingkat nasional dan internasional.</li><li>Menghasilkan publikasi ilmiah dalam bidang teknologi informasi yang dapat meningkatkan kapasitas dosen, mahasiswa, dan masyarakat di tingkat nasional dan internasional.</li><li>Menerapkan Teknologi Informasi yang dapat menjadi solusi atas kebutuhan-kebutuhan masyarakat dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Berjejaring dengan institusi pendidikan, industri dan pemerintah untuk meningkatkan kualitas pendidikan, penelitian dan pengabdian di bidang Teknologi Informasi.</li></ol></p>\n<p><strong>KEEMPAT:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', 'private/surat_keputusan/signed/8_7ae37f64de56414ff6f683ee2fd8876e.pdf', NULL, NULL, NULL, 'disetujui', 1, 10, 10, '2025-10-02 19:19:53', NULL, NULL, NULL, NULL, NULL, NULL, 37, 37, 0.95, '2025-09-29 04:34:22', '2025-10-02 19:19:58', NULL),
(13, 'UT-SK-PEN-001/FIKOM/2025', NULL, NULL, 'UJI: Revisi oleh penandatangan saat pending', '[\"Alasan A (direvisi)\", \"Alasan B\", \"Alasan C (baru)\"]', '[\"Dasar 1\", \"Dasar 2\", \"Dasr 3\"]', '[{\"isi\": \"<p>Lakukan A</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Lakukan A</p></p>', NULL, NULL, NULL, NULL, 'pending', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-02 17:59:50', '2025-10-02 18:06:07', NULL),
(14, 'UT-SK-APP-001/FIKOM/2025', '2025-10-03', '2025-10-02 17:59:50', 'UJI: Approve SK', '[\"Test\"]', '[\"Dasar Approve 1\"]', '[{\"isi\": \"<p>Approve tugas X</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Approve tugas X</p></p>', NULL, 'Test', 'Tembusan Yth:\r\n1. Test', NULL, 'draft', 1, 10, 10, '2025-10-02 17:59:50', NULL, NULL, NULL, NULL, '{\"w_mm\": 42}', '{\"w_mm\": 35, \"opacity\": 0.95}', NULL, NULL, NULL, '2025-10-02 17:59:50', '2025-10-07 11:26:52', NULL),
(15, 'UT-SK-REJ-001/FIKOM/2025', NULL, NULL, 'UJI: Reject SK', '[\"Alasan perlu revisi\"]', '[\"Dasar X\"]', NULL, '<p><strong>KESATU:</strong> Draft awal (butuh perbaikan).</p>', NULL, NULL, NULL, NULL, 'ditolak', 1, 10, NULL, NULL, 10, '2025-10-02 17:59:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-02 17:59:50', '2025-10-02 17:59:50', NULL),
(16, '001/B.10.1/SK/UNIKA/FIKOM/X/2025', NULL, NULL, 'UJI: Publish SK', '[\"Sudah final\"]', '[\"Dasar lengkap\"]', NULL, '<p><strong>KESATU:</strong> Berlaku sejak ditetapkan.</p>', NULL, NULL, NULL, NULL, 'arsip', 1, 10, 10, '2025-10-02 17:59:51', NULL, NULL, 1, '2025-10-02 17:59:51', NULL, NULL, NULL, NULL, NULL, '2025-10-02 17:59:50', '2025-10-02 17:59:51', NULL),
(17, '001/B.10.1/TG/UNIKA/X/2025', '2025-10-12', NULL, 'Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025', '[\"bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2025 diperlukan pembentukan panitia pelaksana\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\"]', '[{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p></p>', NULL, '[{\"value\":\"Yth. Rektor\"}]', NULL, '[]', 'pending', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-12 14:20:13', '2025-10-12 14:57:41', NULL);

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
  `dibaca` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keputusan_penerima`
--

INSERT INTO `keputusan_penerima` (`id`, `keputusan_id`, `pengguna_id`, `read_at`, `created_at`, `updated_at`, `dibaca`) VALUES
(3, 2, 5, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', 0),
(4, 2, 6, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', 0),
(5, 3, 5, '2025-09-27 16:06:03', '2025-09-27 16:16:03', '2025-09-27 16:16:03', 1),
(6, 3, 6, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', 0),
(7, 4, 5, NULL, '2025-09-27 16:16:03', '2025-09-27 16:16:03', 0),
(8, 5, 5, '2025-09-21 03:00:00', '2025-09-27 16:16:03', '2025-09-27 16:16:03', 1),
(9, 5, 6, '2025-09-21 03:05:00', '2025-09-27 16:16:03', '2025-09-27 16:16:03', 1),
(10, 6, 5, '2025-07-05 21:00:00', '2025-09-27 16:16:03', '2025-09-27 16:16:03', 1),
(11, 6, 6, '2025-07-05 21:05:00', '2025-09-27 16:16:03', '2025-09-27 16:16:03', 1),
(20, 7, 11, NULL, '2025-09-28 08:04:12', '2025-09-28 08:04:12', 0),
(21, 7, 13, NULL, '2025-09-28 08:04:12', '2025-09-28 08:04:12', 0),
(24, 1, 6, NULL, '2025-09-29 03:12:51', '2025-09-29 03:12:51', 0),
(25, 1, 5, NULL, '2025-09-29 03:12:51', '2025-09-29 03:12:51', 0),
(30, 16, 5, NULL, '2025-10-02 17:59:51', '2025-10-02 17:59:51', 0),
(31, 16, 6, NULL, '2025-10-02 17:59:51', '2025-10-02 17:59:51', 0),
(43, 8, 5, NULL, '2025-10-02 18:56:40', '2025-10-02 18:56:40', 0),
(74, 14, 6, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(75, 14, 7, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(76, 14, 8, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(77, 14, 9, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(78, 14, 11, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(79, 14, 12, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(80, 14, 13, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(81, 14, 15, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(82, 14, 17, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0),
(83, 14, 22, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_surat`
--

CREATE TABLE `klasifikasi_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `kode` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `klasifikasi_surat`
--

INSERT INTO `klasifikasi_surat` (`id`, `kode`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'A.4', 'Program Terpadu Mahasiswa Baru (PTMB)', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(2, 'A.4.--', 'Program Terpadu Mahasiswa Baru (PTMB)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(3, 'B.1.1', 'Penawaran Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(4, 'B.1.2', 'Jadwal Kuliah (revisi/pengganti/tambahan)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(5, 'B.1.3', 'Pembatalan Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(6, 'B.1.4', 'Pengisian KRS', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(7, 'B.1.5', 'Kuliah Umum', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(8, 'B.1.6', 'Awal Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(9, 'B.1.7', 'Penugasan Perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(10, 'B.1.8', 'Praktikum/Laboratorium', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(11, 'B.1.9', 'Kuliah Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(12, 'B.1.10', 'Akhir Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(13, 'B.1.11', 'Pekan Teduh', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(14, 'B.1.12', 'Libur Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(15, 'B.1.13', 'Angket evaluasi perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(16, 'B.2.1', 'Ujian Tengah Semester (UTS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(17, 'B.2.2', 'Ujian Akhir Semester (UAS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(18, 'B.2.3', 'Ujian Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(19, 'B.2.4', 'Ujian Susulan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(20, 'B.2.5', 'Ujian Pembekalan KKN/KKU/KAPKI', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(21, 'B.2.6', 'Ujian Kertas Karya', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(22, 'B.2.7', 'Ujian Kerja Praktek/Seminar/Proposal/Draf', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(23, 'B.2.8', 'Ujian Skripsi/Pendadaran/Ujian Tahap Akhir/Proyek', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(24, 'B.2.9', 'Ujian Tesis', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(25, 'B.2.10', 'Ujian Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(26, 'B.3.1', 'Pendaftaran KKN/KKU/KAPKI/KKUKerja Praktek/Kertas Karya/Skripsi/Tesis/Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(27, 'B.3.2', 'Peninjauan/Survey/Data', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(28, 'B.3.3', 'Perijinan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(29, 'B.3.4', 'Pembekalan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(30, 'B.3.5', 'Bimbingan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(31, 'B.3.6', 'Pembatalan/Gugur', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(32, 'B.3.7', 'Perpanjangan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(33, 'B.3.8', 'Perintah Kerja', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(34, 'B.4.1', 'Evaluasi semesteran', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(35, 'B.4.2', 'Evaluasi tahunan/Jumlah SKS yang telah ditempuh', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(36, 'B.4.3', 'Peringatan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(37, 'B.4.4', 'Perpanjangan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(38, 'B.4.5', 'Sanksi akademik', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(39, 'B.4.6', 'Pemberhentian Status Mahasiswa (DO)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(40, 'B.5.1', 'Pindah Fakultas/Program Studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(41, 'B.5.2', 'Pindah dari Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(42, 'B.5.3', 'Pindah ke Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(43, 'B.5.4', 'Mengundurkan diri', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(44, 'B.6.1', 'Mohon Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(45, 'B.6.2', 'Kirim Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(46, 'B.6.3', 'Revisi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(47, 'B.6.4', 'Hapus Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(48, 'B.6.5', 'Konversi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(49, 'B.6.6', 'Yudisium (Penentuan Nilai Lulus Ujian Sarjana)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(50, 'B.6.7', 'Hasil Studi (KHS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(51, 'B.6.8', 'Daftar Kumpulan Nilai (Transkrip)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(52, 'B.6.9', 'Pedoman Penilaian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(53, 'B.7.1', 'Informasi/Penawaran Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(54, 'B.7.2', 'Tim Peneliti/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(55, 'B.7.3', 'Ijin Penelitian/Survey/Data', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(56, 'B.7.4', 'Usulan Proyek Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(57, 'B.7.5', 'Review/Revisi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(58, 'B.7.6', 'Laporan Hasil Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(59, 'B.7.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Penelitian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(60, 'B.7.8', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(61, 'B.7.9', 'Penulisan Ilmiah/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(62, 'B.8.1', 'Informasi/Penawaran Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(63, 'B.8.2', 'Tim Pengabdian/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(64, 'B.8.3', 'Ijin Kegiatan Pengabdian/Survey/Data', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(65, 'B.8.4', 'Usulan Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(66, 'B.8.5', 'Review/Revisi/Presentasi Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(67, 'B.8.6', 'Laporan Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(68, 'B.8.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Kegiatan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(69, 'B.8.8', 'Ceramah/Bimbingan/Penyuluhan/Pelatihan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(70, 'B.8.9', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(71, 'B.8.10', 'Penulisan Ilmiah/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(72, 'B.9.1', 'Penetapan Keputusan (SK Kelulusan)', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(73, 'B.9.2', 'Lulusan Terbaik/Tercepat', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(74, 'B.9.3', 'Keterangan Lulus', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(75, 'B.9.4', 'Wisuda/Pelepasan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(76, 'B.9.5', 'Ijazah/Bukti Kelulusan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(77, 'B.9.6', 'Legalisasi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(78, 'B.9.7', 'Keterangan Pengganti Ijazah', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(79, 'B.9.8', 'Penggunaan Gelar', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(80, 'B.9.9', 'Kartu Alumni', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(81, 'B.10.1', 'Pengadaan Buku/Jurnal', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(82, 'B.10.2', 'Pengolahan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(83, 'B.10.3', 'Peminjaman', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(84, 'B.10.4', 'Tagihan Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(85, 'B.10.5', 'Bedah/Resensi Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(86, 'B.10.6', 'Pelatihan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(87, 'B.10.7', 'Pameran Buku/Bursa Buku', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(88, 'B.10.8', 'Koleksi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(89, 'B.10.9', 'Sumbangan Koleksi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(90, 'B.10.10', 'Stock Opname', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(91, 'B.10.11', 'Statistik', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(92, 'B.10.12', 'Tata Tertib', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(93, 'B.10.13', 'Keanggotaan', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(94, 'B.11.1', 'Kalender Akademik', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(95, 'B.12.1', 'Dispensasi (Perkuliahan/Tugas/Praktikum/Pengisian KRS)', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(96, 'B.13.1', 'Heregistrasi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(97, 'B.13.2', 'Aktif Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(98, 'B.13.3', 'Mahasiswa Asing/Pendengar', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(99, 'B.13.4', 'Cuti Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(100, 'B.13.5', 'Sedang Skripsi', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(101, 'B.13.6', 'Pernah Kuliah', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(102, 'B.13.7', 'Double Degree', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(103, 'B.13.8', 'Penyerahan ijazah sma/smk/paket c', '2025-08-02 15:41:34', '2025-10-11 05:21:41'),
(104, 'A.1.1', 'Promosi', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(105, 'A.1.2', 'Open House', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(106, 'A.1.3', 'Pameran', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(107, 'A.1.4', 'Kunjungan/Safari ke SMU', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(108, 'A.1.5', 'Pertandingan/Lomba antar SMU', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(109, 'A.1.6', 'Diskusi/Seminar/Ceramah/Dialog', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(110, 'A.2.1', 'Jalur PMDK', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(111, 'A.2.2', 'Jalur Kerja Sama', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(112, 'A.2.3', 'Reguler/Umum', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(113, 'A.2.4', 'Materi/Soal Tes', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(114, 'A.2.5', 'Tester/Pengawas', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(115, 'A.2.6', 'Koordinasi Tugas', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(116, 'A.3.1', 'Pengumuman Tes', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(117, 'A.3.2', 'Ketetapan Diterima', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(118, 'A.3.3', 'Hasil Seleksi', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(119, 'A.3.4', 'Registrasi', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(120, 'C.1.1', 'Karya Ilmiah Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(121, 'C.1.2', 'Diskusi/Konferensi/Dialog Ilmiah Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(122, 'C.1.3', 'Simposium', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(123, 'C.2.1', 'Beasiswa Yayasan Sandjojo', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(124, 'C.2.2', 'Beasiswa Swasta (KWI, Djarum, Bank, dll)', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(125, 'C.2.3', 'Beasiswa Pemerintah (Supersemar, Dikti, Kopertis)', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(126, 'C.2.4', 'Beasiswa Luar Negeri', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(127, 'C.3.1', 'Pertukaran Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(128, 'C.3.2', 'Mahasiswa Berprestasi/Mahasiswa Teladan', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(129, 'C.3.3', 'Pertandingan/Kompetisi', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(130, 'C.3.4', 'Pentas Seni/Musik Festival', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(131, 'C.3.5', 'Pelatihan Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(132, 'C.3.6', 'Kemah Bhakti', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(133, 'C.3.7', 'Duta/Utusan/Perwakilan Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41'),
(134, 'C.4', 'Pelatihan Pengembangan Kepribadian Mahasiswa', '2025-10-11 05:21:41', '2025-10-11 05:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `master_kop_surat`
--

CREATE TABLE `master_kop_surat` (
  `id` bigint UNSIGNED NOT NULL,
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
  `judul_atas` varchar(255) DEFAULT NULL,
  `subjudul` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_kiri_path` varchar(255) DEFAULT NULL,
  `logo_kanan_path` varchar(255) DEFAULT NULL,
  `tampilkan_logo_kiri` tinyint(1) NOT NULL DEFAULT '0',
  `tampilkan_logo_kanan` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_kop_surat`
--

INSERT INTO `master_kop_surat` (`id`, `unit`, `background_path`, `cap_path`, `cap_default_width_mm`, `cap_opacity`, `cap_offset_x_mm`, `cap_offset_y_mm`, `updated_by`, `created_at`, `updated_at`, `mode`, `mode_type`, `nama_fakultas`, `alamat_lengkap`, `telepon_lengkap`, `email_website`, `text_align`, `logo_size`, `font_size_title`, `font_size_text`, `text_color`, `header_padding`, `background_opacity`, `judul_atas`, `subjudul`, `alamat`, `telepon`, `fax`, `email`, `website`, `logo_kiri_path`, `logo_kanan_path`, `tampilkan_logo_kiri`, `tampilkan_logo_kanan`) VALUES
(1, NULL, NULL, 'kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png', 30, 85, 0, 0, 1, '2025-08-26 03:50:41', '2025-10-13 16:05:49', 'composed', 'custom', 'FAKULTAS ILMU KOMPUTER', 'Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234', 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265', 'e-mail: unika@unika.ac.id http://www.unika.ac.id/', 'right', 160, 19, 12, '#000000', 5, 100, 'SOEGIJAPRANATA', 'CATHOLIC UNIVERSITY', 'Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234', '(024) 8441555, 85050003', '(024) 8415429 – 8454265', 'unika@unika.ac.id', 'https://www.unika.ac.id', NULL, 'kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg', 0, 1);

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
(40, '2025_10_12_202432_remove_tanggal_asli_from_keputusan_header_table', 23);

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nomor_counters`
--

INSERT INTO `nomor_counters` (`id`, `tipe`, `tahun`, `prefix`, `last_number`, `updated_at`) VALUES
(1, 'SK', 2025, 'B.10.1/SK/UNIKA', 1, '2025-10-02 17:59:51');

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat_counters`
--

CREATE TABLE `nomor_surat_counters` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_surat` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `bulan_romawi` varchar(255) NOT NULL,
  `tahun` int NOT NULL,
  `last_number` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nomor_surat_counters`
--

INSERT INTO `nomor_surat_counters` (`id`, `kode_surat`, `unit`, `bulan_romawi`, `tahun`, `last_number`, `created_at`, `updated_at`) VALUES
(1, 'B.10.1', 'TG', 'IX', 2025, 4, '2025-09-27 17:41:23', '2025-09-28 08:08:49'),
(2, 'B.10.1', 'TG', 'X', 2025, 6, '2025-10-09 16:35:54', '2025-10-12 14:20:27'),
(3, 'B.10.1', 'ST.IKOM', 'X', 2025, 3, '2025-10-09 18:58:58', '2025-10-09 18:59:19'),
(4, 'C.3.5', 'ST.IKOM', 'X', 2025, 1, '2025-10-11 07:09:19', '2025-10-11 07:09:19'),
(5, 'B.1.10', 'ST.IKOM', 'X', 2025, 2, '2025-10-11 16:32:58', '2025-10-11 16:34:05'),
(6, 'A.1.5', 'ST.IKOM', 'X', 2025, 4, '2025-10-12 05:30:08', '2025-10-12 06:49:25');

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
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `pengguna_id`, `tipe`, `referensi_id`, `pesan`, `dibaca`, `created_at`, `updated_at`, `dibuat_pada`) VALUES
(1, 3, 'surat_tugas', 9, 'Surat Tugas 001/B.10.6/TG/UNIKA/IX/2025 menunggu persetujuan Anda.', 0, '2025-09-13 23:47:54', '2025-09-13 23:47:54', '2025-09-13 23:47:54'),
(2, 10, 'surat_tugas', 7, 'Surat Tugas 006/B.3.5/TG/UNIKA/VIII/2025 menunggu persetujuan Anda.', 0, '2025-09-14 06:55:01', '2025-09-14 06:55:01', '2025-09-14 06:55:01'),
(3, 1, 'surat_tugas', 11, 'Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025 telah disetujui.', 1, '2025-09-14 22:34:13', '2025-10-12 08:48:06', '2025-09-14 22:34:13'),
(4, 16, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13'),
(5, 17, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13'),
(6, 18, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13'),
(15, 1, 'surat_tugas', 10, 'Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025 telah disetujui.', 1, '2025-09-24 08:45:23', '2025-10-12 08:48:06', '2025-09-24 08:45:23'),
(16, 8, 'surat_tugas', 10, 'Anda terdaftar sebagai penerima pada Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025.', 0, '2025-09-24 08:45:23', '2025-09-24 08:45:23', '2025-09-24 08:45:23'),
(17, 9, 'surat_tugas', 10, 'Anda terdaftar sebagai penerima pada Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025.', 0, '2025-09-24 08:45:29', '2025-09-24 08:45:29', '2025-09-24 08:45:29'),
(18, 10, 'surat_keputusan', 1, 'Surat Keputusan SK-TEST/001/FIKOM/2025 menunggu persetujuan Anda.', 0, '2025-09-29 03:12:51', '2025-09-29 03:12:51', '2025-09-29 03:12:51'),
(19, 10, 'surat_keputusan', 8, 'Surat Keputusan 2644/F.1.2/FIKOM/XI/2023 menunggu persetujuan Anda.', 0, '2025-09-29 04:34:22', '2025-09-29 04:34:22', '2025-09-29 04:34:22'),
(20, 1, 'surat_keputusan', 1, 'Surat Keputusan SK-TEST/001/FIKOM/2025 ditolak. Catatan: Kurang jelas', 1, '2025-09-29 08:06:00', '2025-10-12 08:48:06', '2025-09-29 08:06:00'),
(21, 1, 'surat_keputusan', 10, 'Surat Keputusan UT-SK-APP-001/FIKOM/2025 telah disetujui.', 1, '2025-10-02 17:58:21', '2025-10-12 08:48:06', '2025-10-02 17:58:21'),
(22, 1, 'surat_keputusan', 11, 'Surat Keputusan UT-SK-REJ-001/FIKOM/2025 ditolak. Catatan: Kurang jelas pada bagian dasar hukum.', 1, '2025-10-02 17:58:21', '2025-10-12 08:48:06', '2025-10-02 17:58:21'),
(23, 1, 'surat_keputusan', 14, 'Surat Keputusan UT-SK-APP-001/FIKOM/2025 telah disetujui.', 1, '2025-10-02 17:59:50', '2025-10-12 08:48:06', '2025-10-02 17:59:50'),
(24, 1, 'surat_keputusan', 15, 'Surat Keputusan UT-SK-REJ-001/FIKOM/2025 ditolak. Catatan: Kurang jelas pada bagian dasar hukum.', 1, '2025-10-02 17:59:50', '2025-10-12 08:48:06', '2025-10-02 17:59:50'),
(25, 10, 'surat_keputusan', 13, 'SK UT-SK-PEN-001/FIKOM/2025 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 0, '2025-10-02 18:06:07', '2025-10-02 18:06:07', '2025-10-02 18:06:07'),
(26, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 0, '2025-10-02 18:45:29', '2025-10-02 18:45:29', '2025-10-02 18:45:29'),
(27, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 0, '2025-10-02 18:45:31', '2025-10-02 18:45:31', '2025-10-02 18:45:31'),
(28, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 0, '2025-10-02 18:45:43', '2025-10-02 18:45:43', '2025-10-02 18:45:43'),
(29, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 0, '2025-10-02 18:54:17', '2025-10-02 18:54:17', '2025-10-02 18:54:17'),
(30, 1, 'surat_keputusan', 8, 'Surat Keputusan 2644/F.1.2/FIKOM/XI/2023 telah disetujui.', 1, '2025-10-02 19:19:58', '2025-10-12 08:48:06', '2025-10-02 19:19:58'),
(31, 5, 'surat_keputusan', 8, 'Anda mendapat tembusan Surat Keputusan 2644/F.1.2/FIKOM/XI/2023.', 0, '2025-10-02 19:19:58', '2025-10-02 19:19:58', '2025-10-02 19:19:58'),
(32, 10, 'surat_tugas', 14, 'Surat Tugas 001/B.3.5/TG/UNIKA/X/2025 menunggu persetujuan Anda.', 0, '2025-10-03 02:58:07', '2025-10-03 02:58:07', '2025-10-03 02:58:07'),
(33, 1, 'surat_tugas', 14, 'Surat Tugas 001/B.3.5/TG/UNIKA/X/2025 telah disetujui.', 1, '2025-10-03 04:14:39', '2025-10-12 08:48:04', '2025-10-03 04:14:39'),
(34, 13, 'surat_tugas', 14, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.3.5/TG/UNIKA/X/2025.', 0, '2025-10-03 04:14:39', '2025-10-03 04:14:39', '2025-10-03 04:14:39'),
(35, 14, 'surat_tugas', 14, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.3.5/TG/UNIKA/X/2025.', 0, '2025-10-03 04:14:46', '2025-10-03 04:14:46', '2025-10-03 04:14:46'),
(36, 3, 'surat_keputusan', 17, 'SK 001/B.10.1/TG/UNIKA/X/2025 ditarik ke Draft oleh AGUSTINA ALAM ANGGITASARI, SE., MM.', 0, '2025-10-12 14:33:25', '2025-10-12 14:33:25', '2025-10-12 14:33:25');

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
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `email`, `sandi_hash`, `nama_lengkap`, `npp`, `jabatan`, `peran_id`, `status`, `created_at`, `updated_at`, `last_activity`, `deleted_at`, `remember_token`) VALUES
(1, 'agustina.anggitasari@unika.ac.id', '$2y$12$0rYDf0RqcBpaABHw3vaOxe3LV6UxLazy9R85vBmmwA8juagm6Xadq', 'AGUSTINA ALAM ANGGITASARI, SE., MM', NULL, 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2025-10-13 16:05:52', '2025-10-13 23:05:52', NULL, 'WBYSiVSHoadXLldvCmYf1OoydDCX2uFCyxVWbmFTgUtUnO8fBdxLk5HfgTzJ'),
(2, 'kariyani.spd@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'KARIYANI, S.Pd', NULL, 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2025-08-01 23:28:10', NULL, NULL, NULL),
(3, 'bernhardinus.harnadi@unika.ac.id', '$2y$12$rr.ntE7OagwdG25kLxSLwOnZwIaq72oImrbM8jXOkn6AEM62QRIY2', 'Prof. BERNARDINUS HARNADI, ST., MT., Ph.D.', NULL, NULL, 3, 'aktif', '2025-04-22 03:15:27', '2025-10-04 08:46:59', '2025-10-04 15:46:59', NULL, NULL),
(4, 'muh.khudori@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'MUH KHUDORI', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:31', NULL, NULL, NULL),
(5, 'paulus.sapto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'PAULUS SAPTO NUGROHO', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:36', NULL, NULL, NULL),
(6, 'bambang.setiawan@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'BAMBANG SETIAWAN, ST', NULL, NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:39', NULL, NULL, NULL),
(7, 'erdhi.nugroho@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ERDHI WIDYARTO NUGROHO, ST., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:43', NULL, NULL, NULL),
(8, 'fx.hendra@unika.ac.id', '$2y$12$yzabxjVXeAkmIgQmvCQBuOwjSU1cUnGqowliut934gE1bnbNMZ9M.', 'FX. HENDRA PRASETYA, ST, MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-10-11 17:50:27', '2025-10-12 00:50:27', NULL, NULL),
(9, 'tecla.chandrawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. TECLA BRENDA CHANDRAWATI, S.T., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:51', NULL, NULL, NULL),
(10, 'ridwan.sanjaya@unika.ac.id', '$2y$12$VRlxXvgiT0gdC3mVx0vp6Oct3Q/VPnmvACYjDz3n.DKotAIkG1QrS', 'Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC', '058.1.2002.255', 'Dekan Fakultas Ilmu Komputer', 2, 'aktif', '2025-04-22 03:15:27', '2025-10-11 17:45:55', '2025-10-12 00:45:55', NULL, NULL),
(11, 'alb.dwiw@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ALBERTUS DWIYOGA WIDIANTORO, S.Kom., M.Kom', NULL, NULL, 4, 'aktif', '2025-04-22 03:15:27', NULL, NULL, NULL, NULL),
(12, 'agus.cahyo@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'AGUS CAHYO NUGROHO, S.Kom., M.T', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:02', NULL, NULL, NULL),
(13, 'andre.pamudji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ANDRE KURNIAWAN PAMUDJI, S.Kom., M.Ling', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:10', NULL, NULL, NULL),
(14, 'stephan.swastini@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'STEPHAN INGRITT SWASTINI DEWI, S.Kom., MBA', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:13', NULL, NULL, NULL),
(15, 'hironimus.leong@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HIRONIMUS LEONG, S.Kom., M.Com', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:15', NULL, NULL, NULL),
(16, 'rosita.herawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ROSITA HERAWATI, ST., MT', NULL, NULL, 4, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:41:03', NULL, NULL, NULL),
(17, 'yulianto.putranto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. YULIANTO TEDJO PUTRANTO, ST., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:21', NULL, NULL, NULL),
(18, 'shinta.wahyuningrum@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'SHINTA ESTRI WAHYUNINGRUM, S.Si., M.Cs', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:25', NULL, NULL, NULL),
(19, 'setiawan.aji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'R. SETIAWAN AJI NUGROHO, ST. M.CompIT, Ph.D', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:28', NULL, NULL, NULL),
(20, 'dwi.setianto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Y.B. DWI SETIANTO, ST., M.Cs(CCNA)', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:31', NULL, NULL, NULL),
(21, 'yonathan.santosa@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'YONATHAN PURBO SANTOSA, S.Kom., M.Sc', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:47', NULL, NULL, NULL),
(22, 'henoch.christanto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HENOCH JULI CHRISTANTO, S.Kom., M.Kom', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:52', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `peran`
--

CREATE TABLE `peran` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peran`
--

INSERT INTO `peran` (`id`, `nama`, `deskripsi`, `dibuat_pada`) VALUES
(1, 'admin_tu', 'Administrator Tata Usaha Fakultas', '2025-06-01 22:53:10'),
(2, 'dekan', 'Dekan Fakultas', '2025-06-01 22:53:10'),
(3, 'wakil_dekan', 'Wakil Dekan Fakultas', '2025-06-01 22:53:10'),
(4, 'kaprodi', 'Kepala Program Studi', '2025-06-01 22:53:10'),
(5, 'dosen', 'Dosen Pengajar', '2025-08-02 16:38:34'),
(6, 'tendik', 'Tenaga Kependidikan', '2025-08-02 16:38:34');

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
('jamDesTcGU8Bjw1S6YcHo175PZcAggTxeX6Xlpdg', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiclBMcXFxeEVxdWNqUGllUzU1STZJWG5MSFBVeUU2M0ZuNk16cWFLayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3BlbmdhdHVyYW4va29wLXN1cmF0Ijt9fQ==', 1760371552);

-- --------------------------------------------------------

--
-- Table structure for table `sub_tugas`
--

CREATE TABLE `sub_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `jenis_tugas_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sub_tugas`
--

INSERT INTO `sub_tugas` (`id`, `jenis_tugas_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 1, 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, NULL),
(2, 1, 'Koordinator MK', NULL, NULL),
(3, 1, 'Koordinator Tugas MK', NULL, NULL),
(4, 1, 'Bimbingan Mahasiswa/Akademik', NULL, NULL),
(5, 7, 'Pendampingan dosen dalam KKL', NULL, NULL),
(6, 1, 'Koordinator Kerja Praktik/KKL', NULL, '2025-10-11 05:28:34'),
(7, 5, 'Reviewer Kenaikan Jabatan Fungsional Lektor Kepala', NULL, NULL),
(8, 5, 'Reviewer Kenaikan Jabatan Fungsional Guru Besar', NULL, NULL),
(9, 5, 'Reviewer Kenaikan Jabatan Fungsional Asisten Ahli', NULL, NULL),
(10, 5, 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, NULL),
(11, 5, 'Asesor BKD', NULL, NULL),
(12, 5, 'Validator BKD', NULL, NULL),
(13, 3, 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, NULL),
(14, 6, 'Reviewer Jurnal Nasional', NULL, NULL),
(15, 6, 'Reviewer Jurnal Internasional', NULL, NULL),
(16, 8, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05'),
(90, 1, 'Pembimbing Akademik (PA)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(91, 1, 'Pembimbing Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(92, 1, 'Penguji Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(93, 1, 'Pembimbing Kerja Praktik', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(94, 2, 'Ketua Penelitian (Internal/Eksternal)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(95, 2, 'Anggota Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(96, 2, 'Penyusun Proposal Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(97, 3, 'Ketua Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(98, 3, 'Anggota Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(99, 3, 'Narasumber/Pemateri Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(100, 4, 'Panitia Kegiatan Fakultas/Prodi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(101, 4, 'Pembina/Koordinator UKM/Komunitas', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(102, 4, 'Pembicara Tamu/Kuliah Umum', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(103, 5, 'Sekretaris/Koordinator Program Studi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(104, 5, 'Panitia Seleksi/Asesor Internal', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(105, 5, 'Pengembang Kurikulum/Perangkat Akademik', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(106, 6, 'Editor/Section Editor Jurnal', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(107, 6, 'Pemakalah Seminar Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(108, 6, 'Pemakalah Seminar Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(109, 6, 'Penulis Jurnal Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(110, 6, 'Penulis Jurnal Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(111, 7, 'Narasumber/Trainer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(112, 7, 'Konsultan/Reviewer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(113, 8, 'Tugas Khusus Pimpinan', '2025-10-11 05:32:06', '2025-10-11 05:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_detail`
--

CREATE TABLE `tugas_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `sub_tugas_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_detail`
--

INSERT INTO `tugas_detail` (`id`, `sub_tugas_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 1, 'Jumlah kelompok dikoordinasi', NULL, NULL),
(2, 2, 'Jumlah MK dikoordinasi', NULL, NULL),
(3, 3, 'Jumlah Tugas MK dikoordinasi', NULL, NULL),
(4, 4, 'Jumlah mahasiswa dibimbing', NULL, NULL),
(5, 5, 'Jumlah pendampingan KKL', NULL, NULL),
(6, 6, 'Jumlah DPL/KKN/KAPKI', NULL, NULL),
(7, 7, 'Jumlah usulan penelitian/pengabdian direview', NULL, NULL),
(8, 8, 'Jumlah artikel jurnal nasional direview', NULL, NULL),
(9, 9, 'Jumlah artikel jurnal internasional direview', NULL, NULL),
(10, 10, 'Jumlah usulan kenaikan jabatan Lektor Kepala', NULL, NULL),
(11, 11, 'Jumlah usulan kenaikan jabatan Guru Besar', NULL, NULL),
(12, 12, 'Jumlah usulan kenaikan jabatan Asisten Ahli', NULL, NULL),
(13, 13, 'Jumlah usulan kenaikan jabatan Lektor', NULL, NULL),
(14, 14, 'Jumlah asesor BKD', NULL, NULL),
(15, 15, 'Jumlah validator BKD', NULL, NULL),
(16, 16, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05'),
(92, 90, 'Jumlah mahasiswa bimbingan (PA)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(93, 91, 'Jumlah mahasiswa bimbingan skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(94, 92, 'Jumlah sidang/mahasiswa diuji', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(95, 6, 'Jumlah kegiatan KKL/KP dikoordinasi', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(96, 93, 'Jumlah kelompok/mahasiswa KP dibimbing', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(97, 94, 'Judul/Skema (ketua) – jumlah luaran', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(98, 95, 'Jumlah kegiatan sebagai anggota', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(99, 96, 'Jumlah proposal disusun/diajukan', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(100, 97, 'Jumlah kegiatan (ketua)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(101, 98, 'Jumlah kegiatan (anggota)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(102, 99, 'Jumlah sesi narasumber/pelatihan', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(103, 100, 'Jumlah kegiatan kepanitiaan', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(104, 101, 'Jumlah unit/UKM dibina', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(105, 102, 'Jumlah undangan kuliah umum/ceramah', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(106, 103, 'Jumlah dokumen/rapat/produk manajemen', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(107, 104, 'Jumlah asesmen/seleksi yang ditangani', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(108, 105, 'Jumlah dokumen kurikulum/perangkat disusun', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(109, 106, 'Jumlah naskah dikelola (editor)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(110, 107, 'Jumlah pemakalah seminar nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(111, 108, 'Jumlah pemakalah seminar internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(112, 109, 'Jumlah artikel jurnal nasional (penulis)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(113, 110, 'Jumlah artikel jurnal internasional (penulis)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(114, 111, 'Jumlah sesi narasumber/pelatihan (eksternal)', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(115, 112, 'Jumlah penugasan konsultan/reviewer', '2025-10-11 05:32:06', '2025-10-11 05:32:06'),
(116, 113, 'Uraian tugas & keluaran', '2025-10-11 05:32:06', '2025-10-11 05:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_header`
--

CREATE TABLE `tugas_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(255) NOT NULL,
  `tanggal_asli` datetime DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui') NOT NULL DEFAULT 'draft',
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
  `nama_pembuat` bigint UNSIGNED NOT NULL,
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
  `detail_tugas_id` bigint UNSIGNED NOT NULL,
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kode_surat` varchar(255) DEFAULT NULL,
  `bulan` varchar(255) DEFAULT NULL,
  `klasifikasi_surat_id` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_header`
--

INSERT INTO `tugas_header` (`id`, `nomor`, `tanggal_asli`, `status_surat`, `nomor_surat`, `tanggal_surat`, `submitted_at`, `signed_at`, `dibuat_oleh`, `dibuat_pada`, `dikunci_pada`, `file_path`, `signed_pdf_path`, `nomor_status`, `nama_pembuat`, `no_bin`, `tahun`, `semester`, `no_surat_manual`, `nama_umum`, `asal_surat`, `status_penerima`, `jenis_tugas`, `tugas`, `detail_tugas`, `detail_tugas_id`, `waktu_mulai`, `waktu_selesai`, `tempat`, `redaksi_pembuka`, `penutup`, `tembusan`, `penandatangan`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `next_approver`, `created_at`, `updated_at`, `kode_surat`, `bulan`, `klasifikasi_surat_id`, `deleted_at`) VALUES
(1, 'ST-001/UNIKA/2025', '2025-05-01 00:00:00', 'draft', NULL, NULL, NULL, NULL, 1, '2025-06-01 22:53:10', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 1', 1, 'dosen', 'Seminar', '', NULL, 16, '2025-05-10 00:00:00', '2025-05-12 00:00:00', 'Aula UNIKA', NULL, 'Demikian, terima kasih.', NULL, 4, NULL, NULL, NULL, NULL, NULL, 3, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, NULL, NULL),
(2, 'ST-002/UNIKA/2025', '2025-06-01 00:00:00', 'disetujui', '002/UNIKA/2025', '2025-06-01', '2025-06-02 05:53:10', NULL, 2, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, 'locked', 2, NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 2', 2, 'tendik', 'Pelatihan', '', NULL, 16, '2025-06-10 00:00:00', '2025-06-12 00:00:00', 'Ruang Rapat', NULL, 'Harap dilaksanakan sebaik-baiknya.', NULL, 3, NULL, NULL, NULL, NULL, NULL, 4, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, NULL, NULL),
(4, '002/A.1.3/TG/UNIKA/2025/2025', '2025-07-31 00:00:00', 'draft', NULL, NULL, NULL, NULL, 1, '2025-07-31 11:08:30', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Genap', NULL, 'awdwdwadw', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, 16, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Jogja', 'Test', NULL, '\"\"', 10, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-31 04:08:30', '2025-10-12 06:47:29', NULL, '2025', 106, NULL),
(5, '003/TG/UNIKA/II/2025', '2025-07-31 00:00:00', 'pending', NULL, NULL, '2025-07-31 14:49:20', NULL, 1, '2025-07-31 11:44:02', NULL, NULL, NULL, 'reserved', 1, 'FIKOM/006', 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 3', 4, 'dosen', 'Seminar', '', NULL, 16, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Aula UNIKA', NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 4, '2025-07-31 04:44:02', '2025-07-31 07:49:20', NULL, NULL, NULL, NULL),
(6, '001/TG/UNIKA/I/2025', '2025-08-01 09:30:10', 'disetujui', NULL, '2025-09-04', '2025-08-01 09:30:10', '2025-09-04 00:20:32', 1, '2025-08-01 09:30:10', NULL, NULL, 'private/surat_tugas/signed/6.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, NULL, 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, 16, '2025-08-01 09:29:00', '2025-08-01 11:29:00', NULL, NULL, NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": -38, \"offset_y\": 72, \"width_mm\": 51, \"height_mm\": 22}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 2, \"offset_y\": 70, \"width_mm\": 30}', NULL, NULL, NULL, 10, '2025-08-01 02:30:10', '2025-09-04 00:20:35', NULL, NULL, NULL, NULL),
(7, '006/B.3.5/TG/UNIKA/VIII/2025', '2025-08-02 17:30:55', 'disetujui', NULL, '2025-08-01', '2025-09-14 13:55:01', '2025-09-14 07:08:54', 1, '2025-08-02 17:30:55', NULL, NULL, 'private/surat_tugas/signed/7.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'dosen', 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', '<p><strong>Keren</strong></p>', 16, '2025-08-02 17:29:00', '2025-08-02 19:29:00', 'HC Lt 8', 'Test', NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": 118, \"offset_y\": 17, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 20, \"base_left_mm\": 15}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 102, \"offset_y\": 14, \"width_mm\": 30, \"base_top_mm\": 15, \"base_left_mm\": 35}', NULL, NULL, NULL, 10, '2025-08-02 10:30:55', '2025-09-14 07:08:56', NULL, 'VIII', 30, NULL),
(8, '010/B.10.1/TG/UNIKA/VIII/2025', '2025-08-03 07:57:06', 'disetujui', NULL, '2025-09-03', '2025-08-03 07:57:06', '2025-09-03 13:39:16', 1, '2025-08-03 07:57:06', NULL, NULL, 'private/surat_tugas/signed/8.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'tendik', 'Bimbingan', 'Koordinator Tugas MK', '<p>WOW</p>', 16, '2025-08-03 07:56:00', '2025-08-03 09:56:00', NULL, 'Coba', 'Coba', NULL, 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-08-03 00:57:06', '2025-09-03 13:39:18', NULL, 'VIII', 81, NULL),
(9, '001/B.10.6/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-14', NULL, '2025-09-14 01:27:19', 1, '2025-09-14 06:47:54', NULL, NULL, 'private/surat_tugas/signed/9.pdf', 'reserved', 1, NULL, 2025, NULL, NULL, 'Surat Dekan', 3, 'dosen', 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, 13, '2025-09-14 06:46:00', '2025-09-14 08:46:00', 'Ruang Teater TA', 'Sehubung', 'Demikian', NULL, 3, '{\"path\": \"private/ttd/3.png\", \"show\": true, \"offset_x\": -33, \"offset_y\": 77, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 205, \"base_left_mm\": 108}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 0, \"offset_y\": 68, \"width_mm\": 30, \"base_top_mm\": 185, \"base_left_mm\": 125}', NULL, NULL, NULL, 3, '2025-09-13 23:47:54', '2025-09-14 01:27:22', NULL, 'IX', 86, NULL),
(10, '002/B.7.2/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-24', '2025-09-15 12:04:37', '2025-09-24 08:45:21', 1, '2025-09-15 05:04:37', NULL, NULL, 'private/surat_tugas/signed/10_a469cf7c2029a003015973fe7c042a1c.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Tim Reviewer Jurnal Internal', 1, NULL, 'Penelitian', 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, 13, '2025-09-20 08:00:00', '2025-10-20 17:00:00', 'Fakultas Ilmu Komputer', NULL, NULL, NULL, 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-09-15 05:04:37', '2025-09-24 08:45:23', NULL, 'IX', 54, NULL),
(11, '003/B.8.2/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-15', '2025-09-15 12:04:37', '2025-09-14 22:34:11', 1, '2025-09-15 05:04:37', NULL, NULL, 'private/surat_tugas/signed/11_.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Pengabdian Masyarakat', 1, NULL, 'Pengabdian', 'Validator BKD', NULL, 15, '2025-09-22 08:00:00', '2025-09-22 17:00:00', 'Desa Binaan ABC', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.70, 3, '2025-09-15 05:04:37', '2025-09-14 22:34:13', NULL, 'IX', 63, NULL),
(12, '004/B.9.4/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-12', '2025-09-15 12:04:37', '2025-09-12 03:00:00', 1, '2025-09-15 05:04:37', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Wisuda', 1, NULL, 'Lainnya', 'Lainnya', NULL, 16, '2025-09-15 08:00:00', '2025-09-20 17:00:00', 'Auditorium Albertus', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.90, NULL, '2025-09-15 05:04:37', '2025-09-15 05:04:37', NULL, 'IX', 75, NULL),
(13, '001/B.1.5/TG/UNIKA/IX/2025', NULL, 'pending', NULL, NULL, NULL, NULL, 1, '2025-09-16 19:15:15', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan 2 Tetstste', 10, NULL, 'Bimbingan', 'Koordinator MK', '<p>Bimbingan dimulai</p>', 2, '2025-09-16 19:14:00', '2025-09-16 21:14:00', 'Ruang HC', 'Bimbingan', 'Terimakasih', '\"\"', 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-09-16 12:15:15', '2025-10-12 06:39:00', NULL, 'IX', 7, NULL),
(14, '001/B.3.5/TG/UNIKA/X/2025', NULL, 'disetujui', NULL, '2025-10-03', NULL, '2025-10-03 04:14:37', 1, '2025-10-03 02:58:07', NULL, NULL, 'private/surat_tugas/signed/14_5fe72d188901a2722362f9f759809923.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Bimbingan Di Luar Kota', 10, 'dosen', 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', NULL, 4, '2025-10-03 02:54:00', '2025-10-03 04:54:00', 'Ruang Theater', 'halo', 'siap', NULL, 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-02 19:58:07', '2025-10-03 04:14:39', NULL, 'X', 30, NULL),
(15, '001/B.1.7/TG/UNIKA/X/2025', NULL, 'pending', NULL, NULL, NULL, NULL, 1, '2025-10-04 09:15:55', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Draft Panitia Acara Dies Natalis', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, 16, '2025-10-10 08:00:00', '2025-12-20 17:00:00', 'Ruang Teater dan sekitarnya', 'Dalam rangka persiapan Dies Natalis FIKOM ke-30, maka dibentuklah kepanitiaan.', 'Demikian surat tugas ini dibuat untuk dilaksanakan.', '\"Yth. Wakil Rektor I\"', 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-10-04 09:15:55', '2025-10-11 10:43:45', NULL, 'X', 9, NULL),
(16, '002/PENDING/ST/UNIKA/X/2025', NULL, 'pending', NULL, NULL, '2025-10-04 16:15:55', NULL, 1, '2025-10-04 09:15:55', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Dosen Pembimbing Kerja Praktik', 10, NULL, 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', NULL, 4, '2025-10-05 00:00:00', '2026-01-31 23:59:59', 'Fakultas Ilmu Komputer', 'Sehubungan dengan pelaksanaan Kerja Praktik semester Ganjil 2025/2026, dengan ini menugaskan dosen sebagai pembimbing.', 'Harap melaksanakan tugas dengan sebaik-baiknya.', 'Kepala Program Studi Sistem Informasi\nKoordinator Kerja Praktik\nArsip', 3, NULL, NULL, NULL, NULL, NULL, 3, '2025-10-04 09:15:55', '2025-10-04 09:15:55', NULL, 'X', 30, NULL),
(17, '003/DONE/ST/UNIKA/X/2025', NULL, 'disetujui', NULL, '2025-10-04', '2025-10-04 16:15:55', '2025-10-04 09:15:55', 1, '2025-10-04 09:15:55', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Tim Pengabdian Masyarakat', 10, NULL, 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, 13, '2025-11-01 09:00:00', '2025-11-01 15:00:00', 'Desa Rowosari, Kendal', 'Menindaklanjuti program kerja fakultas bidang pengabdian kepada masyarakat, maka ditugaskan tim untuk melaksanakan kegiatan.', 'Atas perhatian dan kerjasamanya diucapkan terima kasih.', 'LPPM Unika Soegijapranata\nKepala Desa Rowosari\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-04 09:15:55', '2025-10-04 09:15:55', NULL, 'X', 63, NULL),
(18, '001/C.3.5/TG/UNIKA/X/2025', NULL, 'pending', NULL, NULL, NULL, NULL, 1, '2025-10-11 07:10:50', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Surat Tugas Pelatihan Mahasiswa Tahun 2025', 10, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', NULL, 103, '2025-10-11 13:06:00', '2025-10-11 15:06:00', 'Ruang Theater Albertus', 'Penugasan PTMB', 'Demikian', '\"\"', 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-10-11 07:10:50', '2025-10-11 10:44:58', NULL, 'X', 131, NULL),
(19, '002/B.1.10/TG/UNIKA/X/2025', NULL, 'disetujui', NULL, NULL, NULL, '2025-10-11 17:43:35', 1, '2025-10-11 16:34:06', NULL, NULL, NULL, 'locked', 1, NULL, 2025, 'Ganjil', NULL, 'UAS Akhir Semestar', 3, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', NULL, 103, '2025-10-11 23:32:00', '2025-10-12 01:32:00', 'Ruang HC', 'Test', 'TYER', '\"Yth. Rektor\"', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-11 16:34:06', '2025-10-11 17:43:35', NULL, 'X', 12, NULL),
(20, '002/A.1.5/TG/UNIKA/X/2025', NULL, 'pending', NULL, NULL, NULL, NULL, 1, '2025-10-12 05:30:34', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan wadWdawd', 3, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', '<p>awdasdwsdwd</p>', 1, '2025-10-12 12:29:00', '2025-10-12 14:29:00', 'Ruang HC', 'Tesad', 'tfawadas', '\"Yth. Rektor\"', 3, NULL, NULL, NULL, NULL, NULL, 3, '2025-10-12 05:30:34', '2025-10-12 06:34:24', NULL, 'X', 108, NULL),
(21, '004/A.1.5/TG/UNIKA/X/2025', NULL, 'pending', NULL, '2025-10-12', '2025-10-13 00:18:39', NULL, 1, '2025-10-12 06:49:25', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Draft Panitia Acara Dies Natalis 26', 3, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Test</p>', 103, '2025-10-12 13:48:00', '2025-10-12 15:48:00', 'Ruang HC', 'Test', 'Test', '\"Yth. Rektor\"', 3, NULL, NULL, NULL, NULL, NULL, 3, '2025-10-12 06:49:25', '2025-10-12 17:18:39', NULL, 'X', 108, NULL);

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_log`
--

INSERT INTO `tugas_log` (`id`, `tugas_id`, `status_lama`, `status_baru`, `user_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(2, 4, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:08:30'),
(3, 5, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:44:02'),
(17, 5, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 07:49:20'),
(18, 6, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-01 02:30:10'),
(19, 7, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 10:30:55'),
(20, 8, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 00:57:06');

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
  `dibaca` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_penerima`
--

INSERT INTO `tugas_penerima` (`id`, `tugas_id`, `pengguna_id`, `nama_penerima`, `jabatan_penerima`, `instansi`, `penerima_key`, `dibaca`) VALUES
(1, 1, 5, '', NULL, NULL, 'I#5', 0),
(2, 1, 6, '', NULL, NULL, 'I#6', 0),
(3, 2, 5, '', NULL, NULL, 'I#5', 1),
(4, 2, 4, '', NULL, NULL, 'I#4', 1),
(9, 5, 6, '', NULL, NULL, 'I#6', 0),
(12, 6, 4, '', NULL, NULL, 'I#4', 0),
(17, 8, 4, '', 'Tenaga Kependidikan', NULL, 'I#4', 0),
(21, 9, 10, '', NULL, NULL, 'I#10', 0),
(24, 7, 3, '', 'Wakil Dekan Fakultas', NULL, 'I#3', 0),
(25, 10, 8, '', NULL, NULL, 'I#8', 0),
(26, 10, 9, '', NULL, NULL, 'I#9', 0),
(27, 11, 16, '', NULL, NULL, 'I#16', 0),
(28, 11, 17, '', NULL, NULL, 'I#17', 0),
(29, 11, 18, '', NULL, NULL, 'I#18', 0),
(30, 12, 4, '', NULL, NULL, 'I#4', 0),
(31, 12, 5, '', NULL, NULL, 'I#5', 0),
(40, 14, 13, '', 'Dosen Pengajar', NULL, 'I#13', 0),
(41, 14, 14, '', 'Dosen Pengajar', NULL, 'I#14', 0),
(51, 15, 6, '', NULL, NULL, 'I#6', 0),
(52, 18, 11, '', NULL, NULL, 'I#11', 0),
(71, 19, 3, '', NULL, NULL, 'I#3', 0),
(72, 19, 4, '', NULL, NULL, 'I#4', 0),
(73, 19, 5, '', NULL, NULL, 'I#5', 0),
(74, 19, 6, '', NULL, NULL, 'I#6', 0),
(75, 19, 7, '', NULL, NULL, 'I#7', 0),
(76, 19, 8, '', NULL, NULL, 'I#8', 0),
(77, 19, 9, '', NULL, NULL, 'I#9', 0),
(78, 19, 11, '', NULL, NULL, 'I#11', 0),
(79, 19, 12, '', NULL, NULL, 'I#12', 0),
(83, 20, 7, '', NULL, NULL, 'I#7', 0),
(86, 13, 9, '', NULL, NULL, 'I#9', 0),
(87, 4, 6, '', NULL, NULL, 'I#6', 0),
(88, 4, 19, '', NULL, NULL, 'I#19', 0),
(109, 21, 3, '', NULL, NULL, 'I#3', 0),
(110, 21, 4, '', NULL, NULL, 'I#4', 0),
(111, 21, 5, '', NULL, NULL, 'I#5', 0),
(112, 21, 6, '', NULL, NULL, 'I#6', 0),
(113, 21, 7, '', NULL, NULL, 'I#7', 0),
(114, 21, 8, '', NULL, NULL, 'I#8', 0),
(115, 21, 9, '', NULL, NULL, 'I#9', 0),
(116, 21, 10, '', NULL, NULL, 'I#10', 0),
(117, 21, 11, '', NULL, NULL, 'I#11', 0),
(118, 21, 12, '', NULL, NULL, 'I#12', 0);

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_signatures`
--

INSERT INTO `user_signatures` (`id`, `pengguna_id`, `ttd_path`, `default_width_mm`, `default_height_mm`, `created_at`, `updated_at`) VALUES
(1, 10, 'private/ttd/10.png', 35, 15, '2025-09-03 13:32:08', '2025-09-03 13:32:08'),
(2, 3, 'private/ttd/3.png', 35, 15, '2025-09-14 01:26:47', '2025-09-14 01:26:47');

--
-- Indexes for dumped tables
--

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
  ADD KEY `idx_keph__nomor` (`nomor`);

--
-- Indexes for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_keputusan_penerima_unique` (`keputusan_id`,`pengguna_id`),
  ADD UNIQUE KEY `uq_kepp` (`keputusan_id`,`pengguna_id`),
  ADD KEY `keputusan_penerima_keputusan_id_foreign` (`keputusan_id`),
  ADD KEY `keputusan_penerima_pengguna_id_foreign` (`pengguna_id`),
  ADD KEY `idx_keputusan_id` (`keputusan_id`),
  ADD KEY `idx_pengguna_id` (`pengguna_id`),
  ADD KEY `idx_kepp__keputusan` (`keputusan_id`),
  ADD KEY `idx_kepp__pengguna` (`pengguna_id`);

--
-- Indexes for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `klasifikasi_surat_kode_unique` (`kode`);

--
-- Indexes for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `uq_counter_skst` (`tipe`,`tahun`,`prefix`);

--
-- Indexes for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_counter_scope` (`kode_surat`,`unit`,`bulan_romawi`,`tahun`),
  ADD UNIQUE KEY `uq_counter_surat` (`kode_surat`,`unit`,`bulan_romawi`,`tahun`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasi_pengguna_id_foreign` (`pengguna_id`),
  ADD KEY `idx_notif_dibaca` (`dibaca`),
  ADD KEY `idx_notif_tipe_ref` (`tipe`,`referensi_id`),
  ADD KEY `idx_notif_user_read_created` (`pengguna_id`,`dibaca`,`created_at`),
  ADD KEY `idx_notif__user_baca_tipe_waktu` (`pengguna_id`,`dibaca`,`tipe`,`dibuat_pada`);

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
  ADD UNIQUE KEY `peran_nama_unique` (`nama`);

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
  ADD KEY `sub_tugas_jenis_tugas_id_foreign` (`jenis_tugas_id`);

--
-- Indexes for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_detail_sub_tugas_id_foreign` (`sub_tugas_id`);

--
-- Indexes for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tugas_header_nomor_unique` (`nomor`),
  ADD KEY `tugas_header_nama_pembuat_foreign` (`nama_pembuat`),
  ADD KEY `tugas_header_asal_surat_foreign` (`asal_surat`),
  ADD KEY `tugas_header_klasifikasi_surat_id_foreign` (`klasifikasi_surat_id`),
  ADD KEY `tugas_header_detail_tugas_id_foreign` (`detail_tugas_id`),
  ADD KEY `idx_tugas_status` (`status_surat`),
  ADD KEY `idx_tugas_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_tugas_next_approver` (`next_approver`),
  ADD KEY `idx_tugas_penandatangan` (`penandatangan`),
  ADD KEY `idx_tugas_created_status` (`created_at`,`status_surat`),
  ADD KEY `idx_tugas__status_tgl` (`status_surat`,`tanggal_surat`),
  ADD KEY `idx_tugas__dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_tugas__penandatangan` (`penandatangan`),
  ADD KEY `idx_tugas__next_approver` (`next_approver`),
  ADD KEY `idx_tugas__waktu` (`waktu_mulai`,`waktu_selesai`),
  ADD KEY `idx_tugas__klasifikasi` (`klasifikasi_surat_id`),
  ADD KEY `idx_tugas__kode_bulan_tahun` (`kode_surat`,`bulan`,`tahun`);

--
-- Indexes for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_log_tugas_id_foreign` (`tugas_id`),
  ADD KEY `tugas_log_user_id_foreign` (`user_id`);

--
-- Indexes for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_tugas_penerima_unique_per_surat` (`tugas_id`,`penerima_key`),
  ADD KEY `idx_penerima_tugas` (`tugas_id`),
  ADD KEY `idx_penerima_pengguna` (`pengguna_id`);

--
-- Indexes for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_signatures_pengguna_id_unique` (`pengguna_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `nomor_counters`
--
ALTER TABLE `nomor_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
-- AUTO_INCREMENT for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `tugas_header`
--
ALTER TABLE `tugas_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tugas_log`
--
ALTER TABLE `tugas_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `user_signatures`
--
ALTER TABLE `user_signatures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions__user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD CONSTRAINT `fk_sub_tugas__jenis` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sub_tugas_jenis_tugas_id_foreign` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  ADD CONSTRAINT `fk_tugas_detail__sub` FOREIGN KEY (`sub_tugas_id`) REFERENCES `sub_tugas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tugas_detail_sub_tugas_id_foreign` FOREIGN KEY (`sub_tugas_id`) REFERENCES `sub_tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD CONSTRAINT `fk_tugas_header__detail` FOREIGN KEY (`detail_tugas_id`) REFERENCES `tugas_detail` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_tugas_header__klasifikasi` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_header_asal_surat_foreign` FOREIGN KEY (`asal_surat`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_header_detail_tugas_id_foreign` FOREIGN KEY (`detail_tugas_id`) REFERENCES `tugas_detail` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tugas_header_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_klasifikasi_surat_id_foreign` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`),
  ADD CONSTRAINT `tugas_header_nama_pembuat_foreign` FOREIGN KEY (`nama_pembuat`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_next_approver_foreign` FOREIGN KEY (`next_approver`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_penandatangan_foreign` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD CONSTRAINT `tugas_log_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

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
