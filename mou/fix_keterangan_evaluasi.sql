-- ============================================================
-- FIX: Isi tabel keterangan_evaluasi yang kosong
-- Jalankan query ini di phpMyAdmin / database MOU:
-- Database: fike8938_fikom_mou
-- ============================================================

-- Kosongkan dulu jika ada data lama yang tidak valid
TRUNCATE TABLE `keterangan_evaluasi`;

-- Insert 3 status implementasi kegiatan
INSERT INTO `keterangan_evaluasi` (`id_ket_evaluasi`, `ket_evaluasi`) VALUES
(1, 'Sudah Selesai Terlaksana'),
(2, 'Belum Terlaksana'),
(3, 'Tidak Selesai Terlaksana');
