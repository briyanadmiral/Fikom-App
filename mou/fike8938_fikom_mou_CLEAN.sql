-- ============================================================
-- DATABASE: fike8938_fikom_mou
-- Versi    : CLEAN & FIXED - 2026-07-09
-- Deskripsi: Schema lengkap sistem MOU & Evaluasi Kepuasan
--            FIKOM UNIKA Soegijapranata
--
-- CARA IMPORT:
-- 1. Buka phpMyAdmin di server
-- 2. Pilih / buat database baru: fike8938_fikom_mou
-- 3. Klik tab "Import" lalu pilih file ini
-- 4. Klik "Go"
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- TABEL: keterangan_evaluasi
-- Referensi status implementasi kegiatan
-- ============================================================

CREATE TABLE `keterangan_evaluasi` (
  `id_ket_evaluasi` int(11) NOT NULL AUTO_INCREMENT,
  `ket_evaluasi` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_ket_evaluasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data referensi status (WAJIB ADA - jangan dihapus)
INSERT INTO `keterangan_evaluasi` (`id_ket_evaluasi`, `ket_evaluasi`) VALUES
(1, 'Sudah Selesai Terlaksana'),
(2, 'Belum Terlaksana'),
(3, 'Tidak Selesai Terlaksana');

-- ============================================================
-- TABEL: mou
-- Data perjanjian kerja sama (MOU)
-- ============================================================

CREATE TABLE `mou` (
  `id_mou` int(11) NOT NULL AUTO_INCREMENT,
  `no_mou_eks` varchar(100) DEFAULT NULL,
  `no_mou` varchar(50) NOT NULL,
  `nama_mou` varchar(100) NOT NULL,
  `pihak_1` varchar(255) DEFAULT NULL,
  `pihak_2` varchar(255) DEFAULT NULL,
  `tingkat` enum('Universitas','Fakultas') DEFAULT NULL,
  `tgl_mou` date NOT NULL,
  `desk_mou` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_mou`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABEL: perencanaan
-- Rencana kegiatan dari setiap MOU
-- ============================================================

CREATE TABLE `perencanaan` (
  `id_perencanaan` int(11) NOT NULL AUTO_INCREMENT,
  `id_mou` int(11) NOT NULL,
  `keg_perencanaan` text NOT NULL,
  `tanggal_rencana` date DEFAULT NULL,
  `pic_kegiatan` varchar(100) DEFAULT NULL,
  `ket` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_perencanaan`),
  KEY `id_mou` (`id_mou`),
  CONSTRAINT `perencanaan_ibfk_1` FOREIGN KEY (`id_mou`) REFERENCES `mou` (`id_mou`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABEL: pelaksanaan
-- Realisasi kegiatan dari setiap MOU
-- status: 0 = belum, 1 = sudah, 2 = tidak
-- ============================================================

CREATE TABLE `pelaksanaan` (
  `id_pelaksanaan` int(11) NOT NULL AUTO_INCREMENT,
  `id_mou` int(11) NOT NULL,
  `nama_pelaksanaan` varchar(100) NOT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `pic_kegiatan` varchar(100) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 0 COMMENT '0=belum, 1=sudah, 2=tidak',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pelaksanaan`),
  KEY `id_mou` (`id_mou`),
  CONSTRAINT `pelaksanaan_ibfk_1` FOREIGN KEY (`id_mou`) REFERENCES `mou` (`id_mou`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABEL: evaluasi_internal
-- Evaluasi internal oleh dosen / admin
-- ============================================================

CREATE TABLE `evaluasi_internal` (
  `id_eval_internal` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelaksanaan` int(11) NOT NULL,
  `evaluasi` text DEFAULT NULL,
  `tanggal_evaluasi` date DEFAULT NULL,
  `pemberi_evaluasi` varchar(100) DEFAULT NULL,
  `id_ket_evaluasi` int(11) DEFAULT NULL,
  `bukti` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_eval_internal`),
  KEY `id_pelaksanaan` (`id_pelaksanaan`),
  KEY `id_ket_evaluasi` (`id_ket_evaluasi`),
  CONSTRAINT `evaluasi_internal_ibfk_1` FOREIGN KEY (`id_pelaksanaan`) REFERENCES `pelaksanaan` (`id_pelaksanaan`) ON DELETE CASCADE,
  CONSTRAINT `evaluasi_internal_ibfk_2` FOREIGN KEY (`id_ket_evaluasi`) REFERENCES `keterangan_evaluasi` (`id_ket_evaluasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABEL: evaluasi_eksternal
-- Evaluasi kepuasan dari klien / mitra
-- q1-q4: jawaban kuesioner kepuasan kerja sama
-- ============================================================

CREATE TABLE `evaluasi_eksternal` (
  `id_eval_eksternal` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelaksanaan` int(11) NOT NULL,
  `evaluasi` text DEFAULT NULL,
  `tanggal_evaluasi` date DEFAULT NULL,
  `pemberi_evaluasi` varchar(100) DEFAULT NULL,
  `id_ket_evaluasi` int(11) DEFAULT NULL,
  `bukti` text DEFAULT NULL,
  `q1` varchar(255) DEFAULT NULL COMMENT 'Penilaian keseluruhan program',
  `q2` varchar(255) DEFAULT NULL COMMENT 'Komunikasi dan responsivitas',
  `q3` varchar(255) DEFAULT NULL COMMENT 'Dampak positif bagi instansi',
  `q4` text DEFAULT NULL COMMENT 'Saran dan rekomendasi',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_eval_eksternal`),
  KEY `id_pelaksanaan` (`id_pelaksanaan`),
  KEY `id_ket_evaluasi` (`id_ket_evaluasi`),
  CONSTRAINT `evaluasi_eksternal_ibfk_1` FOREIGN KEY (`id_pelaksanaan`) REFERENCES `pelaksanaan` (`id_pelaksanaan`) ON DELETE CASCADE,
  CONSTRAINT `evaluasi_eksternal_ibfk_2` FOREIGN KEY (`id_ket_evaluasi`) REFERENCES `keterangan_evaluasi` (`id_ket_evaluasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

-- ============================================================
-- SELESAI
-- Tabel yang dibuat:
--   1. keterangan_evaluasi  (+ data referensi 3 baris)
--   2. mou
--   3. perencanaan
--   4. pelaksanaan
--   5. evaluasi_internal
--   6. evaluasi_eksternal   (sudah include kolom q1,q2,q3,q4 & deleted_at)
-- ============================================================
