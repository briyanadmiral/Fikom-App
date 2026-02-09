<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Peran
        $peran = [
            ['nama' => 'admin_tu', 'deskripsi' => 'Admin Tata Usaha'],
            ['nama' => 'dekan', 'deskripsi' => 'Dekan'],
            ['nama' => 'wakil_dekan', 'deskripsi' => 'Wakil Dekan'],
            ['nama' => 'user', 'deskripsi' => 'User Biasa'],
        ];
        foreach ($peran as $row) {
            DB::table('peran')->insert([
                'nama' => $row['nama'],
                'deskripsi' => $row['deskripsi'],
                'dibuat_pada' => now(),
            ]);
        }

        // Semua password akan di-set jadi 123456
        $defaultPassword = Hash::make('123456');

        // Pengguna
        $users = [
            [
                'email' => 'agustina.anggitasari@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'AGUSTINA ALAM ANGGITASARI, SE., MM',
                'peran_id' => 1,
            ],
            [
                'email' => 'kariyani.spd@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'KARIYANI, S.Pd',
                'peran_id' => 1,
            ],
            [
                'email' => 'bernhardinus.harnadi@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'Prof. BERNARDINUS HARNADI, ST., MT., Ph.D.',
                'peran_id' => 3,
            ],
            [
                'email' => 'ridwan.sanjaya@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC',
                'peran_id' => 2,
            ],
            [
                'email' => 'muh.khudori@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'MUH KHUDORI',
                'peran_id' => 4,
            ],
            [
                'email' => 'bambang.setiawan@unika.ac.id',
                'sandi_hash' => $defaultPassword,
                'nama_lengkap' => 'BAMBANG SETIAWAN, ST',
                'peran_id' => 4,
            ],
        ];
        foreach ($users as $row) {
            DB::table('pengguna')->insert([
                'email' => $row['email'],
                'sandi_hash' => $row['sandi_hash'],
                'nama_lengkap' => $row['nama_lengkap'],
                'peran_id' => $row['peran_id'],
                'dibuat_pada' => now(),
            ]);
        }

        // Tugas Header (minimal 2 contoh)
        DB::table('tugas_header')->insert([
            [
                'nomor' => 'ST-001/UNIKA/2025',
                'tanggal_asli' => '2025-05-01',
                'status_surat' => 'draft',
                'nomor_surat' => null,
                'tanggal_surat' => null,
                'submitted_at' => null,
                'dibuat_oleh' => 1,
                'dibuat_pada' => now(),
                'dikunci_pada' => null,
                'file_path' => null,
                'nomor_status' => 'reserved',
                'nama_pembuat' => 1,
                'no_bin' => null,
                'tahun' => 2025,
                'semester' => 'Genap',
                'no_surat_manual' => null,
                'nama_umum' => 'Surat Tugas Kegiatan 1',
                'asal_surat' => 1,
                'status_penerima' => 'dosen',
                'jenis_tugas' => 'Seminar',
                'tugas' => 'Memberikan seminar kepada mahasiswa baru.',
                'waktu_mulai' => '2025-05-10',
                'waktu_selesai' => '2025-05-12',
                'tempat' => 'Aula UNIKA',
                'penutup' => 'Demikian, terima kasih.',
                'tembusan' => null,
                'penandatangan' => 4,
                'next_approver' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomor' => 'ST-002/UNIKA/2025',
                'tanggal_asli' => '2025-06-01',
                'status_surat' => 'disetujui',
                'nomor_surat' => '002/UNIKA/2025',
                'tanggal_surat' => '2025-06-01',
                'submitted_at' => now(),
                'dibuat_oleh' => 2,
                'dibuat_pada' => now(),
                'dikunci_pada' => now(),
                'file_path' => null,
                'nomor_status' => 'locked',
                'nama_pembuat' => 2,
                'no_bin' => null,
                'tahun' => 2025,
                'semester' => 'Genap',
                'no_surat_manual' => null,
                'nama_umum' => 'Surat Tugas Kegiatan 2',
                'asal_surat' => 2,
                'status_penerima' => 'tendik',
                'jenis_tugas' => 'Pelatihan',
                'tugas' => 'Mengikuti pelatihan administrasi kampus.',
                'waktu_mulai' => '2025-06-10',
                'waktu_selesai' => '2025-06-12',
                'tempat' => 'Ruang Rapat',
                'penutup' => 'Harap dilaksanakan sebaik-baiknya.',
                'tembusan' => null,
                'penandatangan' => 3,
                'next_approver' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Tugas Penerima (minimal 2 per surat)
        DB::table('tugas_penerima')->insert([
            [
                'tugas_id' => 1,
                'pengguna_id' => 5,
                'dibaca' => false,
            ],
            [
                'tugas_id' => 1,
                'pengguna_id' => 6,
                'dibaca' => false,
            ],
            [
                'tugas_id' => 2,
                'pengguna_id' => 5,
                'dibaca' => false,
            ],
            [
                'tugas_id' => 2,
                'pengguna_id' => 4,
                'dibaca' => true,
            ],
        ]);
    }
}
