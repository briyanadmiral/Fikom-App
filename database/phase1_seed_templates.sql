-- =====================================================
-- PHASE 1 SEED DATA - Template Dummy Data
-- Run this AFTER phase1_migration.sql
-- Requires: jenis_tugas table to exist with data
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- First, let's check if we have jenis_tugas data
-- If not, insert some basic jenis_tugas
-- --------------------------------------------------------

INSERT IGNORE INTO `jenis_tugas` (`id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'Penelitian', NOW(), NOW()),
(2, 'Pengabdian', NOW(), NOW()),
(3, 'Mengajar', NOW(), NOW()),
(4, 'Pelatihan', NOW(), NOW()),
(5, 'Seminar', NOW(), NOW()),
(6, 'Workshop', NOW(), NOW()),
(7, 'Kegiatan Akademik', NOW(), NOW());

-- --------------------------------------------------------
-- Insert Sample Templates
-- NOTE: dibuat_oleh = 1 assumes user ID 1 exists (admin)
-- Change this if your admin has a different ID
-- --------------------------------------------------------

INSERT INTO `surat_templates` 
(`nama`, `deskripsi`, `jenis_tugas_id`, `detail_tugas`, `tembusan`, `is_active`, `dibuat_oleh`, `created_at`, `updated_at`) 
VALUES

-- Template 1: Seminar Nasional
(
    'Template Seminar Nasional',
    'Template untuk penugasan panitia atau pembicara seminar nasional',
    5, -- Seminar
    '<p>Sehubungan dengan akan diselenggarakannya <strong>Seminar Nasional {{nama_kegiatan}}</strong> yang akan dilaksanakan pada:</p>
<ul>
<li>Hari/Tanggal: {{tanggal_pelaksanaan}}</li>
<li>Waktu: {{waktu_pelaksanaan}}</li>
<li>Tempat: {{tempat_pelaksanaan}}</li>
</ul>
<p>Maka dengan ini kami menugaskan kepada yang bersangkutan untuk:</p>
<ol>
<li>Menjadi {{peran_utama}} dalam kegiatan tersebut</li>
<li>Mempersiapkan segala keperluan teknis dan administratif</li>
<li>Menyusun laporan kegiatan setelah acara selesai</li>
</ol>',
    'Yth. Rektor,Yth. Wakil Rektor I,Dekan Fakultas Ilmu Komputer,Arsip',
    1,
    1,
    NOW(),
    NOW()
),

-- Template 2: Pelatihan Dosen
(
    'Template Pelatihan Dosen',
    'Template untuk penugasan dosen mengikuti pelatihan/workshop',
    4, -- Pelatihan
    '<p>Dalam rangka peningkatan kompetensi dosen, dengan ini ditugaskan kepada yang bersangkutan untuk mengikuti:</p>
<ul>
<li>Nama Pelatihan: {{nama_pelatihan}}</li>
<li>Penyelenggara: {{penyelenggara}}</li>
<li>Tanggal: {{tanggal_mulai}} s.d. {{tanggal_selesai}}</li>
<li>Lokasi: {{lokasi_pelatihan}}</li>
</ul>
<p>Yang bersangkutan berkewajiban untuk:</p>
<ol>
<li>Mengikuti seluruh rangkaian kegiatan pelatihan</li>
<li>Menyerahkan sertifikat/bukti kehadiran</li>
<li>Membuat laporan hasil pelatihan</li>
</ol>',
    'Yth. Wakil Rektor I,Kepala Program Studi,Unit Kepegawaian,Arsip',
    1,
    1,
    NOW(),
    NOW()
),

-- Template 3: Penelitian
(
    'Template Penugasan Penelitian',
    'Template untuk penugasan melakukan penelitian',
    1, -- Penelitian
    '<p>Berdasarkan program penelitian Fakultas Ilmu Komputer Tahun Akademik {{tahun_akademik}}, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan penelitian dengan judul:</p>
<p><strong>"{{judul_penelitian}}"</strong></p>
<p>Penelitian dilaksanakan pada:</p>
<ul>
<li>Periode: {{periode_penelitian}}</li>
<li>Sumber Dana: {{sumber_dana}}</li>
</ul>
<p>Dengan kewajiban:</p>
<ol>
<li>Melaksanakan penelitian sesuai proposal yang diajukan</li>
<li>Menyusun laporan kemajuan dan laporan akhir</li>
<li>Mempublikasikan hasil penelitian di jurnal terakreditasi</li>
</ol>',
    'Yth. Wakil Rektor I,Ketua Lembaga Penelitian,Arsip',
    1,
    1,
    NOW(),
    NOW()
),

-- Template 4: Pengabdian Masyarakat
(
    'Template Pengabdian Masyarakat',
    'Template untuk penugasan melakukan kegiatan pengabdian kepada masyarakat',
    2, -- Pengabdian
    '<p>Dalam rangka melaksanakan Tri Dharma Perguruan Tinggi, dengan ini ditugaskan kepada yang bersangkutan untuk melaksanakan kegiatan Pengabdian kepada Masyarakat dengan tema:</p>
<p><strong>"{{tema_pengabdian}}"</strong></p>
<p>Kegiatan dilaksanakan di:</p>
<ul>
<li>Lokasi: {{lokasi_pengabdian}}</li>
<li>Mitra: {{nama_mitra}}</li>
<li>Waktu: {{waktu_pelaksanaan}}</li>
</ul>
<p>Kewajiban yang harus dipenuhi:</p>
<ol>
<li>Melaksanakan kegiatan sesuai proposal</li>
<li>Membuat dokumentasi kegiatan</li>
<li>Menyusun laporan akhir kegiatan</li>
</ol>',
    'Yth. Wakil Rektor II,Ketua LPPM,Arsip',
    1,
    1,
    NOW(),
    NOW()
),

-- Template 5: Kegiatan Akademik Umum
(
    'Template Kegiatan Akademik Umum',
    'Template umum untuk penugasan berbagai kegiatan akademik',
    7, -- Kegiatan Akademik
    '<p>Sehubungan dengan {{keperluan_umum}}, dengan ini ditugaskan kepada yang bersangkutan untuk:</p>
<ol>
<li>{{tugas_1}}</li>
<li>{{tugas_2}}</li>
<li>{{tugas_3}}</li>
</ol>
<p>Pelaksanaan tugas pada:</p>
<ul>
<li>Tanggal: {{tanggal_pelaksanaan}}</li>
<li>Waktu: {{waktu}}</li>
<li>Tempat: {{tempat}}</li>
</ul>
<p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>',
    'Arsip',
    1,
    1,
    NOW(),
    NOW()
);

COMMIT;

-- =====================================================
-- SEED DATA COMPLETED
-- =====================================================
-- Inserted templates:
-- 1. Template Seminar Nasional
-- 2. Template Pelatihan Dosen
-- 3. Template Penugasan Penelitian
-- 4. Template Pengabdian Masyarakat
-- 5. Template Kegiatan Akademik Umum
--
-- Placeholders format: {{nama_placeholder}}
-- These will be displayed in the template editor
-- =====================================================
