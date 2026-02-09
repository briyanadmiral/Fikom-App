<?php

namespace Database\Seeders;

use App\Models\TugasHeader;
use App\Models\TugasPenerima;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestDraftSuratTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user pembuat (Admin TU - peran_id 1)
        $pembuat = User::where('peran_id', 1)->first();

        // Ambil penandatangan (Dekan - peran_id 2)
        $penandatangan = User::where('peran_id', 2)->first();

        // Ambil beberapa user untuk penerima
        $penerima = User::where('status', 'aktif')
            ->whereIn('peran_id', [4, 5, 6])
            ->take(3)
            ->get();

        if (! $pembuat || ! $penandatangan) {
            $this->command->error('ERROR: Tidak ada user Admin TU atau Dekan di database!');

            return;
        }

        if ($penerima->count() == 0) {
            $this->command->warn('WARN: Tidak ada penerima, membuat tanpa penerima...');
        }

        // Buat surat tugas draft
        $tugas = TugasHeader::create([
            'nomor' => '',
            'nomor_status' => 'draft',
            'bulan' => 'XII',
            'tahun' => 2025,
            'semester' => 'Genap',
            'nama_umum' => 'Surat Tugas Testing - Jangan Dihapus',
            'klasifikasi_surat_id' => 1,
            'tanggal_surat' => Carbon::now()->format('Y-m-d'),
            'status_surat' => 'draft',
            'dibuat_oleh' => $pembuat->id,
            'nama_pembuat' => $pembuat->id, // ✅ FIX: Use legacy field instead of pembuat_id
            'penandatangan_id' => $penandatangan->id,
            'jenis_tugas' => 'Mengikuti Kegiatan',
            'tugas' => 'Mengikuti Workshop Testing Laravel',
            'detail_tugas' => 'Mengikuti workshop testing dan debugging pada aplikasi Laravel untuk meningkatkan kualitas development.',
            'detail_tugas_id' => 1,
            'status_penerima' => 'dosen',
            'redaksi_pembuka' => '<p>Dengan ini ditugaskan kepada yang namanya tersebut di bawah ini untuk mengikuti kegiatan workshop.</p>',
            'penutup' => '<p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>',
            'waktu_mulai' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'waktu_selesai' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'tempat' => 'Ruang Seminar Lantai 3',
            'next_approver' => null,
            'tembusan' => 'Dekan Fakultas',
            'submitted_at' => null,
        ]);

        // Tambahkan penerima
        foreach ($penerima as $p) {
            TugasPenerima::create([
                'tugas_id' => $tugas->id,
                'pengguna_id' => $p->id,
            ]);
        }

        $this->command->info('✅ SUCCESS: Surat Tugas Draft berhasil dibuat!');
        $this->command->line('─────────────────────────────────────');
        $this->command->line('ID: '.$tugas->id);
        $this->command->line('Nama: '.$tugas->nama_umum);
        $this->command->line('Status: '.$tugas->status_surat);
        $this->command->line('Pembuat: '.$pembuat->nama_lengkap);
        $this->command->line('Penandatangan: '.$penandatangan->nama_lengkap);
        $this->command->line('Jumlah Penerima: '.$penerima->count());
        if ($penerima->count() > 0) {
            $this->command->line('Penerima: '.$penerima->pluck('nama_lengkap')->join(', '));
        }
        $this->command->line('─────────────────────────────────────');
    }
}
