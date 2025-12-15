-- =====================================================
-- PHASE 1 MIGRATION SQL - Surat Siega
-- Run this in phpMyAdmin on database: surat_siega
-- Generated: 2025-12-15
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `surat_templates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `surat_templates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL COMMENT 'Nama template',
  `deskripsi` varchar(500) DEFAULT NULL COMMENT 'Deskripsi singkat template',
  `jenis_tugas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Kategori jenis tugas (opsional)',
  `detail_tugas` text NOT NULL COMMENT 'Isi template dengan placeholder',
  `tembusan` text DEFAULT NULL COMMENT 'Tembusan default',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status aktif template',
  `dibuat_oleh` bigint UNSIGNED NOT NULL COMMENT 'User yang membuat template',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template_jenis` (`jenis_tugas_id`),
  KEY `idx_template_creator` (`dibuat_oleh`),
  KEY `idx_template_active` (`is_active`),
  KEY `idx_template_nama` (`nama`),
  KEY `idx_template_deleted` (`deleted_at`),
  CONSTRAINT `fk_template_jenis_tugas` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_template_creator` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Template surat tugas untuk reuse';

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_entity_type` (`entity_type`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log aktivitas user untuk audit trail';

-- --------------------------------------------------------
-- Insert migration record to Laravel migrations table
-- --------------------------------------------------------

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2025_12_15_130000_create_surat_templates_table', 41),
('2025_12_15_130001_create_audit_logs_table', 41);

COMMIT;

-- =====================================================
-- MIGRATION COMPLETED
-- =====================================================
-- New tables created:
-- 1. surat_templates - Template library for Surat Tugas
-- 2. audit_logs - User activity audit trail
-- =====================================================
