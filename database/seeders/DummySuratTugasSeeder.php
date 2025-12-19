<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TugasHeader;
use App\Models\User;
use App\Models\KlasifikasiSurat;
use App\Models\TugasPenerima;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class DummySuratTugasSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Ambil user
        $users = User::all();
        if ($users->count() < 2) {
            $this->command->error('Butuh minimal 2 user di database.');
            return;
        }

        $admin = $users->first();
        $signer = $users->where('id', '!=', $admin->id)->random() ?? $users->last();

        // Ambil klasifikasi
        $klasifikasiIds = KlasifikasiSurat::pluck('id')->toArray();

        $statuses = ['draft', 'pending', 'disetujui'];

        // Buat 5 dummy surat
        for ($i = 0; $i < 5; $i++) {
            $status = $faker->randomElement($statuses);
            $tanggal = Carbon::now()->subDays(rand(1, 30));
            
            $nomor = ($status !== 'draft') 
                ? 'ST-DUMMY-' . str_pad($i+1, 3, '0', STR_PAD_LEFT) . '/FIKOM/' . $tanggal->year
                : 'DRAFT-' . uniqid();

            try {
                $tugas = TugasHeader::create([
                    'nomor' => $nomor,
                    'jenis_tugas' => $faker->randomElement(['Penelitian', 'Pengabdian', 'Bimbingan']),
                    'tugas' => $faker->sentence(4),
                    'klasifikasi_surat_id' => $faker->randomElement($klasifikasiIds),
                    'dibuat_oleh' => $admin->id,
                    'asal_surat' => $admin->peran_id ?? 1, // Required NOT NULL
                    'penandatangan' => $signer->id,
                    'status_surat' => $status,
                    'tanggal_surat' => $tanggal,
                    'tanggal_asli' => $tanggal,
                    'waktu_mulai' => $tanggal->copy()->setTime(9, 0),
                    'waktu_selesai' => $tanggal->copy()->setTime(12, 0),
                    'tempat' => $faker->city,
                    'nama_umum' => $faker->company,
                    'bulan' => $tanggal->format('m'),
                    'tahun' => $tanggal->year,
                    'semester' => ($tanggal->month > 6) ? 'Ganjil' : 'Genap',
                    'status_penerima' => $faker->randomElement(['dosen', 'tendik', null]),
                    'detail_tugas_id' => 1, // Minimal valid ID
                    'signed_at' => ($status === 'disetujui') ? $tanggal->copy()->addDay() : null,
                ]);

                // Tambah penerima
                $receiverCount = rand(1, 2);
                $receivers = $users->random(min($receiverCount, $users->count()));
                
                foreach ($receivers as $receiver) {
                    TugasPenerima::create([
                        'tugas_id' => $tugas->id,
                        'pengguna_id' => $receiver->id,
                        'nama_penerima' => '', // Akan di-set oleh trigger
                        'dibaca' => $faker->boolean(30),
                    ]);
                }
                
                $this->command->info("✓ Created: {$tugas->nomor} ({$status})");
                
            } catch (\Exception $e) {
                $this->command->error("✗ Failed #{$i}: " . $e->getMessage());
            }
        }
        
        $this->command->info("\n✅ Seeding selesai!");
    }
}
