-- =====================================================
-- PHASE 2 & 3 MIGRATION SQL - Surat Siega
-- Run this in phpMyAdmin on database: surat_siega
-- Generated: 2025-12-15
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Phase 2.1: Recipient Imports Table (for bulk import tracking)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `recipient_imports` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint UNSIGNED NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
    `total_rows` int DEFAULT 0,
    `success_count` int DEFAULT 0,
    `error_count` int DEFAULT 0,
    `errors` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    CONSTRAINT `fk_recipient_imports_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tracking bulk recipient import jobs';

-- --------------------------------------------------------
-- Phase 2.2: Menimbang Library Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `menimbang_library` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `judul` varchar(200) NOT NULL COMMENT 'Judul singkat poin menimbang',
    `isi` text NOT NULL COMMENT 'Isi lengkap poin menimbang',
    `kategori` varchar(50) DEFAULT NULL COMMENT 'Kategori: akademik, kepegawaian, keuangan, dll',
    `tags` json DEFAULT NULL COMMENT 'Tag pencarian',
    `dibuat_oleh` bigint UNSIGNED NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `usage_count` int DEFAULT 0 COMMENT 'Jumlah penggunaan',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_kategori` (`kategori`),
    KEY `idx_active` (`is_active`),
    KEY `idx_creator` (`dibuat_oleh`),
    KEY `idx_deleted` (`deleted_at`),
    FULLTEXT KEY `ft_search` (`judul`, `isi`),
    CONSTRAINT `fk_menimbang_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Library poin menimbang untuk SK';

-- --------------------------------------------------------
-- Phase 2.3: Mengingat Library Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `mengingat_library` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `judul` varchar(200) NOT NULL COMMENT 'Judul dasar hukum',
    `isi` text NOT NULL COMMENT 'Isi lengkap referensi',
    `kategori` varchar(50) DEFAULT NULL COMMENT 'Kategori: UU, PP, Permen, SK Rektor, dll',
    `nomor_referensi` varchar(100) DEFAULT NULL COMMENT 'Nomor UU/PP/SK',
    `tanggal_referensi` date DEFAULT NULL COMMENT 'Tanggal penetapan',
    `dibuat_oleh` bigint UNSIGNED NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `usage_count` int DEFAULT 0 COMMENT 'Jumlah penggunaan',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_kategori` (`kategori`),
    KEY `idx_nomor` (`nomor_referensi`),
    KEY `idx_active` (`is_active`),
    KEY `idx_creator` (`dibuat_oleh`),
    KEY `idx_deleted` (`deleted_at`),
    FULLTEXT KEY `ft_search` (`judul`, `isi`, `nomor_referensi`),
    CONSTRAINT `fk_mengingat_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Library dasar hukum untuk SK';

-- --------------------------------------------------------
-- Phase 3.2: Extend master_kop_surat for Multi-Unit
-- --------------------------------------------------------

-- Check if columns already exist, skip if they do
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'master_kop_surat' AND COLUMN_NAME = 'unit_code');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `master_kop_surat` 
     ADD COLUMN `unit_code` varchar(50) DEFAULT NULL AFTER `id`,
     ADD COLUMN `nama_kop` varchar(100) DEFAULT NULL AFTER `unit_code`,
     ADD COLUMN `is_default` tinyint(1) DEFAULT 0 AFTER `nama_kop`', 
    'SELECT "Columns already exist, skipping..." AS Info');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for unit lookup
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'master_kop_surat' AND INDEX_NAME = 'idx_kop_unit');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX `idx_kop_unit` ON `master_kop_surat` (`unit_code`)', 
    'SELECT "Index already exists, skipping..." AS Info');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing record to be default
UPDATE `master_kop_surat` SET `is_default` = 1, `nama_kop` = 'Kop Default FIKOM' WHERE `id` = 1 AND `nama_kop` IS NULL;

-- --------------------------------------------------------
-- Phase 3.4: Notification Preferences Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `pengguna_id` bigint UNSIGNED NOT NULL,
    `email_on_approval_needed` tinyint(1) DEFAULT 1 COMMENT 'Notifikasi email saat ada dokumen perlu disetujui',
    `email_on_approved` tinyint(1) DEFAULT 1 COMMENT 'Notifikasi email saat dokumen disetujui',
    `email_on_rejected` tinyint(1) DEFAULT 1 COMMENT 'Notifikasi email saat dokumen ditolak',
    `email_digest_weekly` tinyint(1) DEFAULT 0 COMMENT 'Kirim digest mingguan dokumen pending',
    `inapp_notifications` tinyint(1) DEFAULT 1 COMMENT 'Notifikasi in-app',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_user` (`pengguna_id`),
    CONSTRAINT `fk_notifpref_user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Preferensi notifikasi per user';

-- --------------------------------------------------------
-- Insert migration records
-- --------------------------------------------------------

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2025_12_15_140000_create_recipient_imports_table', 42),
('2025_12_15_140001_create_menimbang_library_table', 42),
('2025_12_15_140002_create_mengingat_library_table', 42),
('2025_12_15_140003_extend_master_kop_surat_multiunit', 42),
('2025_12_15_140004_create_notification_preferences_table', 42);

COMMIT;

-- =====================================================
-- SAMPLE DATA for Testing
-- =====================================================

START TRANSACTION;

-- Sample Menimbang Library Data
INSERT INTO `menimbang_library` (`judul`, `isi`, `kategori`, `tags`, `dibuat_oleh`, `is_active`, `usage_count`, `created_at`, `updated_at`) VALUES
('Kelancaran Pelaksanaan Seminar', 'bahwa untuk kelancaran pelaksanaan Seminar Nasional diperlukan pembentukan panitia pelaksana yang kompeten dan berdedikasi', 'akademik', '["seminar", "panitia", "kegiatan"]', 1, 1, 0, NOW(), NOW()),
('Peningkatan Kualitas Pendidikan', 'bahwa dalam rangka peningkatan kualitas pendidikan dan pembelajaran di lingkungan Fakultas Ilmu Komputer perlu dilakukan evaluasi dan penyempurnaan kurikulum secara berkala', 'akademik', '["kurikulum", "pendidikan", "evaluasi"]', 1, 1, 0, NOW(), NOW()),
('Pengembangan Tri Dharma', 'bahwa Tri Dharma Perguruan Tinggi meliputi pendidikan, penelitian, dan pengabdian kepada masyarakat yang harus dilaksanakan secara berimbang dan berkelanjutan', 'umum', '["tri dharma", "penelitian", "pengabdian"]', 1, 1, 0, NOW(), NOW()),
('Pelaksanaan Wisuda', 'bahwa dalam rangka pelaksanaan Wisuda Periode Oktober 2025 diperlukan pembentukan panitia yang bertugas mempersiapkan dan melaksanakan acara tersebut', 'akademik', '["wisuda", "kelulusan", "panitia"]', 1, 1, 0, NOW(), NOW()),
('Akreditasi Program Studi', 'bahwa untuk mempertahankan dan meningkatkan akreditasi program studi perlu dilakukan persiapan dokumen dan evaluasi diri secara komprehensif', 'akreditasi', '["akreditasi", "borang", "evaluasi diri"]', 1, 1, 0, NOW(), NOW());

-- Sample Mengingat Library Data
INSERT INTO `mengingat_library` (`judul`, `isi`, `kategori`, `nomor_referensi`, `tanggal_referensi`, `dibuat_oleh`, `is_active`, `usage_count`, `created_at`, `updated_at`) VALUES
('UU Pendidikan Tinggi', 'Undang-Undang Republik Indonesia Nomor 12 Tahun 2012 tentang Pendidikan Tinggi', 'UU', 'UU No. 12 Tahun 2012', '2012-08-10', 1, 1, 0, NOW(), NOW()),
('PP Penyelenggaraan Pendidikan Tinggi', 'Peraturan Pemerintah Republik Indonesia Nomor 4 Tahun 2014 tentang Penyelenggaraan Pendidikan Tinggi dan Pengelolaan Perguruan Tinggi', 'PP', 'PP No. 4 Tahun 2014', '2014-01-17', 1, 1, 0, NOW(), NOW()),
('Statuta Unika', 'Keputusan Yayasan Sandjojo Nomor 66/PER/YS/05/VII/2013 tentang Statuta Universitas Katolik Soegijapranata', 'SK Yayasan', 'No. 66/PER/YS/05/VII/2013', '2013-07-01', 1, 1, 0, NOW(), NOW()),
('Peraturan Akademik Unika', 'Peraturan Universitas Katolik Soegijapranata tentang Pedoman Akademik', 'Peraturan Internal', 'No. E.2/1616/UKS.01/VII/2001', '2001-07-01', 1, 1, 0, NOW(), NOW()),
('SK SN-Dikti', 'Peraturan Menteri Pendidikan dan Kebudayaan Republik Indonesia Nomor 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi', 'Permen', 'Permendikbud No. 3 Tahun 2020', '2020-01-24', 1, 1, 0, NOW(), NOW()),
('UU SISDIKNAS', 'Undang-Undang Republik Indonesia Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional', 'UU', 'UU No. 20 Tahun 2003', '2003-07-08', 1, 1, 0, NOW(), NOW()),
('Permenristekdikti SNPT', 'Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi Republik Indonesia Nomor 44 Tahun 2015 tentang Standar Nasional Pendidikan Tinggi', 'Permen', 'Permenristekdikti No. 44 Tahun 2015', '2015-12-28', 1, 1, 0, NOW(), NOW());

COMMIT;

-- =====================================================
-- MIGRATION COMPLETED
-- =====================================================
-- New tables created:
-- 1. recipient_imports - Track bulk import jobs
-- 2. menimbang_library - Library poin "menimbang" untuk SK
-- 3. mengingat_library - Library dasar hukum untuk SK
-- 4. notification_preferences - Preferensi notifikasi per user
--
-- Modified tables:
-- 1. master_kop_surat - Added multi-unit support
--
-- Sample data inserted for testing.
-- =====================================================
