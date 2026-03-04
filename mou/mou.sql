-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Jan 2026 pada 13.14
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mou`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluasi_eksternal`
--

CREATE TABLE `evaluasi_eksternal` (
  `id_eval_eksternal` int(11) NOT NULL,
  `id_pelaksanaan` int(11) NOT NULL,
  `evaluasi` varchar(100) DEFAULT NULL,
  `tanggal_evaluasi` date DEFAULT NULL,
  `pemberi_evaluasi` varchar(100) DEFAULT NULL,
  `id_ket_evaluasi` int(11) DEFAULT NULL,
  `bukti` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `evaluasi_eksternal`
--

INSERT INTO `evaluasi_eksternal` (`id_eval_eksternal`, `id_pelaksanaan`, `evaluasi`, `tanggal_evaluasi`, `pemberi_evaluasi`, `id_ket_evaluasi`, `bukti`) VALUES
(6, 21, 'hh', '2025-11-11', 'vatri', 3, ''),
(7, 21, 'mntp', '2025-11-11', 'vatri', 1, ''),
(8, 21, 'poi', '2025-11-11', 'vatri', 1, ''),
(9, 22, 'oke sih', '2025-11-22', 'santo', 1, ''),
(10, 23, 'suka makan', '2025-11-15', 'bima', 3, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluasi_internal`
--

CREATE TABLE `evaluasi_internal` (
  `id_eval_internal` int(11) NOT NULL,
  `id_pelaksanaan` int(11) NOT NULL,
  `evaluasi` varchar(100) DEFAULT NULL,
  `tanggal_evaluasi` date DEFAULT NULL,
  `pemberi_evaluasi` varchar(100) DEFAULT NULL,
  `id_ket_evaluasi` int(11) DEFAULT NULL,
  `bukti` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `evaluasi_internal`
--

INSERT INTO `evaluasi_internal` (`id_eval_internal`, `id_pelaksanaan`, `evaluasi`, `tanggal_evaluasi`, `pemberi_evaluasi`, `id_ket_evaluasi`, `bukti`) VALUES
(5, 21, 'bgs', '2025-11-11', 'vatri', 1, ''),
(6, 22, 'bagus sudah selesai', '2025-11-22', 'burno', 1, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keterangan_evaluasi`
--

CREATE TABLE `keterangan_evaluasi` (
  `id_ket_evaluasi` int(11) NOT NULL,
  `ket_evaluasi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keterangan_evaluasi`
--

INSERT INTO `keterangan_evaluasi` (`id_ket_evaluasi`, `ket_evaluasi`) VALUES
(1, 'sudah selesai terlaksana'),
(2, 'belum terlaksana'),
(3, 'tidak selesai terlaksana');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mou`
--

CREATE TABLE `mou` (
  `id_mou` int(11) NOT NULL,
  `no_mou_eks` varchar(100) DEFAULT NULL,
  `no_mou` varchar(50) NOT NULL,
  `nama_mou` varchar(100) NOT NULL,
  `pihak_1` varchar(255) DEFAULT NULL,
  `pihak_2` varchar(255) DEFAULT NULL,
  `tingkat` enum('Universitas','Fakultas') DEFAULT NULL,
  `tgl_mou` date NOT NULL,
  `desk_mou` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mou`
--

INSERT INTO `mou` (`id_mou`, `no_mou_eks`, `no_mou`, `nama_mou`, `pihak_1`, `pihak_2`, `tingkat`, `tgl_mou`, `desk_mou`, `file`, `deleted_at`) VALUES
(16, 'mou/eks/miau', 'mou/2025/miau', 'mou kov', 'miau', 'miau-2', 'Fakultas', '2025-10-02', 'kerja sama dengan kov', 'mou_mou2025miau_20251002.pdf', NULL),
(17, 'mou/eks/01', 'mou/2025/0001', 'mou siang 11', 'siang 11', 'siang 11-2', 'Fakultas', '2025-10-03', 'kerja sama antar fakultas di siang 11', 'mou_mou20250001_20251003.pdf', NULL),
(18, 'mou/eks/20', 'mou/2025/9191', 'mou point', 'p 1', 'p 2', 'Fakultas', '2025-10-03', 'hm', 'mou_mou20259191_20251003.pdf', NULL),
(19, '1115', '1511', 'mou sabtu', 'laptop', 'computer', 'Fakultas', '2025-11-15', 'mou sabtu', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelaksanaan`
--

CREATE TABLE `pelaksanaan` (
  `id_pelaksanaan` int(11) NOT NULL,
  `id_mou` int(11) NOT NULL,
  `nama_pelaksanaan` varchar(100) NOT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `pic_kegiatan` varchar(100) DEFAULT NULL,
  `status` int(1) NOT NULL COMMENT '0 belum, 1 sudah, 2 tidak',
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelaksanaan`
--

INSERT INTO `pelaksanaan` (`id_pelaksanaan`, `id_mou`, `nama_pelaksanaan`, `tanggal_kegiatan`, `tanggal_selesai`, `pic_kegiatan`, `status`, `deleted_at`) VALUES
(21, 16, 'nama', '2025-10-31', NULL, 'miftah', 0, NULL),
(22, 19, 'magang sistem', '2025-11-16', '2025-12-16', 'burno', 0, NULL),
(23, 19, 'makrab bersama', '2025-11-21', '2025-11-23', 'miftahh', 0, NULL),
(24, 16, 'meet', '2025-11-15', '2025-11-16', 'marni', 0, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `perencanaan`
--

CREATE TABLE `perencanaan` (
  `id_perencanaan` int(11) NOT NULL,
  `id_mou` int(11) NOT NULL,
  `keg_perencanaan` text NOT NULL,
  `tanggal_rencana` date DEFAULT NULL,
  `pic_kegiatan` varchar(100) DEFAULT NULL,
  `ket` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perencanaan`
--

INSERT INTO `perencanaan` (`id_perencanaan`, `id_mou`, `keg_perencanaan`, `tanggal_rencana`, `pic_kegiatan`, `ket`, `deleted_at`) VALUES
(17, 16, 'sistem dapur', '2025-11-29', 'aileen', 'bisa di eksekusi setelah di bahas lebih lanjut di meet tgl 28\r\n', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `evaluasi_eksternal`
--
ALTER TABLE `evaluasi_eksternal`
  ADD PRIMARY KEY (`id_eval_eksternal`),
  ADD KEY `id_pelaksanaan` (`id_pelaksanaan`),
  ADD KEY `id_ket_evaluasi` (`id_ket_evaluasi`);

--
-- Indeks untuk tabel `evaluasi_internal`
--
ALTER TABLE `evaluasi_internal`
  ADD PRIMARY KEY (`id_eval_internal`),
  ADD KEY `id_pelaksanaan` (`id_pelaksanaan`),
  ADD KEY `id_ket_evaluasi` (`id_ket_evaluasi`);

--
-- Indeks untuk tabel `keterangan_evaluasi`
--
ALTER TABLE `keterangan_evaluasi`
  ADD PRIMARY KEY (`id_ket_evaluasi`);

--
-- Indeks untuk tabel `mou`
--
ALTER TABLE `mou`
  ADD PRIMARY KEY (`id_mou`);

--
-- Indeks untuk tabel `pelaksanaan`
--
ALTER TABLE `pelaksanaan`
  ADD PRIMARY KEY (`id_pelaksanaan`),
  ADD KEY `id_mou` (`id_mou`);

--
-- Indeks untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  ADD PRIMARY KEY (`id_perencanaan`),
  ADD KEY `id_mou` (`id_mou`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `evaluasi_eksternal`
--
ALTER TABLE `evaluasi_eksternal`
  MODIFY `id_eval_eksternal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `evaluasi_internal`
--
ALTER TABLE `evaluasi_internal`
  MODIFY `id_eval_internal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `keterangan_evaluasi`
--
ALTER TABLE `keterangan_evaluasi`
  MODIFY `id_ket_evaluasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `mou`
--
ALTER TABLE `mou`
  MODIFY `id_mou` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `pelaksanaan`
--
ALTER TABLE `pelaksanaan`
  MODIFY `id_pelaksanaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  MODIFY `id_perencanaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `evaluasi_eksternal`
--
ALTER TABLE `evaluasi_eksternal`
  ADD CONSTRAINT `evaluasi_eksternal_ibfk_1` FOREIGN KEY (`id_pelaksanaan`) REFERENCES `pelaksanaan` (`id_pelaksanaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluasi_eksternal_ibfk_2` FOREIGN KEY (`id_ket_evaluasi`) REFERENCES `keterangan_evaluasi` (`id_ket_evaluasi`);

--
-- Ketidakleluasaan untuk tabel `evaluasi_internal`
--
ALTER TABLE `evaluasi_internal`
  ADD CONSTRAINT `evaluasi_internal_ibfk_1` FOREIGN KEY (`id_pelaksanaan`) REFERENCES `pelaksanaan` (`id_pelaksanaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluasi_internal_ibfk_2` FOREIGN KEY (`id_ket_evaluasi`) REFERENCES `keterangan_evaluasi` (`id_ket_evaluasi`);

--
-- Ketidakleluasaan untuk tabel `pelaksanaan`
--
ALTER TABLE `pelaksanaan`
  ADD CONSTRAINT `pelaksanaan_ibfk_1` FOREIGN KEY (`id_mou`) REFERENCES `mou` (`id_mou`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perencanaan`
--
ALTER TABLE `perencanaan`
  ADD CONSTRAINT `perencanaan_ibfk_1` FOREIGN KEY (`id_mou`) REFERENCES `mou` (`id_mou`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
