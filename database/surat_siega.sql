-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 09, 2026 at 04:38 AM
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
(1, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'SuratTemplate', 5, 'Template Kegiatan Akademik Umum', '{\"updated_at\": \"2025-12-15T07:27:29.000000Z\", \"detail_tugas\": \"<p>Sehubungan dengan {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:</p>\\r\\n<ol>\\r\\n<li>{{tugas_1}}</li>\\r\\n<li>{{tugas_2}}</li>\\r\\n<li>{{tugas_3}}</li>\\r\\n</ol>\\r\\n<p>Pelaksanaan tugas pada:</p>\\r\\n<ul>\\r\\n<li>Tanggal: {{tanggal_pelaksanaan}}</li>\\r\\n<li>Waktu: {{waktu}}</li>\\r\\n<li>Tempat: {{tempat}}</li>\\r\\n</ul>\\r\\n<p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>\"}', '{\"updated_at\": \"2025-12-15 23:03:32\", \"detail_tugas\": \"<p><strong>Sehubungan dengan</strong> {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:</p><ol><li>{{tugas_1}}</li><li>{{tugas_2}}</li><li>{{tugas_3}}</li></ol><p>Pelaksanaan tugas pada:</p><ul><li>Tanggal: {{tanggal_pelaksanaan}}</li><li>Waktu: {{waktu}}</li><li>Tempat: {{tempat}}</li></ul><p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-15 16:03:32'),
(2, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'create', 'TugasHeader', 26, '001/B.1.1/ST.IKOM/UNIKA/XII/2025', NULL, '{\"id\": 26, \"bulan\": \"XII\", \"nomor\": \"001/B.1.1/ST.IKOM/UNIKA/XII/2025\", \"tahun\": 2025, \"tugas\": \"Panitia Kegiatan Fakultas/Prodi\", \"suffix\": null, \"tempat\": \"Ruang HC\", \"penutup\": \"Testtinggg\", \"semester\": \"Ganjil\", \"tembusan\": \"Yth. Wakil Rektor I\\nYth. Kepala Program Studi\\nUnit Kepegawaian\\nArsip\", \"nama_umum\": \"Testingggg ke 16\", \"asal_surat\": 10, \"created_at\": \"2025-12-16T04:58:34.000000Z\", \"updated_at\": \"2025-12-16T04:58:34.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Penunjang Almamater\", \"waktu_mulai\": \"2025-12-16T04:57:00.000000Z\", \"nomor_status\": \"reserved\", \"status_surat\": \"draft\", \"submitted_at\": null, \"next_approver\": null, \"penandatangan\": 10, \"tanggal_surat\": \"2025-12-15T17:00:00.000000Z\", \"waktu_selesai\": \"2025-12-16T06:57:00.000000Z\", \"nomor_urut_int\": null, \"detail_tugas_id\": 103, \"parent_tugas_id\": null, \"redaksi_pembuka\": \"Testinggg\", \"status_penerima\": null, \"klasifikasi_surat_id\": 3}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 04:58:34'),
(3, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'create', 'TugasHeader', 27, '001/A.1.1/ST.IKOM/UNIKA/XII/2025', NULL, '{\"id\": 27, \"bulan\": \"XII\", \"nomor\": \"001/A.1.1/ST.IKOM/UNIKA/XII/2025\", \"tahun\": 2025, \"tugas\": \"Koordinator kelompok MK/Rumpun/Konsorsium\", \"suffix\": null, \"tempat\": \"dwdwdw\", \"penutup\": \"dwdw\", \"semester\": \"Ganjil\", \"tembusan\": \"Yth. Kepala Program Studi Sistem Informasi\", \"nama_umum\": \"wadawdaw3232\", \"asal_surat\": 10, \"created_at\": \"2025-12-16T15:20:36.000000Z\", \"updated_at\": \"2025-12-16T15:20:36.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Bimbingan\", \"waktu_mulai\": \"2025-12-06T06:58:00.000000Z\", \"nomor_status\": \"reserved\", \"status_surat\": \"pending\", \"submitted_at\": \"2025-12-16T15:20:36.000000Z\", \"next_approver\": 10, \"penandatangan\": 10, \"tanggal_surat\": \"2025-12-15T17:00:00.000000Z\", \"waktu_selesai\": \"2025-12-06T08:58:00.000000Z\", \"nomor_urut_int\": null, \"detail_tugas_id\": 1, \"parent_tugas_id\": null, \"redaksi_pembuka\": \"dwdw\", \"status_penerima\": null, \"klasifikasi_surat_id\": 104}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 15:20:36'),
(4, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'create', 'TugasHeader', 28, '001A/B.1.1/TG/UNIKA/XII/2025', NULL, '{\"id\": 28, \"bulan\": \"XII\", \"nomor\": \"001A/B.1.1/TG/UNIKA/XII/2025\", \"tahun\": 2025, \"tugas\": \"Panitia Kegiatan Fakultas/Prodi\", \"suffix\": \"A\", \"tempat\": \"Ruang HC\", \"penutup\": \"Testtinggg\", \"semester\": \"Ganjil\", \"tembusan\": \"Yth. Wakil Rektor Iyth. Kepala Program Studiunit Kepegawaianarsip\", \"nama_umum\": \"Testingggg ke 16\", \"asal_surat\": 10, \"created_at\": \"2025-12-16T15:45:05.000000Z\", \"updated_at\": \"2025-12-16T15:45:05.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Penunjang Almamater\", \"waktu_mulai\": \"2025-12-16T04:57:00.000000Z\", \"nomor_status\": \"reserved\", \"status_surat\": \"pending\", \"submitted_at\": \"2025-12-16T15:45:05.000000Z\", \"next_approver\": 10, \"penandatangan\": 10, \"tanggal_surat\": \"2025-12-15T17:00:00.000000Z\", \"waktu_selesai\": \"2025-12-16T06:57:00.000000Z\", \"nomor_urut_int\": 1, \"detail_tugas_id\": 103, \"parent_tugas_id\": 26, \"redaksi_pembuka\": \"Testinggg\", \"status_penerima\": null, \"klasifikasi_surat_id\": 3}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 15:45:05'),
(5, NULL, 'System', 'create', 'TugasHeader', 29, 'DRAFT-6941a8acf1309', NULL, '{\"id\": 29, \"bulan\": \"12\", \"nomor\": \"DRAFT-6941a8acf1309\", \"tahun\": 2025, \"tugas\": \"Quidem illum est debitis eum quaerat.\", \"tempat\": \"Tegal\", \"semester\": \"Ganjil\", \"nama_umum\": \"PJ Laksmiwati (Persero) Tbk\", \"signed_at\": null, \"asal_surat\": 1, \"created_at\": \"2025-12-16T18:45:00.000000Z\", \"updated_at\": \"2025-12-16T18:45:00.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Pengabdian\", \"waktu_mulai\": \"2025-12-10T02:00:00.000000Z\", \"status_surat\": \"draft\", \"tanggal_asli\": \"2025-12-09T18:45:00.000000Z\", \"penandatangan\": 20, \"tanggal_surat\": \"2025-12-09T18:45:00.000000Z\", \"waktu_selesai\": \"2025-12-10T05:00:00.000000Z\", \"detail_tugas_id\": 1, \"status_penerima\": \"dosen\", \"klasifikasi_surat_id\": 112}', '127.0.0.1', 'Symfony', '2025-12-16 18:45:01'),
(6, NULL, 'System', 'create', 'TugasHeader', 30, 'ST-DUMMY-002/FIKOM/2025', NULL, '{\"id\": 30, \"bulan\": \"11\", \"nomor\": \"ST-DUMMY-002/FIKOM/2025\", \"tahun\": 2025, \"tugas\": \"Porro est dolor minima.\", \"tempat\": \"Blitar\", \"semester\": \"Ganjil\", \"nama_umum\": \"UD Yuniar Salahudin\", \"signed_at\": null, \"asal_surat\": 1, \"created_at\": \"2025-12-16T18:45:01.000000Z\", \"updated_at\": \"2025-12-16T18:45:01.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Pengabdian\", \"waktu_mulai\": \"2025-11-20T02:00:00.000000Z\", \"status_surat\": \"pending\", \"tanggal_asli\": \"2025-11-19T18:45:01.000000Z\", \"penandatangan\": 20, \"tanggal_surat\": \"2025-11-19T18:45:01.000000Z\", \"waktu_selesai\": \"2025-11-20T05:00:00.000000Z\", \"detail_tugas_id\": 1, \"status_penerima\": null, \"klasifikasi_surat_id\": 25}', '127.0.0.1', 'Symfony', '2025-12-16 18:45:01'),
(7, NULL, 'System', 'create', 'TugasHeader', 31, 'ST-DUMMY-003/FIKOM/2025', NULL, '{\"id\": 31, \"bulan\": \"12\", \"nomor\": \"ST-DUMMY-003/FIKOM/2025\", \"tahun\": 2025, \"tugas\": \"Harum aperiam rem quibusdam.\", \"tempat\": \"Denpasar\", \"semester\": \"Ganjil\", \"nama_umum\": \"Fa Prastuti (Persero) Tbk\", \"signed_at\": \"2025-12-08T18:45:01.000000Z\", \"asal_surat\": 1, \"created_at\": \"2025-12-16T18:45:01.000000Z\", \"updated_at\": \"2025-12-16T18:45:01.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Penelitian\", \"waktu_mulai\": \"2025-12-08T02:00:00.000000Z\", \"status_surat\": \"disetujui\", \"tanggal_asli\": \"2025-12-07T18:45:01.000000Z\", \"penandatangan\": 20, \"tanggal_surat\": \"2025-12-07T18:45:01.000000Z\", \"waktu_selesai\": \"2025-12-08T05:00:00.000000Z\", \"detail_tugas_id\": 1, \"status_penerima\": \"dosen\", \"klasifikasi_surat_id\": 68}', '127.0.0.1', 'Symfony', '2025-12-16 18:45:01'),
(8, NULL, 'System', 'create', 'TugasHeader', 32, 'ST-DUMMY-004/FIKOM/2025', NULL, '{\"id\": 32, \"bulan\": \"11\", \"nomor\": \"ST-DUMMY-004/FIKOM/2025\", \"tahun\": 2025, \"tugas\": \"Totam delectus quia rem expedita.\", \"tempat\": \"Cirebon\", \"semester\": \"Ganjil\", \"nama_umum\": \"Yayasan Salahudin Kusumo\", \"signed_at\": \"2025-11-17T18:45:01.000000Z\", \"asal_surat\": 1, \"created_at\": \"2025-12-16T18:45:01.000000Z\", \"updated_at\": \"2025-12-16T18:45:01.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Penelitian\", \"waktu_mulai\": \"2025-11-17T02:00:00.000000Z\", \"status_surat\": \"disetujui\", \"tanggal_asli\": \"2025-11-16T18:45:01.000000Z\", \"penandatangan\": 20, \"tanggal_surat\": \"2025-11-16T18:45:01.000000Z\", \"waktu_selesai\": \"2025-11-17T05:00:00.000000Z\", \"detail_tugas_id\": 1, \"status_penerima\": null, \"klasifikasi_surat_id\": 121}', '127.0.0.1', 'Symfony', '2025-12-16 18:45:01'),
(9, NULL, 'System', 'create', 'TugasHeader', 33, 'ST-DUMMY-005/FIKOM/2025', NULL, '{\"id\": 33, \"bulan\": \"11\", \"nomor\": \"ST-DUMMY-005/FIKOM/2025\", \"tahun\": 2025, \"tugas\": \"Quidem fugiat ut in.\", \"tempat\": \"Sungai Penuh\", \"semester\": \"Ganjil\", \"nama_umum\": \"PJ Pudjiastuti Maryati Tbk\", \"signed_at\": \"2025-11-24T18:45:01.000000Z\", \"asal_surat\": 1, \"created_at\": \"2025-12-16T18:45:01.000000Z\", \"updated_at\": \"2025-12-16T18:45:01.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"Pengabdian\", \"waktu_mulai\": \"2025-11-24T02:00:00.000000Z\", \"status_surat\": \"disetujui\", \"tanggal_asli\": \"2025-11-23T18:45:01.000000Z\", \"penandatangan\": 20, \"tanggal_surat\": \"2025-11-23T18:45:01.000000Z\", \"waktu_selesai\": \"2025-11-24T05:00:00.000000Z\", \"detail_tugas_id\": 1, \"status_penerima\": \"dosen\", \"klasifikasi_surat_id\": 44}', '127.0.0.1', 'Symfony', '2025-12-16 18:45:01'),
(10, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-10-13T16:05:49.000000Z\", \"telepon_lengkap\": \"Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265\"}', '{\"updated_at\": \"2025-12-20 17:43:34\", \"telepon_lengkap\": \"(024) 8441555 850500\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-20 10:43:34'),
(11, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-20T10:43:34.000000Z\", \"background_path\": null}', '{\"updated_at\": \"2025-12-21 13:29:30\", \"background_path\": \"kop/694793c970479_1766298569.png\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 06:29:30'),
(12, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T06:29:30.000000Z\", \"header_padding\": 5}', '{\"updated_at\": \"2025-12-21 14:57:11\", \"header_padding\": \"115\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 07:57:11'),
(13, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"logo_size\": 160, \"text_color\": \"#000000\", \"updated_at\": \"2025-12-21T07:57:11.000000Z\", \"email_website\": \"e-mail: unika@unika.ac.id http://www.unika.ac.id/\", \"alamat_lengkap\": \"Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234\", \"font_size_title\": 19}', '{\"logo_size\": \"175\", \"text_color\": \"#333333\", \"updated_at\": \"2025-12-21 15:18:17\", \"email_website\": \"unika@unika.ac.id | www.unika.ac.id\", \"alamat_lengkap\": \"Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234\", \"font_size_title\": \"22\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:18:17'),
(14, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"logo_size\": 175, \"updated_at\": \"2025-12-21T08:18:17.000000Z\"}', '{\"logo_size\": \"155\", \"updated_at\": \"2025-12-21 15:21:52\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:21:52'),
(15, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:21:52.000000Z\", \"background_path\": \"kop/694793c970479_1766298569.png\"}', '{\"updated_at\": \"2025-12-21 15:23:18\", \"background_path\": \"kop/6947ae76a69b6_1766305398.png\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:23:18'),
(16, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:23:18.000000Z\", \"header_padding\": 115}', '{\"updated_at\": \"2025-12-21 15:23:30\", \"header_padding\": \"100\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:23:30'),
(17, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:23:30.000000Z\", \"background_path\": \"kop/6947ae76a69b6_1766305398.png\"}', '{\"updated_at\": \"2025-12-21 15:24:38\", \"background_path\": \"kop/6947aec6e09d7_1766305478.png\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:24:39'),
(18, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:24:38.000000Z\", \"header_padding\": 100}', '{\"updated_at\": \"2025-12-21 15:24:45\", \"header_padding\": \"80\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:24:45'),
(19, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:24:45.000000Z\", \"font_size_title\": 22}', '{\"updated_at\": \"2025-12-21 15:25:01\", \"font_size_title\": \"20\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-21 08:25:01'),
(20, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2025-12-21T08:25:01.000000Z\", \"font_size_title\": 20}', '{\"updated_at\": \"2026-01-29 22:30:44\", \"font_size_title\": \"30\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:30:44'),
(21, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'MasterKopSurat', 1, 'ID: 1', '{\"updated_at\": \"2026-01-29T15:30:44.000000Z\", \"font_size_title\": 30}', '{\"updated_at\": \"2026-01-29 22:31:13\", \"font_size_title\": \"20\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:31:13'),
(22, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'create', 'TugasHeader', 34, '001/A.1.3/ST.IKOM/UNIKA/I/2026', NULL, '{\"id\": 34, \"bulan\": \"I\", \"nomor\": \"001/A.1.3/ST.IKOM/UNIKA/I/2026\", \"tahun\": 2026, \"tugas\": \"Pendampingan dosen dalam KKL\", \"suffix\": null, \"tempat\": \"Testing\", \"penutup\": null, \"semester\": \"Ganjil\", \"tembusan\": \"Arsip\", \"nama_umum\": \"Testing aja\", \"asal_surat\": 10, \"created_at\": \"2026-01-29T16:41:09.000000Z\", \"updated_at\": \"2026-01-29T16:41:09.000000Z\", \"dibuat_oleh\": 1, \"jenis_tugas\": \"TA di Luar Mengajar\", \"waktu_mulai\": \"2026-01-29T16:40:00.000000Z\", \"nomor_status\": \"reserved\", \"status_surat\": \"draft\", \"submitted_at\": null, \"next_approver\": null, \"penandatangan\": 10, \"tanggal_surat\": \"2026-01-28T17:00:00.000000Z\", \"waktu_selesai\": \"2026-01-29T18:40:00.000000Z\", \"nomor_urut_int\": null, \"detail_tugas_id\": 5, \"parent_tugas_id\": null, \"redaksi_pembuka\": null, \"status_penerima\": null, \"klasifikasi_surat_id\": 106}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 16:41:09'),
(23, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'KeputusanHeader', 16, '001/B.10.1/SK/UNIKA/FIKOM/X/2025', '{\"updated_at\": \"2025-10-02T17:59:51.000000Z\", \"status_surat\": \"arsip\"}', '{\"updated_at\": \"2026-01-30 10:21:59\", \"status_surat\": \"terbit\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 03:21:59'),
(24, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'KeputusanHeader', 17, '001/B.10.1/TG/UNIKA/X/2025', '{\"tahun\": 2025, \"tembusan\": \"[{&quot;value&quot;:&quot;Yth. Rektor&quot;}]\", \"menetapkan\": [{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}], \"updated_at\": \"2025-12-06T13:08:57.000000Z\"}', '{\"tahun\": 2026, \"tembusan\": \"Yth. Dekan Fakultas Ilmu Komputer\", \"menetapkan\": \"[{\\\"judul\\\":\\\"KESATU\\\",\\\"isi\\\":\\\"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.<\\\\/p>\\\"}]\", \"updated_at\": \"2026-01-30 10:59:58\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 03:59:58'),
(25, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'update', 'KeputusanHeader', 17, '001/B.10.1/TG/UNIKA/X/2025', '{\"menetapkan\": [{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}], \"updated_at\": \"2026-01-30T03:59:58.000000Z\"}', '{\"menetapkan\": \"[{\\\"judul\\\":\\\"KESATU\\\",\\\"isi\\\":\\\"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.<\\\\/p>\\\"}]\", \"updated_at\": \"2026-01-30 11:00:05\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 04:00:06'),
(26, 1, 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'create', 'KeputusanHeader', 23, '', NULL, '{\"id\": 23, \"nomor\": \"\", \"tahun\": 2026, \"tentang\": \"Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025\", \"cap_w_mm\": null, \"tembusan\": \"Yth. Dekan Fakultas Ilmu Komputer\", \"ttd_w_mm\": null, \"mengingat\": [\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\"], \"menimbang\": [\"bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2025 diperlukan pembentukan panitia pelaksana\"], \"cap_config\": null, \"created_at\": \"2026-01-30T04:24:03.000000Z\", \"deleted_at\": null, \"memutuskan\": \"<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p></p...\", \"ttd_config\": null, \"updated_at\": \"2026-01-30T04:24:03.000000Z\", \"cap_opacity\": null, \"dibuat_oleh\": 1, \"status_surat\": \"draft\", \"penandatangan\": 3, \"tanggal_surat\": \"2026-01-29T17:00:00.000000Z\", \"kota_penetapan\": \"Semarang\", \"judul_penetapan\": \"\", \"npp_penandatangan\": \"\", \"penerima_eksternal\": [], \"tembusan_formatted\": null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 04:24:03');

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
('master_kop_surat_instance', 'O:25:\"App\\Models\\MasterKopSurat\":31:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:16:\"master_kop_surat\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:36:{s:2:\"id\";i:1;s:9:\"unit_code\";N;s:8:\"nama_kop\";s:17:\"Kop Default FIKOM\";s:4:\"unit\";N;s:15:\"background_path\";s:32:\"kop/6947aec6e09d7_1766305478.png\";s:8:\"cap_path\";s:48:\"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\";s:20:\"cap_default_width_mm\";i:30;s:11:\"cap_opacity\";i:85;s:15:\"cap_offset_x_mm\";i:0;s:15:\"cap_offset_y_mm\";i:0;s:10:\"updated_by\";i:1;s:10:\"created_at\";s:19:\"2025-08-26 10:50:41\";s:10:\"updated_at\";s:19:\"2026-01-29 22:31:13\";s:4:\"mode\";s:8:\"composed\";s:9:\"mode_type\";s:6:\"custom\";s:13:\"nama_fakultas\";s:22:\"FAKULTAS ILMU KOMPUTER\";s:14:\"alamat_lengkap\";s:54:\"Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234\";s:15:\"telepon_lengkap\";s:20:\"(024) 8441555 850500\";s:13:\"email_website\";s:35:\"unika@unika.ac.id | www.unika.ac.id\";s:10:\"text_align\";s:5:\"right\";s:9:\"logo_size\";i:155;s:15:\"font_size_title\";i:20;s:14:\"font_size_text\";i:12;s:10:\"text_color\";s:7:\"#333333\";s:14:\"header_padding\";i:80;s:18:\"background_opacity\";i:100;s:6:\"alamat\";s:52:\"Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234\";s:7:\"telepon\";s:23:\"(024) 8441555, 85050003\";s:3:\"fax\";s:25:\"(024) 8415429 – 8454265\";s:5:\"email\";s:17:\"unika@unika.ac.id\";s:7:\"website\";s:23:\"https://www.unika.ac.id\";s:14:\"logo_kiri_path\";N;s:19:\"tampilkan_logo_kiri\";i:0;s:15:\"logo_kanan_path\";s:48:\"kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg\";s:20:\"tampilkan_logo_kanan\";i:1;s:10:\"deleted_at\";N;}s:11:\"\0*\0original\";a:36:{s:2:\"id\";i:1;s:9:\"unit_code\";N;s:8:\"nama_kop\";s:17:\"Kop Default FIKOM\";s:4:\"unit\";N;s:15:\"background_path\";s:32:\"kop/6947aec6e09d7_1766305478.png\";s:8:\"cap_path\";s:48:\"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\";s:20:\"cap_default_width_mm\";i:30;s:11:\"cap_opacity\";i:85;s:15:\"cap_offset_x_mm\";i:0;s:15:\"cap_offset_y_mm\";i:0;s:10:\"updated_by\";i:1;s:10:\"created_at\";s:19:\"2025-08-26 10:50:41\";s:10:\"updated_at\";s:19:\"2026-01-29 22:31:13\";s:4:\"mode\";s:8:\"composed\";s:9:\"mode_type\";s:6:\"custom\";s:13:\"nama_fakultas\";s:22:\"FAKULTAS ILMU KOMPUTER\";s:14:\"alamat_lengkap\";s:54:\"Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234\";s:15:\"telepon_lengkap\";s:20:\"(024) 8441555 850500\";s:13:\"email_website\";s:35:\"unika@unika.ac.id | www.unika.ac.id\";s:10:\"text_align\";s:5:\"right\";s:9:\"logo_size\";i:155;s:15:\"font_size_title\";i:20;s:14:\"font_size_text\";i:12;s:10:\"text_color\";s:7:\"#333333\";s:14:\"header_padding\";i:80;s:18:\"background_opacity\";i:100;s:6:\"alamat\";s:52:\"Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234\";s:7:\"telepon\";s:23:\"(024) 8441555, 85050003\";s:3:\"fax\";s:25:\"(024) 8415429 – 8454265\";s:5:\"email\";s:17:\"unika@unika.ac.id\";s:7:\"website\";s:23:\"https://www.unika.ac.id\";s:14:\"logo_kiri_path\";N;s:19:\"tampilkan_logo_kiri\";i:0;s:15:\"logo_kanan_path\";s:48:\"kop/zIVT62aXzfG6geqDtwy3istgxgvJgaa6y3juUo5u.jpg\";s:20:\"tampilkan_logo_kanan\";i:1;s:10:\"deleted_at\";N;}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:10:{s:20:\"tampilkan_logo_kanan\";s:7:\"boolean\";s:19:\"tampilkan_logo_kiri\";s:7:\"boolean\";s:10:\"created_at\";s:8:\"datetime\";s:10:\"updated_at\";s:8:\"datetime\";s:10:\"deleted_at\";s:8:\"datetime\";s:9:\"logo_size\";s:7:\"integer\";s:15:\"font_size_title\";s:7:\"integer\";s:14:\"font_size_text\";s:7:\"integer\";s:14:\"header_padding\";s:7:\"integer\";s:18:\"background_opacity\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:4:{i:0;s:15:\"logo_kanan_path\";i:1;s:14:\"logo_kiri_path\";i:2;s:8:\"cap_path\";i:3;s:15:\"background_path\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:4:{i:0;s:2:\"id\";i:1;s:10:\"created_at\";i:2;s:10:\"updated_at\";i:3;s:10:\"deleted_at\";}s:16:\"\0*\0forceDeleting\";b:0;}', 1770568990);

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
(9, 'Uji Coba', '2026-02-08 16:40:59', '2026-02-08 16:45:15', '2026-02-08 16:45:15'),
(10, 'Testing', '2026-02-08 17:04:41', '2026-02-08 17:17:09', '2026-02-08 17:17:09');

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
(23, 'default', '{\"uuid\":\"49b1d890-f101-4657-9ebf-458409b05249\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:21;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1760289519, 1760289519),
(24, 'default', '{\"uuid\":\"83f89de4-b50b-4e50-97ed-de9b994a7e2f\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:22;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1760865908, 1760865908),
(25, 'default', '{\"uuid\":\"229c4580-62b4-4483-a0f2-7ce462815337\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:20;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1760867470, 1760867470),
(26, 'default', '{\"uuid\":\"51495518-b113-4f55-bf10-3fbd7f51c280\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:16;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1760868052, 1760868052),
(27, 'default', '{\"uuid\":\"c81c1437-ee53-4239-98f2-38e9d872386e\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:18;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1764866038, 1764866038),
(28, 'default', '{\"uuid\":\"22b59003-8350-455f-8733-11000504897b\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:15;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765032506, 1765032506),
(29, 'default', '{\"uuid\":\"39e35331-7305-4407-9930-8f5749775f5e\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:13;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765032535, 1765032535),
(30, 'default', '{\"uuid\":\"f15ffda4-31ac-4b89-b545-c940c69a0eb6\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:26;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1765862629, 1765862629),
(31, 'default', '{\"uuid\":\"e7b8fe19-e07e-43b6-a2ee-b1ab624a843e\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:25;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1765864720, 1765864720),
(32, 'mail', '{\"uuid\":\"3f6710d7-9ffc-4764-9dc4-f42e491146d0\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:28;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-16 23:12:41.104432\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765901561, 1765901556),
(33, 'default', '{\"uuid\":\"cd584de1-cdb1-43da-9d0b-e86830d0b996\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:28;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765901559, 1765901559),
(34, 'mail', '{\"uuid\":\"6b727ae1-c5e6-47c5-9bf4-eaae74494810\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:27;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 00:39:29.118009\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765906769, 1765906765),
(35, 'default', '{\"uuid\":\"6c4a7445-ad24-4284-ad2d-eb1faef9b4a4\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:27;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765906769, 1765906769),
(36, 'mail', '{\"uuid\":\"7662059e-973d-4b44-94bf-87cdf721d637\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:26;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 00:59:18.125824\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765907958, 1765907953),
(37, 'default', '{\"uuid\":\"6dea8449-aa27-4b4c-b0dd-0bd15f12fbc9\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:26;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765907954, 1765907954),
(38, 'mail', '{\"uuid\":\"81dad8bb-f3aa-499d-bbf3-d1faf83f6305\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:25;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 01:50:16.366248\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765911016, 1765911011),
(39, 'default', '{\"uuid\":\"29f3c6ba-9297-4064-b637-8b1485c15ac4\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:25;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765911012, 1765911012),
(40, 'default', '{\"uuid\":\"7fb65b3a-8451-49e2-a6f3-acbec07b4095\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:4;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1765911134, 1765911134),
(41, 'mail', '{\"uuid\":\"13679be6-e209-43c5-baf3-9a31c8f69ad5\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:4;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 01:52:45.647558\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765911165, 1765911160),
(42, 'mail', '{\"uuid\":\"84e910ac-15aa-412f-b3bf-5397559f9518\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:4;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 01:52:45.659174\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765911165, 1765911160),
(43, 'default', '{\"uuid\":\"22180835-b54c-4204-be2d-e3a2034e7f2c\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:4;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765911161, 1765911161),
(44, 'default', '{\"uuid\":\"8865c653-e8ff-44af-96fd-6f8543f48250\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:1;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1765911566, 1765911566),
(45, 'mail', '{\"uuid\":\"899d82ee-2b2d-4147-9160-49018a889c55\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:1;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 01:59:51.689802\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765911591, 1765911586),
(46, 'mail', '{\"uuid\":\"e74df63b-1971-44ce-9dd7-a846a6876747\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:1;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-12-17 01:59:51.700838\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1765911591, 1765911586),
(47, 'default', '{\"uuid\":\"db20ad99-28cd-4d1e-99f4-0dac7050d90f\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:1;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1765911587, 1765911587),
(48, 'default', '{\"uuid\":\"dc908a63-3722-4bb7-a0b4-22a4197acdb7\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:34;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1769704878, 1769704878),
(49, 'default', '{\"uuid\":\"5394835e-be5a-459b-a294-22dce998e0fb\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:29;s:4:\\\"mode\\\";s:11:\\\"to_approver\\\";}\"}}', 0, NULL, 1769705438, 1769705438),
(50, 'mail', '{\"uuid\":\"2dca34e5-d5a9-46cb-8987-3e10f2fa8027\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":4:{s:7:\\\"tugasId\\\";i:34;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";s:5:\\\"queue\\\";s:4:\\\"mail\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-01-29 23:58:49.381068\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:12:\\\"Asia\\/Jakarta\\\";}}\"}}', 0, NULL, 1769705929, 1769705924),
(51, 'default', '{\"uuid\":\"2db79e1c-3015-4dc9-a586-cee26db79974\",\"displayName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendSuratTugasEmail\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\SendSuratTugasEmail\\\":2:{s:7:\\\"tugasId\\\";i:34;s:4:\\\"mode\\\";s:13:\\\"to_recipients\\\";}\"}}', 0, NULL, 1769705927, 1769705927);

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_attachments`
--

CREATE TABLE `keputusan_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file original dari user',
  `nama_file_sistem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file hasil rename sistem',
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path storage file',
  `file_size` int UNSIGNED NOT NULL COMMENT 'Ukuran file dalam bytes',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe MIME file',
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ekstensi file',
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi/keterangan file',
  `kategori` enum('proposal','rab','surat_pengantar','dokumentasi','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya' COMMENT 'Kategori dokumen',
  `download_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Jumlah download',
  `last_downloaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `keputusan_attachments`
--

INSERT INTO `keputusan_attachments` (`id`, `keputusan_id`, `nama_file`, `nama_file_sistem`, `file_path`, `file_size`, `mime_type`, `extension`, `uploaded_by`, `deskripsi`, `kategori`, `download_count`, `last_downloaded_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 'receipt.pdf', '1764648941_692e67eda4f40.pdf', 'lampiran_sk/7/1764648941_692e67eda4f40.pdf', 118294, 'application/pdf', 'pdf', 1, NULL, 'proposal', 0, NULL, '2025-12-02 04:15:41', '2025-12-02 04:21:03', '2025-12-02 04:21:03'),
(2, 7, 'receipt.pdf', '1764649269_692e69358aec7.pdf', 'lampiran_sk/7/1764649269_692e69358aec7.pdf', 118294, 'application/pdf', 'pdf', 1, NULL, 'rab', 4, '2025-12-02 04:21:22', '2025-12-02 04:21:09', '2025-12-02 04:21:29', '2025-12-02 04:21:29'),
(4, 18, 'receipt.pdf', '1765026629_69342b45a3bce.pdf', 'lampiran_sk/18/1765026629_69342b45a3bce.pdf', 118294, 'application/pdf', 'pdf', 1, NULL, 'proposal', 0, NULL, '2025-12-06 13:10:29', '2025-12-06 13:10:29', NULL);

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
(7, '001/B.10.1/TG/UNIKA/IX/2025', '2025-12-01', NULL, 'Semarang', NULL, 'Test', NULL, '[\"Test\"]', '[\"Test\", \"hasi\"]', '[{\"isi\": \"<p>twe</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>test</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> <p>twe</p></p>\n<p><strong>KEDUA:</strong> <p>test</p></p>', NULL, NULL, NULL, '[]', 'pending', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-28 07:39:22', '2025-12-02 04:21:50', NULL),
(8, '2644/F.1.2/FIKOM/XI/2023', '2025-10-03', 2025, 'Semarang', '2025-10-02 19:19:53', 'Penetapan Visi, Misi, Tujuan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dan seluruh Program Studi yang bernaung di bawahnya', NULL, '[\"bahwa Fakultas Ilmu Komputer menaungi 2 (dua) program studi, yaitu Teknik Informatika dan Sistem Informasi sejak 25 Juni 2013 dengan kekhasan dan sumber daya masing-masing;\", \"bahwa Fakultas Ilmu Komputer memerlukan media untuk menyatakan tujuan, arah serta sasaran sebagai landasan program studi guna memanfaatkan dan mengalokasikan sumber daya yang mereka miliki beserta proses pengendaliannya serta untuk membentuk serta membangun budaya institusi;\", \"bahwa berdasarkan keputusan Rapat Senat Fakultas Ilmu Komputer pada tanggal 31 Oktober 2023 yang menetapkan perlunya penyesuaian dan peninjauan Visi dan Misi Fakultas Ilmu Komputer dan seluruh Program Studi yang bernaung di bawahnya serta keputusan Rapat Kerja Fakultas Ilmu Komputer Tahun 2023;\", \"bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a, huruf b, dan huruf c, perlu diterbitkan Surat Keputusan Dekan Fakultas Ilmu Komputer tentang Visi dan Misi Fakultas Ilmu Komputer;\"]', '[\"Undang-Undang No. 20 tahun 2013 tentang Pendidikan Tinggi;\", \"Undang-Undang Republik Indonesia Nomor 12 Tahun 2012 tentang Pendidikan Tinggi;\", \"Peraturan Pemerintah No. 14 tahun 2014 tentang Penyelenggaraan Pendidikan Tinggi dan Pengelolaan Perguruan Tinggi;\", \"Keputusan Yayasan Sandjojo No. 66/PER/YS/05/VII/2013 tentang Statuta Universitas Katolik Soegijapranata;\", \"Peraturan Universitas No. E.2/1616/UKS.01/VII/2001 tentang Organisasi dan Tata Laksana Universitas Katolik Soegijapranata;\"]', '[{\"isi\": \"<p>KEPUTUSAN DEKAN TENTANG PENETAPAN VISI, MISI, DAN TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Misi Fakultas Ilmu Komputer adalah sebagai berikut:</p>\\r\\n\\r\\n<ol><li>Menyelenggarakan kegiatan pendidikan yang bermutu, terencana, dan konsisten secara akademis dalam lingkungan yang mendukung pengembangan versi terbaik dari masing-masing pribadi di masyarakat.</li><li>Melakukan penelitian untuk mengembangkan Teknologi Informasi terkini yang sesuai dengan kebutuhan masyarakat dan ilmu pengetahuan.</li><li>Menerapkan Teknologi Informasi dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Menjalin kerjasama dengan berbagai instansi untuk meningkatkan kualitas Tri Dharma Perguruan Tinggi.</li></ol>\", \"judul\": \"KEDUA\"}, {\"isi\": \"<p>Tujuan Fakultas Ilmu Komputer adalah sebagai berikut:</p>\\r\\n\\r\\n<ol><li>Menghasilkan lulusan yang jujur, adaptif, kreatif, dan peduli kepada masyarakat melalui kompetensinya di bidang Teknologi Informasi.</li><li>Mewujudkan mutu pendidikan yang paripurna berdasar pada standar nasional pendidikan.</li><li>Menghasilkan penelitian di bidang Teknologi Informasi yang bermanfaat bagi masyarakat dan mampu bersaing di tingkat nasional dan internasional.</li><li>Menghasilkan publikasi ilmiah dalam bidang teknologi informasi yang dapat meningkatkan kapasitas dosen, mahasiswa, dan masyarakat di tingkat nasional dan internasional.</li><li>Menerapkan Teknologi Informasi yang dapat menjadi solusi atas kebutuhan-kebutuhan masyarakat dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Berjejaring dengan institusi pendidikan, industri dan pemerintah untuk meningkatkan kualitas pendidikan, penelitian dan pengabdian di bidang Teknologi Informasi.</li></ol>\", \"judul\": \"KETIGA\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEEMPAT\"}]', '<p><strong>KESATU:</strong> <p>KEPUTUSAN DEKAN TENTANG PENETAPAN VISI, MISI, DAN TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA.</p></p>\n<p><strong>KEDUA:</strong> <p>Misi Fakultas Ilmu Komputer adalah sebagai berikut:</p>\r\n\r\n<ol><li>Menyelenggarakan kegiatan pendidikan yang bermutu, terencana, dan konsisten secara akademis dalam lingkungan yang mendukung pengembangan versi terbaik dari masing-masing pribadi di masyarakat.</li><li>Melakukan penelitian untuk mengembangkan Teknologi Informasi terkini yang sesuai dengan kebutuhan masyarakat dan ilmu pengetahuan.</li><li>Menerapkan Teknologi Informasi dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Menjalin kerjasama dengan berbagai instansi untuk meningkatkan kualitas Tri Dharma Perguruan Tinggi.</li></ol></p>\n<p><strong>KETIGA:</strong> <p>Tujuan Fakultas Ilmu Komputer adalah sebagai berikut:</p>\r\n\r\n<ol><li>Menghasilkan lulusan yang jujur, adaptif, kreatif, dan peduli kepada masyarakat melalui kompetensinya di bidang Teknologi Informasi.</li><li>Mewujudkan mutu pendidikan yang paripurna berdasar pada standar nasional pendidikan.</li><li>Menghasilkan penelitian di bidang Teknologi Informasi yang bermanfaat bagi masyarakat dan mampu bersaing di tingkat nasional dan internasional.</li><li>Menghasilkan publikasi ilmiah dalam bidang teknologi informasi yang dapat meningkatkan kapasitas dosen, mahasiswa, dan masyarakat di tingkat nasional dan internasional.</li><li>Menerapkan Teknologi Informasi yang dapat menjadi solusi atas kebutuhan-kebutuhan masyarakat dalam lingkup pengabdian masyarakat ataupun komersial (hilirisasi).</li><li>Berjejaring dengan institusi pendidikan, industri dan pemerintah untuk meningkatkan kualitas pendidikan, penelitian dan pengabdian di bidang Teknologi Informasi.</li></ol></p>\n<p><strong>KEEMPAT:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', 'private/surat_keputusan/signed/8_7ae37f64de56414ff6f683ee2fd8876e.pdf', NULL, NULL, NULL, 'disetujui', 1, 10, NULL, 10, '2025-10-02 19:19:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37, 37, 0.95, '2025-09-29 04:34:22', '2025-10-02 19:19:58', NULL),
(13, 'UT-SK-PEN-001/FIKOM/2025', '2025-12-04', 2025, 'Semarang', '2025-12-04 16:36:23', 'UJI: Revisi oleh penandatangan saat pending', NULL, '[\"Alasan A (direvisi)\", \"Alasan B\", \"Alasan C (baru)\"]', '[\"Dasar 1\", \"Dasar 2\", \"Dasr 3\"]', '[{\"isi\": \"<p>Lakukan A</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Lakukan A</p></p>', 'private/surat_keputusan/signed/13_90fbca25a83188277a5d46584ffdf73d.pdf', NULL, NULL, NULL, 'disetujui', 1, 10, NULL, 10, '2025-12-04 16:36:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 47, 40, 0.80, '2025-10-02 17:59:50', '2025-12-04 16:36:25', NULL),
(14, 'UT-SK-APP-001/FIKOM/2025', '2025-10-03', 2025, 'Semarang', '2025-11-21 03:49:41', 'UJI: Approve SK', NULL, '[\"Test\"]', '[\"Dasar Approve 1\"]', '[{\"isi\": \"<p>Approve tugas X</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Approve tugas X</p></p>', 'private/surat_keputusan/signed/14_0484de2370d8ce488b9da8f4a344582a.pdf', 'Test', 'Tembusan Yth:\r\n1. Test', NULL, 'disetujui', 1, 10, NULL, 10, '2025-11-21 03:49:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"w_mm\": 42}', '{\"w_mm\": 35, \"opacity\": 0.95}', 35, 35, 0.95, '2025-10-02 17:59:50', '2025-11-21 03:49:45', NULL),
(15, 'UT-SK-REJ-001/FIKOM/2025', '2025-12-06', 2025, 'Semarang', NULL, 'UJI: Reject SK', NULL, '[\"Alasan perlu revisi\"]', '[\"Dasar X\"]', '[{\"isi\": \"<p>dw</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>dw</p></p>', NULL, NULL, NULL, '[]', 'ditolak', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, 10, '2025-10-02 17:59:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-02 17:59:50', '2025-12-06 12:09:41', NULL),
(16, '001/B.10.1/SK/UNIKA/FIKOM/X/2025', NULL, 2025, 'Semarang', NULL, 'UJI: Publish SK', NULL, '[\"Sudah final\"]', '[\"Dasar lengkap\"]', NULL, '<p><strong>KESATU:</strong> Berlaku sejak ditetapkan.</p>', NULL, NULL, NULL, NULL, 'terbit', 1, 10, NULL, 10, '2025-10-02 17:59:51', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-10-02 17:59:51', NULL, NULL, NULL, NULL, NULL, '2025-10-02 17:59:50', '2026-01-30 03:21:59', NULL),
(17, '001/B.10.1/TG/UNIKA/X/2025', '2025-10-12', 2026, 'Semarang', NULL, 'Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025', NULL, '[\"bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2025 diperlukan pembentukan panitia pelaksana\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\"]', '[{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p></p>', NULL, 'Yth. Dekan Fakultas Ilmu Komputer', NULL, '[]', 'pending', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-12 14:20:13', '2026-01-30 04:00:05', NULL),
(18, 'SK-TEST-001/FIKOM/2025', '2025-12-06', 2025, 'Semarang', '2025-12-06 14:48:09', 'Testing SK Status Draft', 'KEPUTUSAN DEKAN TENTANG TESTING SK STATUS DRAFT', '[\"Untuk keperluan testing sistem.\", \"Memastikan fitur draft berjalan dengan baik.\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi.\", \"Statuta Universitas Katolik Soegijapranata.\"]', '[{\"isi\": \"<p>SK ini dibuat untuk testing status <strong>DRAFT</strong>.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', '<p><strong>KESATU:</strong> <p>SK ini dibuat untuk testing status <strong>DRAFT</strong>.</p></p>\n<p><strong>KEDUA:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', 'private/surat_keputusan/signed/18_821af1f059d0b3d569f0be80960345f5.pdf', 'Yth. Rektor\nArsip', NULL, '[]', 'disetujui', 1, 10, '058.1.2002.255', 10, '2025-12-06 14:48:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35, 35, 0.95, '2025-12-06 13:07:00', '2025-12-06 14:48:10', NULL),
(19, 'SK-TEST-002/FIKOM/2025', '2025-12-06', 2025, 'Semarang', NULL, 'Testing SK Status Pending', 'KEPUTUSAN DEKAN TENTANG TESTING SK STATUS PENDING', '[\"SK diajukan untuk persetujuan Dekan.\", \"Perlu segera diproses.\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi.\", \"Statuta Universitas Katolik Soegijapranata.\"]', '[{\"isi\": \"<p>SK ini dalam status <strong>PENDING</strong> menunggu approval dari Dekan.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', 'MEMUTUSKAN', NULL, 'Yth. Rektor, Yth. Wakil Rektor I, Arsip', NULL, NULL, 'pending', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 13:07:00', '2025-12-06 13:07:00', NULL),
(20, 'SK-TEST-003/FIKOM/2025', '2025-12-06', 2025, 'Semarang', NULL, 'Testing SK Status Ditolak', 'KEPUTUSAN DEKAN TENTANG TESTING SK STATUS DITOLAK', '[\"SK ditolak oleh penandatangan karena perlu revisi.\", \"Harus diperbaiki dan diajukan kembali.\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi.\", \"Statuta Universitas Katolik Soegijapranata.\"]', '[{\"isi\": \"<p>SK ini <strong>DITOLAK</strong> dan perlu revisi sebelum diajukan kembali.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Pembuat SK diminta untuk memperbaiki dokumen sesuai catatan penolakan.</p>\", \"judul\": \"KEDUA\"}]', 'MEMUTUSKAN', NULL, 'Yth. Rektor, Arsip', NULL, NULL, 'ditolak', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 13:07:00', '2025-12-06 13:07:00', NULL),
(21, 'SK-TEST-004/FIKOM/2025', '2025-12-06', 2025, 'Semarang', NULL, 'Testing SK Status Disetujui', 'KEPUTUSAN DEKAN TENTANG TESTING SK STATUS DISETUJUI', '[\"SK telah disetujui oleh Dekan.\", \"Siap untuk diterbitkan.\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi.\", \"Statuta Universitas Katolik Soegijapranata.\"]', '[{\"isi\": \"<p>SK ini sudah <strong>DISETUJUI</strong> dan siap diterbitkan secara resmi.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>\", \"judul\": \"KEDUA\"}]', 'MEMUTUSKAN', NULL, 'Yth. Rektor, Yth. Wakil Rektor I, Yth. Wakil Rektor II, Arsip', NULL, NULL, 'disetujui', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 13:07:00', '2025-12-06 13:07:00', NULL),
(22, 'SK-TEST-005/FIKOM/2025', '2025-12-06', 2025, 'Semarang', NULL, 'Testing SK Status Terbit', 'KEPUTUSAN DEKAN TENTANG TESTING SK STATUS TERBIT', '[\"SK telah diterbitkan dan berlaku resmi.\", \"Distribusi ke pihak terkait telah dilakukan.\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi.\", \"Statuta Universitas Katolik Soegijapranata.\"]', '[{\"isi\": \"<p>SK ini sudah <strong>TERBIT</strong> dan berlaku resmi sejak tanggal ditetapkan.</p>\", \"judul\": \"KESATU\"}, {\"isi\": \"<p>Semua pihak terkait wajib melaksanakan isi keputusan ini.</p>\", \"judul\": \"KEDUA\"}]', 'MEMUTUSKAN', NULL, 'Yth. Rektor, Yth. Wakil Rektor I, Yth. Wakil Rektor II, Dekan Fakultas Ilmu Komputer, Arsip', NULL, NULL, 'terbit', 1, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 13:07:00', '2025-12-06 05:00:00', NULL),
(23, NULL, '2026-01-30', 2026, 'Semarang', NULL, 'Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025', NULL, '[\"bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2025 diperlukan pembentukan panitia pelaksana\"]', '[\"Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi\"]', '[{\"isi\": \"<p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>\", \"judul\": \"KESATU\"}]', '<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional Teknologi Informasi 2025 dengan susunan sebagai berikut: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p></p>', NULL, 'Yth. Dekan Fakultas Ilmu Komputer', NULL, '[]', 'draft', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 04:24:03', '2026-01-30 04:24:03', NULL);

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
(30, 16, 5, NULL, '2025-10-02 17:59:51', '2025-10-02 17:59:51', 0, NULL),
(31, 16, 6, NULL, '2025-10-02 17:59:51', '2025-10-02 17:59:51', 0, NULL),
(43, 8, 5, NULL, '2025-10-02 18:56:40', '2025-10-02 18:56:40', 0, NULL),
(74, 14, 6, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(75, 14, 7, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(76, 14, 8, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(77, 14, 9, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(78, 14, 11, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(79, 14, 12, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(80, 14, 13, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(81, 14, 15, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(82, 14, 17, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL),
(83, 14, 22, NULL, '2025-10-07 11:26:52', '2025-10-07 11:26:52', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_status_logs`
--

CREATE TABLE `keputusan_status_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `status_dari` enum('draft','pending','disetujui','ditolak','terbit','arsip') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_ke` enum('draft','pending','disetujui','ditolak','terbit','arsip') COLLATE utf8mb4_unicode_ci NOT NULL,
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
(188, 'A.1.7', 'Testing', '2026-02-08 17:19:36', '2026-02-08 17:22:17', '2026-02-08 17:22:17');

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
(7, 'Permenristekdikti SNPT', 'Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi Republik Indonesia Nomor 44 Tahun 2015 tentang Standar Nasional Pendidikan Tinggi', 'Permen', 'Permenristekdikti No. 44 Tahun 2015', '2015-12-28', 1, 1, 0, '2025-12-15 09:04:36', '2025-12-15 09:04:36', NULL),
(8, 'Testing', 'Test', 'UU', 'WWWW', '2025-12-15', 1, 1, 0, '2025-12-15 09:16:03', '2025-12-15 09:16:03', NULL);

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
(6, 'Testing', 'Test', 'akademik', NULL, 1, 1, 0, '2025-12-15 09:11:25', '2025-12-15 09:11:25', NULL);

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
(59, '2026_02_09_100000_add_foto_path_to_pengguna_table', 52);

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
(1, 'SK', 2025, 'B.10.1/SK/UNIKA', 1, '2025-10-02 17:59:51', NULL);

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
(1, 'B.10.1', 'TG', 'IX', 2025, 4, '2025-09-27 17:41:23', '2025-09-28 08:08:49', NULL),
(2, 'B.10.1', 'TG', 'X', 2025, 6, '2025-10-09 16:35:54', '2025-10-12 14:20:27', NULL),
(3, 'B.10.1', 'ST.IKOM', 'X', 2025, 3, '2025-10-09 18:58:58', '2025-10-09 18:59:19', NULL),
(4, 'C.3.5', 'ST.IKOM', 'X', 2025, 1, '2025-10-11 07:09:19', '2025-10-11 07:09:19', NULL),
(5, 'B.1.10', 'ST.IKOM', 'X', 2025, 2, '2025-10-11 16:32:58', '2025-10-11 16:34:05', NULL),
(6, 'A.1.5', 'ST.IKOM', 'X', 2025, 4, '2025-10-12 05:30:08', '2025-10-12 06:49:25', NULL),
(7, 'A11', 'STIKOM', 'X', 2025, 9, '2025-10-19 08:36:07', '2025-10-19 09:05:33', NULL),
(8, 'B35', 'STIKOM', 'XII', 2025, 2, '2025-12-04 16:17:26', '2025-12-04 16:20:06', NULL),
(9, 'A11', 'STIKOM', 'XII', 2025, 6, '2025-12-06 04:56:04', '2025-12-06 07:08:34', NULL),
(10, 'A31', 'STIKOM', 'XII', 2025, 1, '2025-12-06 07:08:53', '2025-12-06 07:08:53', NULL),
(11, 'A15', 'STIKOM', 'XII', 2025, 1, '2025-12-06 07:09:08', '2025-12-06 07:09:08', NULL),
(12, 'A.1.3', 'ST.IKOM', 'XII', 2025, 2, '2025-12-06 07:18:02', '2025-12-15 07:24:03', NULL),
(13, 'A.3.1', 'ST.IKOM', 'XII', 2025, 2, '2025-12-15 07:36:00', '2025-12-15 17:03:43', NULL),
(14, 'B.1.1', 'ST.IKOM', 'XII', 2025, 1, '2025-12-16 04:57:41', '2025-12-16 04:57:41', NULL),
(15, 'B.1.1', 'TG', 'XII', 2025, 1, '2025-12-16 05:23:47', '2025-12-16 05:23:47', NULL),
(16, 'A.1.1', 'TG', 'XII', 2025, 1, '2025-12-16 05:58:40', '2025-12-16 05:58:40', NULL),
(17, 'A.1.1', 'ST.IKOM', 'XII', 2025, 1, '2025-12-16 15:20:35', '2025-12-16 15:20:35', NULL),
(18, 'A.1.3', 'TG', 'I', 2025, 1, '2025-12-16 18:52:14', '2025-12-16 18:52:14', NULL),
(20, 'A.1.3', 'ST.IKOM', 'I', 2026, 1, '2026-01-29 16:41:09', '2026-01-29 16:41:09', NULL),
(21, 'A.2.3', 'TG', 'XII', 2025, 1, '2026-01-29 16:50:38', '2026-01-29 16:50:38', NULL);

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
(1, 10, 1, 1, 1, 0, 1, '2025-12-15 16:12:28', '2025-12-15 16:12:28'),
(2, 1, 1, 1, 1, 0, 1, '2026-01-30 15:37:50', '2026-01-30 15:37:50');

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
(1, 3, 'surat_tugas', 9, 'Surat Tugas 001/B.10.6/TG/UNIKA/IX/2025 menunggu persetujuan Anda.', 0, '2025-09-13 23:47:54', '2025-09-13 23:47:54', '2025-09-13 23:47:54', NULL),
(2, 10, 'surat_tugas', 7, 'Surat Tugas 006/B.3.5/TG/UNIKA/VIII/2025 menunggu persetujuan Anda.', 1, '2025-09-14 06:55:01', '2025-12-03 04:33:04', '2025-09-14 06:55:01', NULL),
(3, 1, 'surat_tugas', 11, 'Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025 telah disetujui.', 1, '2025-09-14 22:34:13', '2026-01-30 15:37:43', '2025-09-14 22:34:13', '2026-01-30 15:37:43'),
(4, 16, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13', NULL),
(5, 17, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13', NULL),
(6, 18, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13', '2025-09-14 22:34:13', '2025-09-14 22:34:13', NULL),
(15, 1, 'surat_tugas', 10, 'Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025 telah disetujui.', 1, '2025-09-24 08:45:23', '2026-01-30 15:37:43', '2025-09-24 08:45:23', '2026-01-30 15:37:43'),
(16, 8, 'surat_tugas', 10, 'Anda terdaftar sebagai penerima pada Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025.', 0, '2025-09-24 08:45:23', '2025-09-24 08:45:23', '2025-09-24 08:45:23', NULL),
(17, 9, 'surat_tugas', 10, 'Anda terdaftar sebagai penerima pada Surat Tugas 002/B.7.2/TG/UNIKA/IX/2025.', 1, '2025-09-24 08:45:29', '2025-12-06 14:58:45', '2025-09-24 08:45:29', NULL),
(18, 10, 'surat_keputusan', 1, 'Surat Keputusan SK-TEST/001/FIKOM/2025 menunggu persetujuan Anda.', 1, '2025-09-29 03:12:51', '2025-12-03 04:33:04', '2025-09-29 03:12:51', NULL),
(19, 10, 'surat_keputusan', 8, 'Surat Keputusan 2644/F.1.2/FIKOM/XI/2023 menunggu persetujuan Anda.', 1, '2025-09-29 04:34:22', '2025-12-03 04:33:04', '2025-09-29 04:34:22', NULL),
(20, 1, 'surat_keputusan', 1, 'Surat Keputusan SK-TEST/001/FIKOM/2025 ditolak. Catatan: Kurang jelas', 1, '2025-09-29 08:06:00', '2026-01-30 15:37:43', '2025-09-29 08:06:00', '2026-01-30 15:37:43'),
(21, 1, 'surat_keputusan', 10, 'Surat Keputusan UT-SK-APP-001/FIKOM/2025 telah disetujui.', 1, '2025-10-02 17:58:21', '2026-01-30 15:37:43', '2025-10-02 17:58:21', '2026-01-30 15:37:43'),
(22, 1, 'surat_keputusan', 11, 'Surat Keputusan UT-SK-REJ-001/FIKOM/2025 ditolak. Catatan: Kurang jelas pada bagian dasar hukum.', 1, '2025-10-02 17:58:21', '2026-01-30 15:37:43', '2025-10-02 17:58:21', '2026-01-30 15:37:43'),
(23, 1, 'surat_keputusan', 14, 'Surat Keputusan UT-SK-APP-001/FIKOM/2025 telah disetujui.', 1, '2025-10-02 17:59:50', '2026-01-30 15:37:43', '2025-10-02 17:59:50', '2026-01-30 15:37:43'),
(24, 1, 'surat_keputusan', 15, 'Surat Keputusan UT-SK-REJ-001/FIKOM/2025 ditolak. Catatan: Kurang jelas pada bagian dasar hukum.', 1, '2025-10-02 17:59:50', '2026-01-30 15:37:43', '2025-10-02 17:59:50', '2026-01-30 15:37:43'),
(25, 10, 'surat_keputusan', 13, 'SK UT-SK-PEN-001/FIKOM/2025 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 1, '2025-10-02 18:06:07', '2025-12-03 04:33:04', '2025-10-02 18:06:07', NULL),
(26, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 1, '2025-10-02 18:45:29', '2025-12-03 04:33:04', '2025-10-02 18:45:29', NULL),
(27, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 1, '2025-10-02 18:45:31', '2025-12-03 04:33:04', '2025-10-02 18:45:31', NULL),
(28, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 1, '2025-10-02 18:45:43', '2025-12-03 04:33:04', '2025-10-02 18:45:43', NULL),
(29, 10, 'surat_keputusan', 8, 'SK 2644/F.1.2/FIKOM/XI/2023 telah direvisi oleh Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC.', 1, '2025-10-02 18:54:17', '2025-12-03 04:33:04', '2025-10-02 18:54:17', NULL),
(30, 1, 'surat_keputusan', 8, 'Surat Keputusan 2644/F.1.2/FIKOM/XI/2023 telah disetujui.', 1, '2025-10-02 19:19:58', '2026-01-30 15:37:43', '2025-10-02 19:19:58', '2026-01-30 15:37:43'),
(31, 5, 'surat_keputusan', 8, 'Anda mendapat tembusan Surat Keputusan 2644/F.1.2/FIKOM/XI/2023.', 0, '2025-10-02 19:19:58', '2025-10-02 19:19:58', '2025-10-02 19:19:58', NULL),
(32, 10, 'surat_tugas', 14, 'Surat Tugas 001/B.3.5/TG/UNIKA/X/2025 menunggu persetujuan Anda.', 1, '2025-10-03 02:58:07', '2025-10-19 09:21:23', '2025-10-03 02:58:07', NULL),
(33, 1, 'surat_tugas', 14, 'Surat Tugas 001/B.3.5/TG/UNIKA/X/2025 telah disetujui.', 1, '2025-10-03 04:14:39', '2026-01-30 15:37:43', '2025-10-03 04:14:39', '2026-01-30 15:37:43'),
(34, 13, 'surat_tugas', 14, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.3.5/TG/UNIKA/X/2025.', 0, '2025-10-03 04:14:39', '2025-10-03 04:14:39', '2025-10-03 04:14:39', NULL),
(35, 14, 'surat_tugas', 14, 'Anda terdaftar sebagai penerima pada Surat Tugas 001/B.3.5/TG/UNIKA/X/2025.', 0, '2025-10-03 04:14:46', '2025-10-03 04:14:46', '2025-10-03 04:14:46', NULL),
(36, 3, 'surat_keputusan', 17, 'SK 001/B.10.1/TG/UNIKA/X/2025 ditarik ke Draft oleh AGUSTINA ALAM ANGGITASARI, SE., MM.', 0, '2025-10-12 14:33:25', '2025-10-12 14:33:25', '2025-10-12 14:33:25', NULL),
(37, 5, 'surat_keputusan', 3, 'SK &quot;Disetujui: Penetapan Tata Tertib Laboratorium&quot; telah diterbitkan dan berlaku efektif.', 0, '2025-12-03 04:29:40', '2025-12-03 04:29:40', '2025-12-03 04:29:40', NULL),
(38, 6, 'surat_keputusan', 3, 'SK &quot;Disetujui: Penetapan Tata Tertib Laboratorium&quot; telah diterbitkan dan berlaku efektif.', 0, '2025-12-03 04:29:40', '2025-12-03 04:29:40', '2025-12-03 04:29:40', NULL),
(39, 3, 'surat_keputusan', 17, 'SK 001/B.10.1/TG/UNIKA/X/2025 ditarik ke Draft oleh AGUSTINA ALAM ANGGITASARI, SE., MM.', 0, '2025-12-06 13:08:52', '2025-12-06 13:08:52', '2025-12-06 13:08:52', NULL),
(40, 10, 'surat_tugas', 26, 'Surat Tugas 001/B.1.1/TG/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2025-12-16 05:23:49', '2025-12-16 05:23:49', '2025-12-16 05:23:49', NULL),
(41, 10, 'surat_tugas', 25, 'Surat Tugas 001/A.1.1/TG/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2025-12-16 05:58:40', '2025-12-16 05:58:40', '2025-12-16 05:58:40', NULL),
(42, 10, 'surat_tugas', 27, 'Surat Tugas 001/A.1.1/ST.IKOM/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2025-12-16 15:20:36', '2025-12-16 15:20:36', '2025-12-16 15:20:36', NULL),
(43, 10, 'surat_tugas', 28, 'Surat Tugas 001A/B.1.1/TG/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2025-12-16 15:45:05', '2025-12-16 15:45:05', '2025-12-16 15:45:05', NULL),
(44, 1, 'surat_tugas', 28, 'Surat Tugas 001A/B.1.1/TG/UNIKA/XII/2025 telah disetujui.', 0, '2025-12-16 16:12:35', '2025-12-16 16:12:35', '2025-12-16 16:12:35', NULL),
(45, 9, 'surat_tugas', 28, 'Anda menerima Surat Tugas baru: 001A/B.1.1/TG/UNIKA/XII/2025', 0, '2025-12-16 16:12:35', '2025-12-16 16:12:35', '2025-12-16 16:12:35', NULL),
(46, 1, 'surat_tugas', 27, 'Surat Tugas 001/A.1.1/ST.IKOM/UNIKA/XII/2025 telah disetujui.', 0, '2025-12-16 17:39:23', '2025-12-16 17:39:23', '2025-12-16 17:39:23', NULL),
(47, 5, 'surat_tugas', 27, 'Anda menerima Surat Tugas baru: 001/A.1.1/ST.IKOM/UNIKA/XII/2025', 0, '2025-12-16 17:39:23', '2025-12-16 17:39:23', '2025-12-16 17:39:23', NULL),
(48, 1, 'surat_tugas', 26, 'Surat Tugas 001/B.1.1/TG/UNIKA/XII/2025 telah disetujui.', 0, '2025-12-16 17:59:13', '2025-12-16 17:59:13', '2025-12-16 17:59:13', NULL),
(49, 9, 'surat_tugas', 26, 'Anda menerima Surat Tugas baru: 001/B.1.1/TG/UNIKA/XII/2025', 0, '2025-12-16 17:59:13', '2025-12-16 17:59:13', '2025-12-16 17:59:13', NULL),
(50, 1, 'surat_tugas', 25, 'Surat Tugas 001/A.1.1/TG/UNIKA/XII/2025 telah disetujui.', 0, '2025-12-16 18:50:11', '2025-12-16 18:50:11', '2025-12-16 18:50:11', NULL),
(51, 7, 'surat_tugas', 25, 'Anda menerima Surat Tugas baru: 001/A.1.1/TG/UNIKA/XII/2025', 0, '2025-12-16 18:50:11', '2025-12-16 18:50:11', '2025-12-16 18:50:11', NULL),
(52, 10, 'surat_tugas', 4, 'Surat Tugas 001/A.1.3/TG/UNIKA/I/2025 menunggu persetujuan Anda.', 0, '2025-12-16 18:52:14', '2025-12-16 18:52:14', '2025-12-16 18:52:14', NULL),
(53, 1, 'surat_tugas', 4, 'Surat Tugas 001/A.1.3/TG/UNIKA/I/2025 telah disetujui.', 1, '2025-12-16 18:52:40', '2026-02-08 15:50:38', '2025-12-16 18:52:40', NULL),
(54, 19, 'surat_tugas', 4, 'Anda menerima Surat Tugas baru: 001/A.1.3/TG/UNIKA/I/2025', 0, '2025-12-16 18:52:40', '2025-12-16 18:52:40', '2025-12-16 18:52:40', NULL),
(55, 6, 'surat_tugas', 4, 'Anda menerima Surat Tugas baru: 001/A.1.3/TG/UNIKA/I/2025', 0, '2025-12-16 18:52:40', '2025-12-16 18:52:40', '2025-12-16 18:52:40', NULL),
(56, 10, 'surat_tugas', 1, 'Surat Tugas ST-001/A.2.3/ST.IKOM/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2025-12-16 18:59:26', '2025-12-16 18:59:26', '2025-12-16 18:59:26', NULL),
(57, 1, 'surat_tugas', 1, 'Surat Tugas ST-001/A.2.3/ST.IKOM/UNIKA/XII/2025 telah disetujui.', 1, '2025-12-16 18:59:46', '2026-02-08 15:47:06', '2025-12-16 18:59:46', NULL),
(58, 5, 'surat_tugas', 1, 'Anda menerima Surat Tugas baru: ST-001/A.2.3/ST.IKOM/UNIKA/XII/2025', 0, '2025-12-16 18:59:46', '2025-12-16 18:59:46', '2025-12-16 18:59:46', NULL),
(59, 6, 'surat_tugas', 1, 'Anda menerima Surat Tugas baru: ST-001/A.2.3/ST.IKOM/UNIKA/XII/2025', 0, '2025-12-16 18:59:46', '2025-12-16 18:59:46', '2025-12-16 18:59:46', NULL),
(60, 10, 'surat_tugas', 34, 'Surat Tugas 001/A.1.3/ST.IKOM/UNIKA/I/2026 menunggu persetujuan Anda.', 0, '2026-01-29 16:41:18', '2026-01-29 16:41:18', '2026-01-29 16:41:18', NULL),
(61, 20, 'surat_tugas', 29, 'Surat Tugas 001/A.2.3/TG/UNIKA/XII/2025 menunggu persetujuan Anda.', 0, '2026-01-29 16:50:38', '2026-01-29 16:50:38', '2026-01-29 16:50:38', NULL),
(62, 1, 'surat_tugas', 34, 'Surat Tugas 001/A.1.3/ST.IKOM/UNIKA/I/2026 telah disetujui.', 1, '2026-01-29 16:58:44', '2026-02-08 15:43:10', '2026-01-29 16:58:44', NULL),
(63, 4, 'surat_tugas', 34, 'Anda menerima Surat Tugas baru: 001/A.1.3/ST.IKOM/UNIKA/I/2026', 0, '2026-01-29 16:58:44', '2026-01-29 16:58:44', '2026-01-29 16:58:44', NULL),
(64, 3, 'surat_keputusan', 17, 'Surat Keputusan 001/B.10.1/TG/UNIKA/X/2025 menunggu persetujuan Anda.', 0, '2026-01-30 03:59:58', '2026-01-30 03:59:58', '2026-01-30 03:59:58', NULL),
(65, 3, 'surat_keputusan', 17, 'Surat Keputusan 001/B.10.1/TG/UNIKA/X/2025 menunggu persetujuan Anda.', 0, '2026-01-30 04:00:06', '2026-01-30 04:00:06', '2026-01-30 04:00:06', NULL);

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
(1, 'agustina.anggitasari@unika.ac.id', '$2y$12$0rYDf0RqcBpaABHw3vaOxe3LV6UxLazy9R85vBmmwA8juagm6Xadq', 'Agustina Alam Anggitasari, Se., Mm', NULL, 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2026-02-09 04:36:25', '2026-02-09 11:36:25', NULL, 'Cp7k2NwFmT1Hk1aGZqVXjyTAIRqDSls4ykE5Ux6An7smZqmy7Z03eTlLiP9d', 'profile_photos/foto_1_1770611275.jpg'),
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
(12, 'agus.cahyo@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'AGUS CAHYO NUGROHO, S.Kom., M.T', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:02', NULL, NULL, NULL, NULL),
(13, 'andre.pamudji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ANDRE KURNIAWAN PAMUDJI, S.Kom., M.Ling', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:10', NULL, NULL, NULL, NULL),
(14, 'stephan.swastini@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'STEPHAN INGRITT SWASTINI DEWI, S.Kom., MBA', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:13', NULL, NULL, NULL, NULL),
(15, 'hironimus.leong@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HIRONIMUS LEONG, S.Kom., M.Com', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:15', NULL, NULL, NULL, NULL),
(16, 'rosita.herawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ROSITA HERAWATI, ST., MT', NULL, NULL, 4, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:41:03', NULL, NULL, NULL, NULL),
(17, 'yulianto.putranto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. YULIANTO TEDJO PUTRANTO, ST., MT', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:21', NULL, NULL, NULL, NULL),
(18, 'shinta.wahyuningrum@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'SHINTA ESTRI WAHYUNINGRUM, S.Si., M.Cs', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:25', NULL, NULL, NULL, NULL),
(19, 'setiawan.aji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'R. SETIAWAN AJI NUGROHO, ST. M.CompIT, Ph.D', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:28', NULL, NULL, NULL, NULL),
(20, 'dwi.setianto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Y.B. DWI SETIANTO, ST., M.Cs(CCNA)', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:31', NULL, NULL, NULL, NULL),
(21, 'yonathan.santosa@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'YONATHAN PURBO SANTOSA, S.Kom., M.Sc', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:47', NULL, NULL, NULL, NULL),
(22, 'henoch.christanto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HENOCH JULI CHRISTANTO, S.Kom., M.Kom', NULL, NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:52', NULL, NULL, NULL, NULL),
(23, 'test@gmail.com', '$2y$12$sMP594DayGfAYQbNBSXdyuK6FXJpY8.WtUyfrosAU9aB0al8wS.Um', 'Testing, Skom', '123', 'Tester', 1, 'aktif', '2025-12-04 08:18:17', '2026-02-09 04:36:50', NULL, NULL, NULL, NULL);

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
('ALESBi9lRiTeeAqjjbAoLUpEDnwROj7cm9kiFTg6', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS09kRXBySWZ3TjNDUGNQSU0yb2licWpIUTNreVZ6RE9ZWEdldDJGSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9zdXJhdF9zaWVnYS50ZXN0L2xvZ2luIjt9czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cDovL3N1cmF0X3NpZWdhLnRlc3QvaG9tZSI7fX0=', 1770574136),
('b88DGzQdxprUoBbx2wSyxxrUIIYXNaGvEgfDFs4R', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6IjlhTWxsb2F0NnE5YVVKbXBhQjFLaDg2WEREdXVLVGRTQ08yRTV6UDIiO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjUxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvamVuaXNfc3VyYXRfdHVnYXMvMS9zdWJfdHVnYXMiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjg6InBlcmFuX2lkIjtpOjE7czoxMDoicGVyYW5fbmFtYSI7czo4OiJhZG1pbl90dSI7czo4OiJpc19hZG1pbiI7YjoxO3M6ODoiaXNfZG9zZW4iO2I6MDtzOjEzOiJsYXN0X2FjdGl2aXR5IjtPOjI1OiJJbGx1bWluYXRlXFN1cHBvcnRcQ2FyYm9uIjozOntzOjQ6ImRhdGUiO3M6MjY6IjIwMjYtMDItMDkgMDA6MDk6MzMuMTUzODE4IjtzOjEzOiJ0aW1lem9uZV90eXBlIjtpOjM7czo4OiJ0aW1lem9uZSI7czoxMjoiQXNpYS9KYWthcnRhIjt9czo5OiJ1c2VyX25hbWUiO3M6MzQ6IkFHVVNUSU5BIEFMQU0gQU5HR0lUQVNBUkksIFNFLiwgTU0iO30=', 1770574369),
('JSZmqZccV37LTB0xOt6MZJmc8lBhPWPvLPnj3vwa', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6IlJWYUdybjZJVmdjRVdHWW1xeXM2UTZ4aXZ5bFhZVEdOMzVTSnlnS2giO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ0OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvc3VyYXRfdGVtcGxhdGVzL2NyZWF0ZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MjY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9ob21lIjt9czo3OiJ1c2VyX2lkIjtpOjE7czo5OiJ1c2VyX3JvbGUiO3M6ODoiYWRtaW5fdHUiO3M6MTI6InVzZXJfcm9sZV9pZCI7aToxO3M6OToidXNlcl9uYW1lIjtzOjM0OiJBZ3VzdGluYSBBbGFtIEFuZ2dpdGFzYXJpLCBTZS4sIE1tIjtzOjIyOiJlbnRlcmVkX2Zyb21fZGFzaGJvYXJkIjtiOjE7czoxMDoiZW50cnlfdGltZSI7TzoyNToiSWxsdW1pbmF0ZVxTdXBwb3J0XENhcmJvbiI6Mzp7czo0OiJkYXRlIjtzOjI2OiIyMDI2LTAyLTA5IDExOjI3OjE2Ljg4MDkzNyI7czoxMzoidGltZXpvbmVfdHlwZSI7aTozO3M6ODoidGltZXpvbmUiO3M6MTI6IkFzaWEvSmFrYXJ0YSI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1770611833),
('LnXlEG5Xda18fafc3bbhCdpnSUBSoV4CPpDSp3OJ', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6Im0zQVRFejY4MHlVSGtwUll1anczVjlLS2NXTmlLcUFYdTBTVGhtYnEiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcGVuZ2F0dXJhbi9ha3VuIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo4OiJwZXJhbl9pZCI7aToxO3M6MTA6InBlcmFuX25hbWEiO3M6ODoiYWRtaW5fdHUiO3M6ODoiaXNfYWRtaW4iO2I6MTtzOjg6ImlzX2Rvc2VuIjtiOjA7czoxMzoibGFzdF9hY3Rpdml0eSI7TzoyNToiSWxsdW1pbmF0ZVxTdXBwb3J0XENhcmJvbiI6Mzp7czo0OiJkYXRlIjtzOjI2OiIyMDI2LTAyLTA5IDEwOjQ3OjQ1LjAzMTY4MiI7czoxMzoidGltZXpvbmVfdHlwZSI7aTozO3M6ODoidGltZXpvbmUiO3M6MTI6IkFzaWEvSmFrYXJ0YSI7fXM6OToidXNlcl9uYW1lIjtzOjM0OiJBR1VTVElOQSBBTEFNIEFOR0dJVEFTQVJJLCBTRS4sIE1NIjt9', 1770611275),
('OAzzFe0Q2hlh7M36mffz1WoQjlwMU3aOu1r2Mu0w', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6IkNVZWh1a0ptM2llUkVQV1FkMUU5bFZKOW5nNVBwMHlYbzN2bGMySW0iO3M6MzoidXJsIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2plbmlzX3N1cmF0X3R1Z2FzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjg6InBlcmFuX2lkIjtpOjE7czoxMDoicGVyYW5fbmFtYSI7czo4OiJhZG1pbl90dSI7czo4OiJpc19hZG1pbiI7YjoxO3M6ODoiaXNfZG9zZW4iO2I6MDtzOjEzOiJsYXN0X2FjdGl2aXR5IjtPOjI1OiJJbGx1bWluYXRlXFN1cHBvcnRcQ2FyYm9uIjozOntzOjQ6ImRhdGUiO3M6MjY6IjIwMjYtMDItMDkgMDA6MTI6MDUuNzk1ODUwIjtzOjEzOiJ0aW1lem9uZV90eXBlIjtpOjM7czo4OiJ0aW1lem9uZSI7czoxMjoiQXNpYS9KYWthcnRhIjt9czo5OiJ1c2VyX25hbWUiO3M6MzQ6IkFHVVNUSU5BIEFMQU0gQU5HR0lUQVNBUkksIFNFLiwgTU0iO30=', 1770571136),
('sTpE7cxRfCctteJkh3NUldzUCkWRkcRO0LRQWepx', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6Ijh0N1o3eHZBdTQ5c0g2ZmpScGRmcVJ2cEVRanBGcHpYWjdMd2kwQWMiO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvc3VyYXRfdHVnYXMvY3JlYXRlIjt9czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo4OiJwZXJhbl9pZCI7aToxO3M6MTA6InBlcmFuX25hbWEiO3M6ODoiYWRtaW5fdHUiO3M6ODoiaXNfYWRtaW4iO2I6MTtzOjg6ImlzX2Rvc2VuIjtiOjA7czoxMzoibGFzdF9hY3Rpdml0eSI7TzoyNToiSWxsdW1pbmF0ZVxTdXBwb3J0XENhcmJvbiI6Mzp7czo0OiJkYXRlIjtzOjI2OiIyMDI2LTAyLTA4IDIzOjE3OjU1LjkxNDQ0OCI7czoxMzoidGltZXpvbmVfdHlwZSI7aTozO3M6ODoidGltZXpvbmUiO3M6MTI6IkFzaWEvSmFrYXJ0YSI7fXM6OToidXNlcl9uYW1lIjtzOjM0OiJBR1VTVElOQSBBTEFNIEFOR0dJVEFTQVJJLCBTRS4sIE1NIjt9', 1770574171);

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
(1, 1, NULL, 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, NULL, NULL),
(2, 1, NULL, 'Koordinator MK', NULL, NULL, NULL),
(3, 1, NULL, 'Koordinator Tugas MK', NULL, NULL, NULL),
(4, 1, NULL, 'Bimbingan Mahasiswa/Akademik', NULL, NULL, NULL),
(5, 7, NULL, 'Pendampingan dosen dalam KKL', NULL, NULL, NULL),
(6, 1, NULL, 'Koordinator Kerja Praktik/KKL', NULL, '2025-10-11 05:28:34', NULL),
(7, 5, NULL, 'Reviewer Kenaikan Jabatan Fungsional Lektor Kepala', NULL, NULL, NULL),
(8, 5, NULL, 'Reviewer Kenaikan Jabatan Fungsional Guru Besar', NULL, NULL, NULL),
(9, 5, NULL, 'Reviewer Kenaikan Jabatan Fungsional Asisten Ahli', NULL, NULL, NULL),
(10, 5, NULL, 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, NULL, NULL),
(11, 5, NULL, 'Asesor BKD', NULL, '2026-02-08 16:36:01', NULL),
(12, 5, NULL, 'Validator BKD', NULL, NULL, NULL),
(13, 3, NULL, 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, NULL, NULL),
(14, 6, NULL, 'Reviewer Jurnal Nasional', NULL, NULL, NULL),
(15, 6, NULL, 'Reviewer Jurnal Internasional', NULL, NULL, NULL),
(16, 8, NULL, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05', NULL),
(90, 1, NULL, 'Pembimbing Akademik (PA)', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(91, 1, NULL, 'Pembimbing Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(92, 1, NULL, 'Penguji Skripsi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(93, 1, NULL, 'Pembimbing Kerja Praktik', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(94, 2, NULL, 'Ketua Penelitian (Internal/Eksternal)', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(95, 2, NULL, 'Anggota Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(96, 2, NULL, 'Penyusun Proposal Penelitian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(97, 3, NULL, 'Ketua Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(98, 3, NULL, 'Anggota Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(99, 3, NULL, 'Narasumber/Pemateri Pengabdian', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(100, 4, NULL, 'Panitia Kegiatan Fakultas/Prodi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(101, 4, NULL, 'Pembina/Koordinator UKM/Komunitas', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(102, 4, NULL, 'Pembicara Tamu/Kuliah Umum', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(103, 5, NULL, 'Sekretaris/Koordinator Program Studi', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(104, 5, NULL, 'Panitia Seleksi/Asesor Internal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(105, 5, NULL, 'Pengembang Kurikulum/Perangkat Akademik', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(106, 6, NULL, 'Editor/Section Editor Jurnal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(107, 6, NULL, 'Pemakalah Seminar Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(108, 6, NULL, 'Pemakalah Seminar Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(109, 6, NULL, 'Penulis Jurnal Nasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(110, 6, NULL, 'Penulis Jurnal Internasional', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(111, 7, NULL, 'Narasumber/Trainer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(112, 7, NULL, 'Konsultan/Reviewer Eksternal', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(113, 8, NULL, 'Tugas Khusus Pimpinan', '2025-10-11 05:32:06', '2025-10-11 05:32:06', NULL),
(115, 1, NULL, 'Testing', '2026-02-08 17:17:33', '2026-02-08 17:19:01', '2026-02-08 17:19:01');

-- --------------------------------------------------------

--
-- Table structure for table `surat_templates`
--

CREATE TABLE `surat_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL COMMENT 'Nama template',
  `deskripsi` varchar(500) DEFAULT NULL COMMENT 'Deskripsi singkat template',
  `jenis_tugas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Kategori jenis tugas (opsional)',
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

INSERT INTO `surat_templates` (`id`, `nama`, `deskripsi`, `jenis_tugas_id`, `detail_tugas`, `tembusan`, `is_active`, `dibuat_oleh`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Template Seminar Nasional', 'Template untuk penugasan panitia atau pembicara seminar nasional', 5, '<p>Sehubungan dengan akan diselenggarakannya <strong>Seminar Nasional {{nama_kegiatan}}</strong> yang akan dilaksanakan pada:</p>\r\n<ul>\r\n<li>Hari/Tanggal: {{tanggal_pelaksanaan}}</li>\r\n<li>Waktu: {{waktu_pelaksanaan}}</li>\r\n<li>Tempat: {{tempat_pelaksanaan}}</li>\r\n</ul>\r\n<p>Maka dengan ini kami menugaskan kepada yang bersangkutan untuk:</p>\r\n<ol>\r\n<li>Menjadi {{peran_utama}} dalam kegiatan tersebut</li>\r\n<li>Mempersiapkan segala keperluan teknis dan administratif</li>\r\n<li>Menyusun laporan kegiatan setelah acara selesai</li>\r\n</ol>', 'Yth. Rektor,Yth. Wakil Rektor I,Dekan Fakultas Ilmu Komputer,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(2, 'Template Pelatihan Dosen', 'Template untuk penugasan dosen mengikuti pelatihan/workshop', 4, '<p>Dalam rangka peningkatan kompetensi dosen, dengan ini ditugaskan kepada yang bersangkutan untuk mengikuti:</p>\r\n<ul>\r\n<li>Nama Pelatihan: {{nama_pelatihan}}</li>\r\n<li>Penyelenggara: {{penyelenggara}}</li>\r\n<li>Tanggal: {{tanggal_mulai}} s.d. {{tanggal_selesai}}</li>\r\n<li>Lokasi: {{lokasi_pelatihan}}</li>\r\n</ul>\r\n<p>Yang bersangkutan berkewajiban untuk:</p>\r\n<ol>\r\n<li>Mengikuti seluruh rangkaian kegiatan pelatihan</li>\r\n<li>Menyerahkan sertifikat/bukti kehadiran</li>\r\n<li>Membuat laporan hasil pelatihan</li>\r\n</ol>', 'Yth. Wakil Rektor I,Kepala Program Studi,Unit Kepegawaian,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(3, 'Template Penugasan Penelitian', 'Template untuk penugasan melakukan penelitian', 1, '<p>Berdasarkan program penelitian Fakultas Ilmu Komputer Tahun Akademik {{tahun_akademik}}, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan penelitian dengan judul:</p>\r\n<p><strong>\"{{judul_penelitian}}\"</strong></p>\r\n<p>Penelitian dilaksanakan pada:</p>\r\n<ul>\r\n<li>Periode: {{periode_penelitian}}</li>\r\n<li>Sumber Dana: {{sumber_dana}}</li>\r\n</ul>\r\n<p>Dengan kewajiban:</p>\r\n<ol>\r\n<li>Melaksanakan penelitian sesuai proposal yang diajukan</li>\r\n<li>Menyusun laporan kemajuan dan laporan akhir</li>\r\n<li>Mempublikasikan hasil penelitian di jurnal terakreditasi</li>\r\n</ol>', 'Yth. Wakil Rektor I,Ketua Lembaga Penelitian,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(4, 'Template Pengabdian Masyarakat', 'Template untuk penugasan melakukan kegiatan pengabdian kepada masyarakat', 2, '<p>Dalam rangka melaksanakan Tri Dharma Perguruan Tinggi, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan kegiatan Pengabdian kepada Masyarakat dengan tema:</p>\r\n<p><strong>\"{{tema_pengabdian}}\"</strong></p>\r\n<p>Kegiatan dilaksanakan di:</p>\r\n<ul>\r\n<li>Lokasi: {{lokasi_pengabdian}}</li>\r\n<li>Mitra: {{nama_mitra}}</li>\r\n<li>Waktu: {{waktu_pelaksanaan}}</li>\r\n</ul>\r\n<p>Kewajiban yang harus dipenuhi:</p>\r\n<ol>\r\n<li>Melaksanakan kegiatan sesuai proposal</li>\r\n<li>Membuat dokumentasi kegiatan</li>\r\n<li>Menyusun laporan akhir kegiatan</li>\r\n</ol>', 'Yth. Wakil Rektor II,Ketua LPPM,Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 07:27:29', NULL),
(5, 'Template Kegiatan Akademik Umum', 'Template umum untuk penugasan berbagai kegiatan akademik', 7, '<p><strong>Sehubungan dengan</strong> {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:</p><ol><li>{{tugas_1}}</li><li>{{tugas_2}}</li><li>{{tugas_3}}</li></ol><p>Pelaksanaan tugas pada:</p><ul><li>Tanggal: {{tanggal_pelaksanaan}}</li><li>Waktu: {{waktu}}</li><li>Tempat: {{tempat}}</li></ul><p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>', 'Arsip', 1, 1, '2025-12-15 07:27:29', '2025-12-15 16:03:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tugas_attachments`
--

CREATE TABLE `tugas_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL COMMENT 'FK ke tugas_header',
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file original dari user',
  `nama_file_sistem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama file hasil rename sistem',
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path storage file',
  `file_size` int UNSIGNED NOT NULL COMMENT 'Ukuran file dalam bytes',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe MIME file',
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ekstensi file',
  `uploaded_by` bigint UNSIGNED NOT NULL COMMENT 'User yang upload',
  `deskripsi` text COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi/keterangan file',
  `kategori` enum('proposal','rab','surat_pengantar','dokumentasi','tor','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya' COMMENT 'Kategori dokumen',
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

INSERT INTO `tugas_header` (`id`, `nomor`, `suffix`, `parent_tugas_id`, `nomor_urut_int`, `tanggal_asli`, `status_surat`, `nomor_surat`, `tanggal_surat`, `submitted_at`, `signed_at`, `dibuat_oleh`, `dibuat_pada`, `dikunci_pada`, `file_path`, `signed_pdf_path`, `nomor_status`, `no_bin`, `tahun`, `semester`, `no_surat_manual`, `nama_umum`, `asal_surat`, `status_penerima`, `jenis_tugas`, `tugas`, `detail_tugas`, `waktu_mulai`, `waktu_selesai`, `tempat`, `redaksi_pembuka`, `penutup`, `tembusan`, `penandatangan`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `next_approver`, `created_at`, `updated_at`, `kode_surat`, `bulan`, `klasifikasi_surat_id`, `deleted_at`, `tanggal_arsip`, `arsipkan_oleh`) VALUES
(1, 'ST-001/A.2.3/ST.IKOM/UNIKA/XII/2025', NULL, NULL, NULL, '2025-05-01 00:00:00', 'disetujui', NULL, '2025-12-17', '2025-12-17 01:59:26', '2025-12-16 18:59:46', 1, '2025-06-01 22:53:10', '2025-12-16 18:59:46', NULL, 'private/surat_tugas/signed/1_ST-001A23STIKOMUNIKAXII2025_67c01b71de80befcbf4b8efccfd54bc3.pdf', 'locked', NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 1', 1, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, '2025-05-10 00:00:00', '2025-05-12 00:00:00', 'Aula UNIKA', NULL, 'Demikian, terima kasih.', NULL, 10, '{\"x\": \"7\", \"y\": \"13\", \"w_mm\": \"84\"}', '{\"x\": \"-27\", \"y\": \"4\", \"w_mm\": \"35\"}', 84, 35, 0.95, NULL, '2025-06-01 22:53:10', '2025-12-16 18:59:47', NULL, 'XII', 112, NULL, NULL, NULL),
(2, 'ST-002/UNIKA/2025', NULL, NULL, NULL, '2025-06-01 00:00:00', 'disetujui', '002/UNIKA/2025', '2025-06-01', '2025-06-02 05:53:10', NULL, 2, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, 'locked', NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 2', 2, 'tendik', 'Pelatihan', '', NULL, '2025-06-10 00:00:00', '2025-06-12 00:00:00', 'Ruang Rapat', NULL, 'Harap dilaksanakan sebaik-baiknya.', NULL, 3, NULL, NULL, NULL, NULL, NULL, 4, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, NULL, NULL, NULL, NULL),
(4, '001/A.1.3/TG/UNIKA/I/2025', NULL, NULL, NULL, '2025-07-31 00:00:00', 'disetujui', NULL, '2025-12-17', '2025-12-17 01:52:14', '2025-12-16 18:52:40', 1, '2025-07-31 11:08:30', '2025-12-16 18:52:40', NULL, 'private/surat_tugas/signed/4_001A13TGUNIKAI2025_7a33f03f619d6335d66e4f075391b2e9.pdf', 'locked', NULL, 2025, 'Genap', NULL, 'awdwdwadw', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Jogja', 'Test', NULL, '\"\"', 10, '{\"x\": \"-13\", \"y\": \"14\", \"w_mm\": \"103\"}', '{\"x\": \"-29\", \"y\": \"5\", \"w_mm\": \"35\"}', 0, 0, 1.00, NULL, '2025-07-31 04:08:30', '2025-12-16 18:52:41', NULL, 'I', 106, NULL, NULL, NULL),
(5, '003/TG/UNIKA/II/2025', NULL, NULL, NULL, '2025-07-31 00:00:00', 'pending', NULL, NULL, '2025-07-31 14:49:20', NULL, 1, '2025-07-31 11:44:02', NULL, NULL, NULL, 'reserved', 'FIKOM/006', 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 3', 4, 'dosen', 'Seminar', '', NULL, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Aula UNIKA', NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 4, '2025-07-31 04:44:02', '2025-07-31 07:49:20', NULL, NULL, NULL, NULL, NULL, NULL),
(6, '001/TG/UNIKA/I/2025', NULL, NULL, NULL, '2025-08-01 09:30:10', 'disetujui', NULL, '2025-09-04', '2025-08-01 09:30:10', '2025-09-04 00:20:32', 1, '2025-08-01 09:30:10', NULL, NULL, 'private/surat_tugas/signed/6.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, NULL, 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, '2025-08-01 09:29:00', '2025-08-01 11:29:00', NULL, NULL, NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": -38, \"offset_y\": 72, \"width_mm\": 51, \"height_mm\": 22}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 2, \"offset_y\": 70, \"width_mm\": 30}', NULL, NULL, NULL, 10, '2025-08-01 02:30:10', '2025-09-04 00:20:35', NULL, NULL, NULL, NULL, NULL, NULL),
(7, '006/B.3.5/TG/UNIKA/VIII/2025', NULL, NULL, NULL, '2025-08-02 17:30:55', 'disetujui', NULL, '2025-08-01', '2025-09-14 13:55:01', '2025-09-14 07:08:54', 1, '2025-08-02 17:30:55', NULL, NULL, 'private/surat_tugas/signed/7.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'dosen', 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', '<p><strong>Keren</strong></p>', '2025-08-02 17:29:00', '2025-08-02 19:29:00', 'HC Lt 8', 'Test', NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": 118, \"offset_y\": 17, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 20, \"base_left_mm\": 15}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 102, \"offset_y\": 14, \"width_mm\": 30, \"base_top_mm\": 15, \"base_left_mm\": 35}', NULL, NULL, NULL, 10, '2025-08-02 10:30:55', '2025-09-14 07:08:56', NULL, 'VIII', 30, NULL, NULL, NULL),
(8, '010/B.10.1/TG/UNIKA/VIII/2025', NULL, NULL, NULL, '2025-08-03 07:57:06', 'disetujui', NULL, '2025-09-03', '2025-08-03 07:57:06', '2025-09-03 13:39:16', 1, '2025-08-03 07:57:06', NULL, NULL, 'private/surat_tugas/signed/8.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'tendik', 'Bimbingan', 'Koordinator Tugas MK', '<p>WOW</p>', '2025-08-03 07:56:00', '2025-08-03 09:56:00', NULL, 'Coba', 'Coba', NULL, 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-08-03 00:57:06', '2025-09-03 13:39:18', NULL, 'VIII', 81, NULL, NULL, NULL),
(9, '001/B.10.6/TG/UNIKA/IX/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-09-14', NULL, '2025-09-14 01:27:19', 1, '2025-09-14 06:47:54', NULL, NULL, 'private/surat_tugas/signed/9.pdf', 'reserved', NULL, 2025, NULL, NULL, 'Surat Dekan', 3, 'dosen', 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, '2025-09-14 06:46:00', '2025-09-14 08:46:00', 'Ruang Teater TA', 'Sehubung', 'Demikian', NULL, 3, '{\"path\": \"private/ttd/3.png\", \"show\": true, \"offset_x\": -33, \"offset_y\": 77, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 205, \"base_left_mm\": 108}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 0, \"offset_y\": 68, \"width_mm\": 30, \"base_top_mm\": 185, \"base_left_mm\": 125}', NULL, NULL, NULL, 3, '2025-09-13 23:47:54', '2025-09-14 01:27:22', NULL, 'IX', 86, NULL, NULL, NULL),
(10, '002/B.7.2/TG/UNIKA/IX/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-09-24', '2025-09-15 12:04:37', '2025-09-24 08:45:21', 1, '2025-09-15 05:04:37', NULL, NULL, 'private/surat_tugas/signed/10_a469cf7c2029a003015973fe7c042a1c.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, 'Penugasan Tim Reviewer Jurnal Internal', 1, NULL, 'Penelitian', 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, '2025-09-20 08:00:00', '2025-10-20 17:00:00', 'Fakultas Ilmu Komputer', NULL, NULL, NULL, 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-09-15 05:04:37', '2025-09-24 08:45:23', NULL, 'IX', 54, NULL, NULL, NULL),
(11, '003/B.8.2/TG/UNIKA/IX/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-09-15', '2025-09-15 12:04:37', '2025-09-14 22:34:11', 1, '2025-09-15 05:04:37', NULL, NULL, 'private/surat_tugas/signed/11_.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Pengabdian Masyarakat', 1, NULL, 'Pengabdian', 'Validator BKD', NULL, '2025-09-22 08:00:00', '2025-09-22 17:00:00', 'Desa Binaan ABC', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.70, 3, '2025-09-15 05:04:37', '2025-09-14 22:34:13', NULL, 'IX', 63, NULL, NULL, NULL),
(12, '004/B.9.4/TG/UNIKA/IX/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-09-12', '2025-09-15 12:04:37', '2025-09-12 03:00:00', 1, '2025-09-15 05:04:37', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Wisuda', 1, NULL, 'Lainnya', 'Lainnya', NULL, '2025-09-15 08:00:00', '2025-09-20 17:00:00', 'Auditorium Albertus', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.90, NULL, '2025-09-15 05:04:37', '2025-09-15 05:04:37', NULL, 'IX', 75, NULL, NULL, NULL),
(13, '001/B.1.5/TG/UNIKA/IX/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-12-06', NULL, '2025-12-06 14:48:54', 1, '2025-09-16 19:15:15', '2025-12-06 14:48:54', NULL, 'private/surat_tugas/signed/13_001B15TGUNIKAIX2025_9b2b15f7f86472748ae9a967a9b228ad.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Bimbingan 2 Tetstste', 10, NULL, 'Bimbingan', 'Koordinator MK', '<p>Bimbingan dimulai</p>', '2025-09-16 19:14:00', '2025-09-16 21:14:00', 'Ruang HC', 'Bimbingan', 'Terimakasih', '\"\"', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-09-16 12:15:15', '2025-12-06 14:48:55', NULL, 'IX', 7, NULL, NULL, NULL),
(14, '001/B.3.5/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-03', NULL, '2025-10-03 04:14:37', 1, '2025-10-03 02:58:07', NULL, NULL, 'private/surat_tugas/signed/14_5fe72d188901a2722362f9f759809923.pdf', 'reserved', NULL, 2025, 'Ganjil', NULL, 'Penugasan Bimbingan Di Luar Kota', 10, 'dosen', 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', NULL, '2025-10-03 02:54:00', '2025-10-03 04:54:00', 'Ruang Theater', 'halo', 'siap', NULL, 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-02 19:58:07', '2025-10-03 04:14:39', NULL, 'X', 30, NULL, NULL, NULL),
(15, '001/B.1.7/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-12-06', NULL, '2025-12-06 14:48:25', 1, '2025-10-04 09:15:55', '2025-12-06 14:48:25', NULL, 'private/surat_tugas/signed/15_001B17TGUNIKAX2025_bc9d2bdb2700de504d0ae08c50db89a3.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Penugasan Draft Panitia Acara Dies Natalis', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, '2025-10-10 08:00:00', '2025-12-20 17:00:00', 'Ruang Teater dan sekitarnya', 'Dalam rangka persiapan Dies Natalis FIKOM ke-30, maka dibentuklah kepanitiaan.', 'Demikian surat tugas ini dibuat untuk dilaksanakan.', '\"Yth. Wakil Rektor I\"', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-04 09:15:55', '2025-12-06 14:48:26', NULL, 'X', 9, NULL, NULL, NULL),
(16, '002/B.3.5/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-19', '2025-10-04 16:15:55', '2025-10-19 10:00:50', 1, '2025-10-04 09:15:55', '2025-10-19 10:00:50', NULL, 'private/surat_tugas/signed/16_002B35TGUNIKAX2025_e0b1e33b3ea55f636cf6a229b52884e3.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Penugasan Dosen Pembimbing Kerja Praktik 23', 10, NULL, 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', NULL, '2025-10-05 00:00:00', '2026-01-31 23:59:00', 'Fakultas Ilmu Komputer', 'Sehubungan dengan pelaksanaan Kerja Praktik semester Ganjil 2025/2026, dengan ini menugaskan dosen sebagai pembimbing.', 'Harap melaksanakan tugas dengan sebaik-baiknya.', 'Yth. Kepala Program Studi Sistem Informasi\nKoordinator Kerja Praktik\nArsip', 3, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-04 09:15:55', '2025-10-19 10:00:52', NULL, 'X', 30, NULL, NULL, NULL),
(17, '003/DONE/ST/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-04', '2025-10-04 16:15:55', '2025-10-04 09:15:55', 1, '2025-10-04 09:15:55', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'Penugasan Tim Pengabdian Masyarakat', 10, NULL, 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, '2025-11-01 09:00:00', '2025-11-01 15:00:00', 'Desa Rowosari, Kendal', 'Menindaklanjuti program kerja fakultas bidang pengabdian kepada masyarakat, maka ditugaskan tim untuk melaksanakan kegiatan.', 'Atas perhatian dan kerjasamanya diucapkan terima kasih.', 'LPPM Unika Soegijapranata\nKepala Desa Rowosari\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-04 09:15:55', '2025-10-04 09:15:55', NULL, 'X', 63, NULL, NULL, NULL),
(18, '001/C.3.5/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-19', NULL, '2025-12-04 16:33:57', 1, '2025-10-11 07:10:50', '2025-12-04 16:33:57', NULL, 'private/surat_tugas/signed/18_001C35TGUNIKAX2025_db6802eae8467559277928ce41582c47.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Surat Tugas Pelatihan Mahasiswa Tahun 2025', 10, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', NULL, '2025-10-11 13:06:00', '2025-10-11 15:06:00', 'Ruang Theater Albertus', 'Penugasan PTMB', 'Demikian', '\"\"', 10, NULL, NULL, 30, 25, 0.95, NULL, '2025-10-11 07:10:50', '2025-12-04 16:33:58', NULL, 'X', 131, NULL, NULL, NULL),
(19, '002/B.1.10/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, NULL, NULL, '2025-10-11 17:43:35', 1, '2025-10-11 16:34:06', NULL, NULL, NULL, 'locked', NULL, 2025, 'Ganjil', NULL, 'UAS Akhir Semestar', 3, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', NULL, '2025-10-11 23:32:00', '2025-10-12 01:32:00', 'Ruang HC', 'Test', 'TYER', '\"Yth. Rektor\"', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-11 16:34:06', '2025-10-11 17:43:35', NULL, 'X', 12, NULL, NULL, NULL),
(20, '002/A.1.5/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-19', NULL, '2025-10-19 09:51:09', 1, '2025-10-12 05:30:34', '2025-10-19 09:51:09', NULL, 'private/surat_tugas/signed/20_002A15TGUNIKAX2025_f34cfe17cbdc7f739c173394e44905c1.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Bimbingan wadWdawd', 3, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', '<p>awdasdwsdwd</p>', '2025-10-12 12:29:00', '2025-10-12 14:29:00', 'Ruang HC', 'Tesad', 'tfawadas', '\"Yth. Rektor\"', 3, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-12 05:30:34', '2025-10-19 09:51:10', NULL, 'X', 108, NULL, NULL, NULL),
(21, '004/A.1.5/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-12', '2025-10-13 00:18:39', '2025-10-19 09:50:51', 1, '2025-10-12 06:49:25', '2025-10-19 09:50:51', NULL, NULL, 'locked', NULL, 2025, 'Ganjil', NULL, 'Penugasan Draft Panitia Acara Dies Natalis 26', 3, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Test</p>', '2025-10-12 13:48:00', '2025-10-12 15:48:00', 'Ruang HC', 'Test', 'Test', '\"Yth. Rektor\"', 3, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-12 06:49:25', '2025-10-19 09:50:51', NULL, 'X', 108, NULL, NULL, NULL),
(22, '009/A.1.1/TG/UNIKA/X/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-10-19', '2025-10-19 16:05:34', '2025-10-19 09:25:04', 1, '2025-10-19 09:05:34', '2025-10-19 09:25:04', NULL, 'private/surat_tugas/signed/22_009A11TGUNIKAX2025_aabc8de149ff61a8c585e5bb9f7675a2.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Surat Tugas Pelatihan Mahasiswa Tahun 2026', 10, NULL, 'Lainnya', 'Tugas Khusus Pimpinan', NULL, '2025-10-19 16:04:00', '2025-10-19 18:04:00', 'Ruang Theater Albertus', 'Test 1', 'Test', 'Yth. Rektor', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-10-19 09:05:34', '2025-10-19 09:25:08', NULL, 'X', 104, NULL, NULL, NULL),
(25, '001/A.1.1/TG/UNIKA/XII/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-12-06', '2025-12-16 12:58:40', '2025-12-16 18:50:11', 1, '2025-12-06 06:58:56', '2025-12-16 18:50:11', NULL, 'private/surat_tugas/signed/25_001A11TGUNIKAXII2025_5bd1443284d94fab0df9d17525cbedaf.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'wadawdaw3232', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', 'dwdwd', '2025-12-06 13:58:00', '2025-12-06 15:58:00', 'dwdwdw', 'dwdw', 'dwdw', 'Yth. Kepala Program Studi Sistem Informasi', 10, '{\"x\": \"-29\", \"y\": \"15\", \"w_mm\": \"63\"}', '{\"x\": \"-13\", \"y\": \"5\", \"w_mm\": \"35\"}', 0, 0, 1.00, NULL, '2025-12-06 06:58:56', '2025-12-16 18:50:12', NULL, 'XII', 104, NULL, NULL, NULL),
(26, '001/B.1.1/TG/UNIKA/XII/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-12-16', '2025-12-16 12:23:47', '2025-12-16 17:59:13', 1, '2025-12-16 04:58:34', '2025-12-16 17:59:13', NULL, 'private/surat_tugas/signed/26_001B11TGUNIKAXII2025_f97d0955b2035e76ce6b36388b47e44f.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Testingggg ke 16', 10, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', 'Dalam rangka peningkatan kompetensi dosen, dengan ini ditugaskan kepada yang bersangkutan untuk mengikuti:Nama Pelatihan: {{nama_pelatihan}}Penyelenggara: {{penyelenggara}}Tanggal: {{tanggal_mulai}} s.d. {{tanggal_selesai}}Lokasi: {{lokasi_pelatihan}}Yang bersangkutan berkewajiban untuk:Mengikuti seluruh rangkaian kegiatan pelatihanMenyerahkan sertifikat/bukti kehadiranMembuat laporan hasil pelatihan', '2025-12-16 11:57:00', '2025-12-16 13:57:00', 'Ruang HC', 'Testinggg', 'Testtinggg', 'Yth. Wakil Rektor I\nYth. Kepala Program Studi\nUnit Kepegawaian\nArsip', 10, '{\"x\": \"-23\", \"y\": \"17\"}', '{\"x\": \"-12\", \"y\": \"0\"}', 0, 0, 1.00, NULL, '2025-12-16 04:58:34', '2025-12-16 17:59:14', NULL, 'XII', 3, NULL, NULL, NULL),
(27, '001/A.1.1/ST.IKOM/UNIKA/XII/2025', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2025-12-16', '2025-12-16 22:20:36', '2025-12-16 17:39:23', 1, '2025-12-16 15:20:36', '2025-12-16 17:39:23', NULL, 'private/surat_tugas/signed/27_001A11STIKOMUNIKAXII2025_a9fb1ab30c8c60b95d5e3e2f7304c20e.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'wadawdaw3232', 10, NULL, 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, '2025-12-06 13:58:00', '2025-12-06 15:58:00', 'dwdwdw', 'dwdw', 'dwdw', 'Yth. Kepala Program Studi Sistem Informasi', 10, '{\"x\": \"5\", \"y\": \"17\"}', '{\"x\": \"-27\", \"y\": \"5\"}', 0, 0, 1.00, NULL, '2025-12-16 15:20:36', '2025-12-16 17:39:29', NULL, 'XII', 104, NULL, NULL, NULL),
(28, '001A/B.1.1/TG/UNIKA/XII/2025', 'A', 26, 1, NULL, 'disetujui', NULL, '2025-12-16', '2025-12-16 22:45:05', '2025-12-16 16:12:35', 1, '2025-12-16 15:45:05', '2025-12-16 16:12:35', NULL, 'private/surat_tugas/signed/28_001AB11TGUNIKAXII2025_19a6d23c5eea404fc6085bde47841dc4.pdf', 'locked', NULL, 2025, 'Ganjil', NULL, 'Testingggg ke 16', 10, NULL, 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', NULL, '2025-12-16 11:57:00', '2025-12-16 13:57:00', 'Ruang HC', 'Testinggg', 'Testtinggg', 'Yth. Wakil Rektor Iyth. Kepala Program Studiunit Kepegawaianarsip', 10, NULL, NULL, 42, 35, 0.95, NULL, '2025-12-16 15:45:05', '2025-12-16 16:12:39', NULL, 'XII', 3, NULL, NULL, NULL),
(29, '001/A.2.3/TG/UNIKA/XII/2025', NULL, NULL, NULL, '2025-12-10 01:45:00', 'pending', NULL, '2025-12-10', '2026-01-29 23:50:38', NULL, 1, '2025-12-16 18:45:00', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'PJ Laksmiwati (Persero) Tbk', 1, 'dosen', 'Pengabdian', 'Quidem illum est debitis eum quaerat.', NULL, '2025-12-10 09:00:00', '2025-12-10 12:00:00', 'Tegal', NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, 20, '2025-12-16 18:45:00', '2026-01-29 16:50:38', NULL, 'XII', 112, NULL, NULL, NULL),
(30, 'ST-DUMMY-002/FIKOM/2025', NULL, NULL, NULL, '2025-11-20 01:45:01', 'pending', NULL, '2025-11-20', NULL, NULL, 1, '2025-12-16 18:45:01', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'UD Yuniar Salahudin', 1, NULL, 'Pengabdian', 'Porro est dolor minima.', NULL, '2025-11-20 09:00:00', '2025-11-20 12:00:00', 'Blitar', NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL, '11', 25, NULL, NULL, NULL),
(31, 'ST-DUMMY-003/FIKOM/2025', NULL, NULL, NULL, '2025-12-08 01:45:01', 'disetujui', NULL, '2025-12-08', NULL, '2025-12-08 18:45:01', 1, '2025-12-16 18:45:01', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'Fa Prastuti (Persero) Tbk', 1, 'dosen', 'Penelitian', 'Harum aperiam rem quibusdam.', NULL, '2025-12-08 09:00:00', '2025-12-08 12:00:00', 'Denpasar', NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL, '12', 68, NULL, NULL, NULL),
(32, 'ST-DUMMY-004/FIKOM/2025', NULL, NULL, NULL, '2025-11-17 01:45:01', 'disetujui', NULL, '2025-11-17', NULL, '2025-11-17 18:45:01', 1, '2025-12-16 18:45:01', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'Yayasan Salahudin Kusumo', 1, NULL, 'Penelitian', 'Totam delectus quia rem expedita.', NULL, '2025-11-17 09:00:00', '2025-11-17 12:00:00', 'Cirebon', NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL, '11', 121, NULL, NULL, NULL),
(33, 'ST-DUMMY-005/FIKOM/2025', NULL, NULL, NULL, '2025-11-24 01:45:01', 'disetujui', NULL, '2025-11-24', NULL, '2025-11-24 18:45:01', 1, '2025-12-16 18:45:01', NULL, NULL, NULL, 'reserved', NULL, 2025, 'Ganjil', NULL, 'PJ Pudjiastuti Maryati Tbk', 1, 'dosen', 'Pengabdian', 'Quidem fugiat ut in.', NULL, '2025-11-24 09:00:00', '2025-11-24 12:00:00', 'Sungai Penuh', NULL, NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL, '11', 44, NULL, NULL, NULL),
(34, '001/A.1.3/ST.IKOM/UNIKA/I/2026', NULL, NULL, NULL, NULL, 'disetujui', NULL, '2026-01-29', '2026-01-29 23:41:18', '2026-01-29 16:58:44', 1, '2026-01-29 16:41:09', '2026-01-29 16:58:44', NULL, 'private/surat_tugas/signed/34_001A13STIKOMUNIKAI2026_e3d10f18be4985bc5331b129913b3b1a.pdf', 'locked', NULL, 2026, 'Ganjil', NULL, 'Testing aja', 10, NULL, 'TA di Luar Mengajar', 'Pendampingan dosen dalam KKL', 'Sehubungan dengan {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:{{tugas_1}}{{tugas_2}}{{tugas_3}}Pelaksanaan tugas pada:Tanggal: {{tanggal_pelaksanaan}}Waktu: {{waktu}}Tempat: {{tempat}}Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.', '2026-01-29 23:40:00', '2026-01-30 01:40:00', 'Testing', NULL, NULL, 'Arsip', 10, '{\"x\": \"16\", \"y\": \"15\", \"w_mm\": \"91\"}', '{\"x\": \"-21\", \"y\": \"5\", \"w_mm\": \"35\"}', 91, 35, 0.95, NULL, '2026-01-29 16:41:09', '2026-01-29 17:29:00', NULL, 'I', 106, NULL, NULL, NULL);

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
(2, 4, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:08:30', NULL),
(3, 5, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:44:02', NULL),
(17, 5, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 07:49:20', NULL),
(18, 6, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-01 02:30:10', NULL),
(19, 7, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 10:30:55', NULL),
(20, 8, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 00:57:06', NULL),
(21, 22, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-19 09:05:34', NULL),
(22, 22, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 09:25:04', NULL),
(23, 21, 'pending', 'disetujui', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 09:50:51', NULL),
(24, 20, 'pending', 'disetujui', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 09:51:09', NULL),
(25, 16, 'pending', 'disetujui', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 10:00:50', NULL),
(26, 18, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-04 16:33:57', NULL),
(27, 25, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-06 06:58:56', NULL),
(28, 15, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-06 14:48:25', NULL),
(29, 13, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-06 14:48:54', NULL),
(30, 26, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 04:58:34', NULL),
(31, 26, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 05:23:49', NULL),
(32, 25, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 05:58:40', NULL),
(33, 27, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 15:20:36', NULL),
(34, 28, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 15:45:05', NULL),
(35, 28, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 16:12:36', NULL),
(36, 27, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 17:39:25', NULL),
(37, 26, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 17:59:13', NULL),
(38, 25, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 18:50:11', NULL),
(39, 4, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 18:52:14', NULL),
(40, 4, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 18:52:40', NULL),
(41, 1, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', '2025-12-16 18:59:26', NULL),
(42, 1, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 18:59:46', NULL),
(43, 34, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 16:41:09', NULL),
(44, 34, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 16:41:18', NULL),
(45, 29, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 16:50:38', NULL),
(46, 34, 'pending', 'disetujui', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 16:58:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tugas_logs`
--

CREATE TABLE `tugas_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL COMMENT 'FK ke tugas_header',
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'FK ke pengguna (user yang melakukan action)',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action: created, updated, submitted, approved, rejected, deleted',
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status sebelum perubahan',
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status setelah perubahan',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Catatan tambahan',
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
(1, 1, 5, '', NULL, NULL, 'I#5', 0, '2025-12-06 06:53:27', '2025-12-16 18:59:26', '2025-12-16 18:59:26'),
(2, 1, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-16 18:59:26', '2025-12-16 18:59:26'),
(3, 2, 5, '', NULL, NULL, 'I#5', 1, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(4, 2, 4, '', NULL, NULL, 'I#4', 1, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(9, 5, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(12, 6, 4, '', NULL, NULL, 'I#4', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(17, 8, 4, '', 'Tenaga Kependidikan', NULL, 'I#4', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(21, 9, 10, '', NULL, NULL, 'I#10', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(24, 7, 3, '', 'Wakil Dekan Fakultas', NULL, 'I#3', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(25, 10, 8, '', NULL, NULL, 'I#8', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(26, 10, 9, '', NULL, NULL, 'I#9', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(27, 11, 16, '', NULL, NULL, 'I#16', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(28, 11, 17, '', NULL, NULL, 'I#17', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(29, 11, 18, '', NULL, NULL, 'I#18', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(30, 12, 4, '', NULL, NULL, 'I#4', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(31, 12, 5, '', NULL, NULL, 'I#5', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(40, 14, 13, '', 'Dosen Pengajar', NULL, 'I#13', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(41, 14, 14, '', 'Dosen Pengajar', NULL, 'I#14', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(51, 15, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(71, 19, 3, '', NULL, NULL, 'I#3', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(72, 19, 4, '', NULL, NULL, 'I#4', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(73, 19, 5, '', NULL, NULL, 'I#5', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(74, 19, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(75, 19, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(76, 19, 8, '', NULL, NULL, 'I#8', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(77, 19, 9, '', NULL, NULL, 'I#9', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(78, 19, 11, '', NULL, NULL, 'I#11', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(79, 19, 12, '', NULL, NULL, 'I#12', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(83, 20, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(86, 13, 9, '', NULL, NULL, 'I#9', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(87, 4, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(88, 4, 19, '', NULL, NULL, 'I#19', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(109, 21, 3, '', NULL, NULL, 'I#3', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(110, 21, 4, '', NULL, NULL, 'I#4', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(111, 21, 5, '', NULL, NULL, 'I#5', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(112, 21, 6, '', NULL, NULL, 'I#6', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(113, 21, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(114, 21, 8, '', NULL, NULL, 'I#8', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(115, 21, 9, '', NULL, NULL, 'I#9', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(116, 21, 10, '', NULL, NULL, 'I#10', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(117, 21, 11, '', NULL, NULL, 'I#11', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(118, 21, 12, '', NULL, NULL, 'I#12', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(120, 22, 3, '', NULL, NULL, 'I#3', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(121, 18, 11, '', NULL, NULL, 'I#11', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(123, 16, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 06:53:27', '2025-12-06 06:53:27', NULL),
(124, 25, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 06:58:56', '2025-12-06 07:59:37', '2025-12-06 07:59:37'),
(125, 25, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 07:59:37', '2025-12-06 07:59:43', '2025-12-06 07:59:43'),
(126, 25, 7, '', NULL, NULL, 'I#7', 0, '2025-12-06 07:59:43', '2025-12-06 07:59:43', NULL),
(127, 26, 9, '', NULL, NULL, 'I#9', 0, '2025-12-16 04:58:34', '2025-12-16 04:58:34', NULL),
(128, 27, 5, '', NULL, NULL, 'I#5', 0, '2025-12-16 15:20:36', '2025-12-16 15:20:36', NULL),
(129, 28, 9, '', NULL, NULL, 'I#9', 0, '2025-12-16 15:45:05', '2025-12-16 15:45:05', NULL),
(130, 29, 3, '', NULL, NULL, 'I#3', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(131, 29, 11, '', NULL, NULL, 'I#11', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(132, 30, 18, '', NULL, NULL, 'I#18', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(133, 30, 19, '', NULL, NULL, 'I#19', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(134, 31, 18, '', NULL, NULL, 'I#18', 1, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(135, 32, 17, '', NULL, NULL, 'I#17', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(136, 33, 8, '', NULL, NULL, 'I#8', 0, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(137, 33, 9, '', NULL, NULL, 'I#9', 1, '2025-12-16 18:45:01', '2025-12-16 18:45:01', NULL),
(138, 1, 5, '', NULL, NULL, 'I#5', 0, '2025-12-16 18:59:26', '2025-12-16 18:59:26', NULL),
(139, 1, 6, '', NULL, NULL, 'I#6', 0, '2025-12-16 18:59:26', '2025-12-16 18:59:26', NULL),
(140, 34, 4, '', NULL, NULL, 'I#4', 0, '2026-01-29 16:41:09', '2026-01-29 16:41:18', '2026-01-29 16:41:18'),
(141, 34, 4, '', NULL, NULL, 'I#4', 0, '2026-01-29 16:41:18', '2026-01-29 16:41:18', NULL);

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
  ADD KEY `idx_template_deleted` (`deleted_at`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `keputusan_attachments`
--
ALTER TABLE `keputusan_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `keputusan_status_logs`
--
ALTER TABLE `keputusan_status_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `nomor_counters`
--
ALTER TABLE `nomor_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tugas_log`
--
ALTER TABLE `tugas_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `tugas_logs`
--
ALTER TABLE `tugas_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

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
  ADD CONSTRAINT `fk_template_jenis_tugas` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE SET NULL;

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
