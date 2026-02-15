-- phpMyAdmin SQL Dump
-- Database: `surat_fikom`
-- Clean Data Rewrite - Feb 13, 2026
-- Removed all test/dummy users and old transactional data
-- Fresh realistic dummy data for Surat Tugas & Surat Keputusan

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- TRUNCATE all transactional tables
-- --------------------------------------------------------
TRUNCATE TABLE `tugas_penerima`;
TRUNCATE TABLE `tugas_log`;
TRUNCATE TABLE `tugas_logs`;
TRUNCATE TABLE `tugas_attachments`;
TRUNCATE TABLE `keputusan_penerima`;
TRUNCATE TABLE `keputusan_status_logs`;
TRUNCATE TABLE `keputusan_attachments`;
TRUNCATE TABLE `notifikasi`;
TRUNCATE TABLE `notification_preferences`;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `jobs`;
TRUNCATE TABLE `failed_jobs`;
TRUNCATE TABLE `cache`;
TRUNCATE TABLE `cache_locks`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `recipient_imports`;
TRUNCATE TABLE `nomor_surat_counters`;
TRUNCATE TABLE `nomor_counters`;

-- Delete all tugas_header (child first due to parent FK)
DELETE FROM `tugas_header` WHERE `parent_tugas_id` IS NOT NULL;
DELETE FROM `tugas_header`;

-- Delete all keputusan_header
DELETE FROM `keputusan_header`;

-- Delete user_signatures
TRUNCATE TABLE `user_signatures`;

-- --------------------------------------------------------
-- Remove dummy/test users (keep only real FIKOM staff: IDs 1-22)
-- --------------------------------------------------------
DELETE FROM `pengguna` WHERE `id` > 22;

-- --------------------------------------------------------
-- Clean up test peran entries
-- --------------------------------------------------------
DELETE FROM `peran` WHERE `id` > 6;

-- --------------------------------------------------------
-- Clean up test jenis_tugas entries
-- --------------------------------------------------------
DELETE FROM `jenis_tugas` WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Clean up test klasifikasi_surat entries
-- --------------------------------------------------------
DELETE FROM `klasifikasi_surat` WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Clean up test sub_tugas entries  
-- --------------------------------------------------------
DELETE FROM `sub_tugas` WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Clean up test surat_templates entries (keep original 1-5)
-- --------------------------------------------------------
DELETE FROM `surat_templates` WHERE `id` > 5;

-- --------------------------------------------------------
-- Clean up test mengingat/menimbang library entries
-- --------------------------------------------------------
DELETE FROM `mengingat_library` WHERE `id` = 8;
DELETE FROM `menimbang_library` WHERE `id` = 6;

-- --------------------------------------------------------
-- Reset AUTO_INCREMENT values
-- --------------------------------------------------------
ALTER TABLE `pengguna` AUTO_INCREMENT = 23;
ALTER TABLE `peran` AUTO_INCREMENT = 7;
ALTER TABLE `tugas_header` AUTO_INCREMENT = 1;
ALTER TABLE `tugas_penerima` AUTO_INCREMENT = 1;
ALTER TABLE `tugas_log` AUTO_INCREMENT = 1;
ALTER TABLE `tugas_logs` AUTO_INCREMENT = 1;
ALTER TABLE `keputusan_header` AUTO_INCREMENT = 1;
ALTER TABLE `keputusan_penerima` AUTO_INCREMENT = 1;
ALTER TABLE `keputusan_status_logs` AUTO_INCREMENT = 1;
ALTER TABLE `keputusan_attachments` AUTO_INCREMENT = 1;
ALTER TABLE `notifikasi` AUTO_INCREMENT = 1;
ALTER TABLE `audit_logs` AUTO_INCREMENT = 1;
ALTER TABLE `jobs` AUTO_INCREMENT = 1;
ALTER TABLE `failed_jobs` AUTO_INCREMENT = 1;
ALTER TABLE `nomor_surat_counters` AUTO_INCREMENT = 1;
ALTER TABLE `nomor_counters` AUTO_INCREMENT = 1;
ALTER TABLE `notification_preferences` AUTO_INCREMENT = 1;
ALTER TABLE `user_signatures` AUTO_INCREMENT = 1;
ALTER TABLE `surat_templates` AUTO_INCREMENT = 6;
ALTER TABLE `jenis_tugas` AUTO_INCREMENT = 9;
ALTER TABLE `klasifikasi_surat` AUTO_INCREMENT = 190;

-- ========================================================
-- INSERT FRESH DUMMY DATA
-- ========================================================

-- --------------------------------------------------------
-- User Signatures (real users only)
-- --------------------------------------------------------
INSERT INTO `user_signatures` (`id`, `pengguna_id`, `ttd_path`, `default_width_mm`, `default_height_mm`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 10, 'private/ttd/ttd_10_1769705908.png', 35, 15, '2025-09-03 13:32:08', '2026-01-29 16:58:28', NULL),
(2, 3, 'private/ttd/3.png', 35, 15, '2025-09-14 01:26:47', '2025-09-14 01:26:47', NULL);

-- --------------------------------------------------------
-- Notification Preferences
-- --------------------------------------------------------
INSERT INTO `notification_preferences` (`id`, `pengguna_id`, `email_on_approval_needed`, `email_on_approved`, `email_on_rejected`, `email_digest_weekly`, `inapp_notifications`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00'),
(2, 1, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00'),
(3, 3, 1, 1, 1, 0, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00');

-- --------------------------------------------------------
-- Nomor Surat Counters (fresh)
-- --------------------------------------------------------
INSERT INTO `nomor_surat_counters` (`id`, `kode_surat`, `unit`, `bulan_romawi`, `tahun`, `last_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'B.7.2', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(2, 'B.3.5', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(3, 'B.8.2', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(4, 'A.1.3', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(5, 'B.1.1', 'TG', 'II', 2026, 2, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(6, 'C.3.5', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL),
(7, 'B.10.1', 'TG', 'II', 2026, 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00', NULL);

-- Nomor Counters SK
INSERT INTO `nomor_counters` (`id`, `tipe`, `tahun`, `prefix`, `last_number`, `updated_at`, `deleted_at`) VALUES
(1, 'SK', 2026, 'B.10.1/SK/UNIKA', 2, '2026-02-13 10:00:00', NULL);

-- ========================================================
-- SURAT TUGAS (Fresh Dummy Data - 10 entries)
-- ========================================================
INSERT INTO `tugas_header` (`id`, `nomor`, `suffix`, `parent_tugas_id`, `nomor_urut_int`, `tanggal_asli`, `status_surat`, `alasan_penolakan`, `nomor_surat`, `tanggal_surat`, `submitted_at`, `signed_at`, `dibuat_oleh`, `dibuat_pada`, `dikunci_pada`, `file_path`, `signed_pdf_path`, `nomor_status`, `no_bin`, `tahun`, `semester`, `no_surat_manual`, `nama_umum`, `asal_surat`, `status_penerima`, `jenis_tugas`, `tugas`, `detail_tugas`, `waktu_mulai`, `waktu_selesai`, `tempat`, `redaksi_pembuka`, `penutup`, `tembusan`, `penandatangan`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `next_approver`, `send_email_on_approve`, `created_at`, `updated_at`, `kode_surat`, `bulan`, `klasifikasi_surat_id`, `deleted_at`, `tanggal_arsip`, `arsipkan_oleh`) VALUES
(1, '001/B.7.2/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'draft', NULL, NULL, '2026-02-10', NULL, NULL, 1, '2026-02-10 08:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Koordinator MK Basis Data', 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', '<p>Ditugaskan sebagai Koordinator Mata Kuliah Basis Data untuk menyusun RPS, mengoordinasikan materi ajar, dan memastikan keseragaman penilaian antar kelas.</p>', '2026-02-17 08:00:00', '2026-06-30 17:00:00', 'Fakultas Ilmu Komputer', 'Sehubungan dengan pelaksanaan perkuliahan Semester Genap 2025/2026, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.', 'Yth. Kepala Program Studi Teknik Informatika\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-10 08:00:00', '2026-02-10 08:00:00', 'B.7.2', 'II', 54, NULL, NULL, NULL),
(2, '001/B.3.5/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'pending', NULL, NULL, '2026-02-11', '2026-02-11 09:00:00', NULL, 1, '2026-02-11 09:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Reviewer Jurnal Internal', 1, 'dosen', 'Penelitian', 'Reviewer Kenaikan Jabatan Fungsional Lektor', '<p>Ditugaskan sebagai Tim Reviewer Jurnal Internal Fakultas Ilmu Komputer untuk melakukan penilaian naskah dan menjamin kualitas publikasi.</p>', '2026-02-15 08:00:00', '2026-04-30 17:00:00', 'Ruang Rapat FIKOM', 'Sehubungan dengan kebutuhan peningkatan kualitas jurnal internal, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat agar dilaksanakan dengan sebaik-baiknya.', 'Yth. Dekan\nArsip', 10, NULL, NULL, NULL, NULL, NULL, 10, 1, '2026-02-11 09:00:00', '2026-02-11 09:00:00', 'B.3.5', 'II', 30, NULL, NULL, NULL),
(3, '001/B.8.2/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-05', '2026-02-05 10:00:00', '2026-02-06 09:00:00', 1, '2026-02-05 08:00:00', '2026-02-06 09:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Pengabdian Masyarakat Literasi Digital', 1, 'dosen', 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', '<p>Ditugaskan untuk melaksanakan pengabdian berupa pelatihan literasi digital bagi pelaku UMKM di wilayah Semarang Selatan.</p>', '2026-02-20 08:00:00', '2026-02-20 16:00:00', 'Balai Desa Tembalang, Semarang', 'Menindaklanjuti program pengabdian kepada masyarakat, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.', 'Yth. LPPM UNIKA\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-05 08:00:00', '2026-02-06 09:00:00', 'B.8.2', 'II', 63, NULL, NULL, NULL),
(4, '001/A.1.3/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'disetujui', NULL, NULL, '2026-02-03', '2026-02-03 10:00:00', '2026-02-04 08:30:00', 1, '2026-02-03 08:00:00', '2026-02-04 08:30:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Narasumber Kuliah Umum Keamanan Siber', 10, 'dosen', 'Penunjang Almamater', 'Pembicara Tamu/Kuliah Umum', '<p>Ditugaskan sebagai narasumber Kuliah Umum: <strong>Keamanan Siber dan Etika Digital</strong>.</p>', '2026-02-15 09:00:00', '2026-02-15 12:00:00', 'Aula Albertus UNIKA', 'Sehubungan dengan pelaksanaan kuliah umum, dengan ini ditugaskan kepada yang bersangkutan.', 'Demikian surat tugas ini dibuat agar dilaksanakan.', 'Yth. Dekan\nYth. Kaprodi TI\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-03 08:00:00', '2026-02-04 08:30:00', 'A.1.3', 'II', 106, NULL, NULL, NULL),
(5, '001/B.1.1/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'ditolak', 'Berkas pendukung (TOR dan RAB) belum lengkap. Mohon dilengkapi sebelum diajukan kembali.', NULL, '2026-02-08', '2026-02-08 14:00:00', NULL, 1, '2026-02-08 10:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Studi Banding Kurikulum ke Universitas Mitra', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Ditugaskan untuk studi banding kurikulum ke universitas mitra dalam rangka pengembangan kurikulum berbasis industri.</p>', '2026-02-25 08:00:00', '2026-02-27 17:00:00', 'Jakarta', 'Sehubungan dengan program pengembangan kurikulum.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Wakil Rektor I\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-08 10:00:00', '2026-02-09 09:00:00', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(6, '002/B.1.1/TG/UNIKA/II/2026', NULL, NULL, 2, NULL, 'disetujui', NULL, NULL, '2026-02-01', '2026-02-01 10:00:00', '2026-02-02 08:00:00', 1, '2026-02-01 08:00:00', '2026-02-02 08:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia Workshop Literasi Data Dosen', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Workshop Literasi Data untuk Dosen. Meliputi koordinasi peserta, kesiapan ruang, dokumentasi, dan laporan.</p>', '2026-02-10 08:00:00', '2026-02-10 16:00:00', 'Ruang HC Lt.8', 'Sehubungan dengan pelaksanaan Workshop Literasi Data.', 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.', 'Yth. Wakil Rektor I\nYth. Kepala Program Studi\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-02-01 08:00:00', '2026-02-02 08:00:00', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(7, '002A/B.1.1/TG/UNIKA/II/2026', 'A', 6, 2, NULL, 'pending', NULL, NULL, '2026-02-12', '2026-02-12 10:00:00', NULL, 1, '2026-02-12 10:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia Workshop Literasi Data (Sesi Lanjutan)', 10, 'dosen', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Surat tugas turunan untuk sesi lanjutan workshop, khususnya dukungan teknis lab dan dokumentasi video.</p>', '2026-02-17 08:00:00', '2026-02-17 16:00:00', 'Lab Komputer FIKOM', 'Sehubungan dengan pelaksanaan sesi lanjutan workshop.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Wakil Rektor I\nArsip', 10, NULL, NULL, NULL, NULL, NULL, 10, 1, '2026-02-12 10:00:00', '2026-02-12 10:00:00', 'B.1.1', 'II', 3, NULL, NULL, NULL),
(8, '001/C.3.5/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'arsip', NULL, NULL, '2026-01-15', '2026-01-15 10:00:00', '2026-01-16 08:00:00', 1, '2026-01-15 08:00:00', '2026-01-16 08:00:00', NULL, NULL, 'locked', NULL, 2026, 'Genap', NULL, 'Penugasan Panitia UAS Semester Ganjil 2025/2026', 3, 'tendik', 'Penunjang Almamater', 'Panitia Kegiatan Fakultas/Prodi', '<p>Panitia pelaksana UAS Ganjil 2025/2026: koordinasi ruang, pengawasan, dan rekapitulasi administrasi ujian.</p>', '2026-01-20 08:00:00', '2026-01-31 17:00:00', 'Kampus UNIKA - Ruang Ujian FIKOM', 'Sehubungan dengan pelaksanaan UAS, dengan ini dibentuk panitia pelaksana.', 'Harap melaksanakan tugas sesuai jadwal dan ketentuan yang berlaku.', 'Yth. Wakil Rektor I\nArsip', 10, NULL, NULL, 42, 35, 0.95, NULL, 1, '2026-01-15 08:00:00', '2026-02-05 10:00:00', 'C.3.5', 'II', 131, NULL, '2026-02-05 10:00:00', 1),
(9, '001/B.10.1/TG/UNIKA/II/2026', NULL, NULL, 1, NULL, 'draft', NULL, NULL, '2026-02-13', NULL, NULL, 1, '2026-02-13 08:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Genap', NULL, 'Penugasan Tim Penyusun Borang Akreditasi Prodi TI', 10, 'dosen', 'Penunjang Administrasi dan Manajemen', 'Sekretaris/Koordinator Program Studi', '<p>Tim Penyusun Borang Akreditasi Prodi TI: pengumpulan dokumen, validasi data, dan penyelarasan narasi.</p>', '2026-03-01 08:00:00', '2026-05-31 17:00:00', 'Fakultas Ilmu Komputer', 'Sehubungan dengan persiapan akreditasi prodi.', 'Demikian surat tugas ini dibuat untuk dapat dilaksanakan.', 'Yth. Dekan\nYth. Kaprodi TI\nArsip', 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-13 08:00:00', '2026-02-13 08:00:00', 'B.10.1', 'II', 81, NULL, NULL, NULL),
(10, '001/B.7.2/TG/UNIKA/I/2026', NULL, NULL, 1, NULL, 'pending', NULL, NULL, '2026-01-20', '2026-01-20 10:00:00', NULL, 1, '2026-01-20 08:00:00', NULL, NULL, NULL, 'reserved', NULL, 2026, 'Ganjil', NULL, 'Penugasan Penelitian Kolaboratif AI untuk Pendidikan', 1, 'dosen', 'Penelitian', 'Ketua Penelitian (Internal/Eksternal)', '<p>Penelitian kolaboratif: penerapan AI untuk pembelajaran adaptif, meliputi pengumpulan data, analisis, dan naskah publikasi.</p>', '2026-02-01 08:00:00', '2026-07-31 17:00:00', 'Kampus UNIKA', 'Dalam rangka pelaksanaan program penelitian.', 'Demikian surat tugas ini dibuat untuk dapat dipergunakan sebagaimana mestinya.', 'Yth. Kepala Pusat Penelitian\nArsip', 3, NULL, NULL, NULL, NULL, NULL, 3, 1, '2026-01-20 08:00:00', '2026-01-20 10:00:00', 'B.7.2', 'I', 54, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Tugas Penerima
-- --------------------------------------------------------
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
(19, 9, 11, '', 'Kaprodi', NULL, 'I#11', 0, '2026-02-13 08:00:00', '2026-02-13 08:00:00', NULL),
(20, 9, 16, '', 'Kaprodi', NULL, 'I#16', 0, '2026-02-13 08:00:00', '2026-02-13 08:00:00', NULL),
(21, 9, 22, '', 'Dosen Pengajar', NULL, 'I#22', 0, '2026-02-13 08:00:00', '2026-02-13 08:00:00', NULL),
(22, 10, 20, '', 'Dosen Pengajar', NULL, 'I#20', 0, '2026-01-20 08:00:00', '2026-01-20 08:00:00', NULL),
(23, 10, 21, '', 'Dosen Pengajar', NULL, 'I#21', 0, '2026-01-20 08:00:00', '2026-01-20 08:00:00', NULL);

-- --------------------------------------------------------
-- Tugas Log
-- --------------------------------------------------------
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
(23, 10, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0', '2026-01-20 10:00:00', NULL);

-- ========================================================
-- SURAT KEPUTUSAN (Fresh Dummy Data - 6 entries)
-- ========================================================
INSERT INTO `keputusan_header` (`id`, `nomor`, `tanggal_surat`, `tahun`, `kota_penetapan`, `signed_at`, `tentang`, `judul_penetapan`, `menimbang`, `mengingat`, `menetapkan`, `memutuskan`, `signed_pdf_path`, `tembusan`, `tembusan_formatted`, `penerima_eksternal`, `status_surat`, `dibuat_oleh`, `penandatangan`, `npp_penandatangan`, `approved_by`, `approved_at`, `tanggal_terbit`, `terbitkan_oleh`, `tanggal_arsip`, `arsipkan_oleh`, `rejected_by`, `rejected_at`, `published_by`, `published_at`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, '2026-02-13', 2026, 'Semarang', NULL, 'Penetapan Visi, Misi, dan Tujuan Fakultas Ilmu Komputer Periode 2026-2030', NULL, '["bahwa Fakultas Ilmu Komputer memerlukan penyesuaian visi, misi, dan tujuan sesuai perkembangan teknologi terkini", "bahwa berdasarkan keputusan Rapat Senat Fakultas pada tanggal 10 Januari 2026 diperlukan peninjauan kembali visi dan misi"]', '["Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi", "Statuta Universitas Katolik Soegijapranata", "Peraturan Menteri Pendidikan Nomor 3 Tahun 2020 tentang SN-Dikti"]', '[{"isi": "<p>Menetapkan Visi Fakultas Ilmu Komputer: Menjadi fakultas unggulan di bidang ilmu komputer yang menghasilkan lulusan berkarakter dan berdaya saing global.</p>", "judul": "KESATU"}, {"isi": "<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>", "judul": "KEDUA"}]', '<p><strong>KESATU:</strong> <p>Menetapkan Visi Fakultas Ilmu Komputer.</p></p>\n<p><strong>KEDUA:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', NULL, 'Yth. Rektor\nYth. Wakil Rektor I\nArsip', NULL, '[]', 'draft', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 08:00:00', '2026-02-13 08:00:00', NULL),

(2, '001/B.10.1/SK/UNIKA/FIKOM/II/2026', '2026-02-05', 2026, 'Semarang', NULL, 'Pengangkatan Panitia Pelaksana Seminar Nasional Teknologi Informasi 2026', 'KEPUTUSAN DEKAN TENTANG PENGANGKATAN PANITIA SEMINAR NASIONAL TI 2026', '["bahwa untuk kelancaran pelaksanaan Seminar Nasional Teknologi Informasi 2026 diperlukan pembentukan panitia pelaksana"]', '["Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi", "Statuta Universitas Katolik Soegijapranata"]', '[{"isi": "<p>Membentuk Panitia Pelaksana Seminar Nasional TI 2026 dengan susunan: Ketua Panitia, Sekretaris, Bendahara, dan Koordinator Acara.</p>", "judul": "KESATU"}, {"isi": "<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>", "judul": "KEDUA"}]', '<p><strong>KESATU:</strong> <p>Membentuk Panitia Pelaksana Seminar Nasional TI 2026.</p></p>\n<p><strong>KEDUA:</strong> <p>Keputusan ini berlaku sejak tanggal ditetapkan.</p></p>', NULL, 'Yth. Dekan Fakultas Ilmu Komputer', NULL, '[]', 'pending', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 08:00:00', '2026-02-07 10:00:00', NULL),

(3, '002/B.10.1/SK/UNIKA/FIKOM/II/2026', '2026-02-01', 2026, 'Semarang', '2026-02-03 09:00:00', 'Penetapan Kurikulum Program Studi Teknik Informatika Tahun 2026', 'KEPUTUSAN DEKAN TENTANG PENETAPAN KURIKULUM PRODI TI 2026', '["bahwa kurikulum perlu disesuaikan dengan kebutuhan industri dan perkembangan teknologi", "bahwa hasil evaluasi kurikulum lama menunjukkan perlunya pembaruan"]', '["Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi", "Peraturan Menteri Pendidikan Nomor 3 Tahun 2020 tentang SN-Dikti", "SK Rektor tentang Pedoman Kurikulum"]', '[{"isi": "<p>Menetapkan Kurikulum Prodi TI 2026 yang berlaku mulai Semester Genap 2025/2026.</p>", "judul": "KESATU"}, {"isi": "<p>Biaya yang ditimbulkan dibebankan pada anggaran fakultas.</p>", "judul": "KEDUA"}, {"isi": "<p>Keputusan ini berlaku sejak tanggal ditetapkan.</p>", "judul": "KETIGA"}]', '<p><strong>KESATU:</strong> Menetapkan Kurikulum Prodi TI 2026.</p>\n<p><strong>KEDUA:</strong> Biaya dibebankan pada anggaran fakultas.</p>\n<p><strong>KETIGA:</strong> Berlaku sejak tanggal ditetapkan.</p>', NULL, 'Yth. Rektor\nYth. Wakil Rektor I\nKaprodi TI\nArsip', NULL, NULL, 'disetujui', 1, 10, '058.1.2002.255', 10, '2026-02-03 09:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37, 37, 0.95, '2026-02-01 08:00:00', '2026-02-03 09:00:00', NULL),

(4, NULL, '2026-02-10', 2026, 'Semarang', NULL, 'Penunjukan Dosen Pembimbing Kerja Praktik Semester Genap 2025/2026', NULL, '["bahwa pelaksanaan Kerja Praktik memerlukan pembimbing yang kompeten", "bahwa perlu ditetapkan dosen pembimbing secara resmi"]', '["Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi", "Pedoman Akademik UNIKA tentang Kerja Praktik"]', '[{"isi": "<p>Menunjuk dosen pembimbing KP Semester Genap 2025/2026 sesuai lampiran.</p>", "judul": "KESATU"}]', '<p><strong>KESATU:</strong> Menunjuk dosen pembimbing KP.</p>', NULL, 'Yth. Kaprodi TI\nYth. Kaprodi SI\nArsip', NULL, '[]', 'ditolak', 1, 10, '058.1.2002.255', NULL, NULL, NULL, NULL, NULL, NULL, 10, '2026-02-12 09:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-10 08:00:00', '2026-02-12 09:00:00', NULL),

(5, NULL, '2026-01-10', 2026, 'Semarang', '2026-01-15 09:00:00', 'Penetapan Jadwal Ujian Akhir Semester Ganjil 2025/2026', 'KEPUTUSAN DEKAN TENTANG JADWAL UAS GANJIL 2025/2026', '["bahwa pelaksanaan UAS memerlukan jadwal yang terkoordinasi"]', '["Undang-Undang Nomor 12 Tahun 2012 tentang Pendidikan Tinggi", "Kalender Akademik UNIKA 2025/2026"]', '[{"isi": "<p>Menetapkan jadwal UAS Ganjil 2025/2026 berlangsung 20-31 Januari 2026.</p>", "judul": "KESATU"}, {"isi": "<p>Berlaku sejak tanggal ditetapkan.</p>", "judul": "KEDUA"}]', '<p><strong>KESATU:</strong> Jadwal UAS 20-31 Januari 2026.</p>\n<p><strong>KEDUA:</strong> Berlaku sejak ditetapkan.</p>', NULL, 'Seluruh Dosen FIKOM\nArsip', NULL, NULL, 'terbit', 1, 10, '058.1.2002.255', 10, '2026-01-15 09:00:00', '2026-01-16 10:00:00', 1, NULL, NULL, NULL, NULL, 1, '2026-01-16 10:00:00', NULL, NULL, 37, 37, 0.95, '2026-01-10 08:00:00', '2026-01-16 10:00:00', NULL),

(6, NULL, '2026-01-05', 2026, 'Semarang', '2026-01-08 09:00:00', 'Penetapan Dosen Wali Akademik Semester Genap 2025/2026', NULL, '["bahwa setiap mahasiswa memerlukan pendampingan akademik melalui dosen wali"]', '["Pedoman Akademik UNIKA", "Statuta Universitas"]', '[{"isi": "<p>Menetapkan dosen wali akademik semester genap 2025/2026 sesuai lampiran.</p>", "judul": "KESATU"}]', '<p><strong>KESATU:</strong> Menetapkan dosen wali akademik.</p>', NULL, 'Arsip', NULL, '[]', 'arsip', 1, 10, '058.1.2002.255', 10, '2026-01-08 09:00:00', '2026-01-09 10:00:00', 1, '2026-02-10 10:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 08:00:00', '2026-02-10 10:00:00', NULL);

-- --------------------------------------------------------
-- Keputusan Penerima
-- --------------------------------------------------------
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
-- Keputusan Status Logs
-- --------------------------------------------------------
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
(12, 6, 'terbit', 'arsip', 1, 'Diarsipkan', '2026-02-10 10:00:00');

-- --------------------------------------------------------
-- Notifikasi (sample)
-- --------------------------------------------------------
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
(11, 1, 'surat_keputusan', 4, 'SK Penunjukan Dosen Pembimbing KP ditolak.', 0, '2026-02-12 09:00:00', '2026-02-12 09:00:00', '2026-02-12 09:00:00', NULL);

-- --------------------------------------------------------
-- Final AUTO_INCREMENT reset
-- --------------------------------------------------------
ALTER TABLE `tugas_header` AUTO_INCREMENT = 11;
ALTER TABLE `tugas_penerima` AUTO_INCREMENT = 24;
ALTER TABLE `tugas_log` AUTO_INCREMENT = 24;
ALTER TABLE `keputusan_header` AUTO_INCREMENT = 7;
ALTER TABLE `keputusan_penerima` AUTO_INCREMENT = 11;
ALTER TABLE `keputusan_status_logs` AUTO_INCREMENT = 13;
ALTER TABLE `notifikasi` AUTO_INCREMENT = 12;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
