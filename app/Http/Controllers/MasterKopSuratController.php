<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Schema;

class MasterKopSuratController extends Controller
{
    public function index()
    {
        // Jika belum ada data sama sekali, buat satu record kosong
        $kop = MasterKopSurat::firstOrCreate([]);
        return view('pengaturan.kop_surat', compact('kop'));
    }

    public function update(Request $r)
    {
        // 1) Validasi yang sudah disesuaikan
        $data = $r->validate([
            'mode'       => ['required', 'in:image,composed'],
            
            // [MODIFIED] Hanya validasi field yang ada di mode 'composed' baru
            'judul_atas' => ['nullable','string','max:255'],
            'alamat'     => ['nullable','string','max:1000'], // max diperbesar untuk textarea

            // [REMOVED] Validasi untuk field lama yang sudah tidak ada di form
            // 'subjudul', 'telepon', 'fax', 'email', 'website' dihapus

            // Upload files (tetap sama)
            'logo_kanan' => ['sometimes','file','image','max:1024'],
            'header'     => ['sometimes','file','image','max:2048'],
            'cap'        => ['sometimes','file','image','max:1024'],
            
            // [REMOVED] Logo kiri sudah tidak dipakai di mode composed baru
            // 'logo_kiri' => ['sometimes','file','image','max:1024'],

            // [MODIFIED] Checkbox tampilkan logo disederhanakan
            'tampilkan_logo_kanan' => ['nullable'],
        ]);

        $kop = MasterKopSurat::firstOrCreate([]);

        // [NEW] Jika modenya composed, kosongkan field-field lama untuk kebersihan data
        if ($data['mode'] === 'composed') {
            $data['subjudul'] = null;
            $data['telepon'] = null;
            $data['fax'] = null;
            $data['email'] = null;
            $data['website'] = null;
            // Secara default, logo kanan selalu tampil di mode ini
            $data['tampilkan_logo_kanan'] = true;
            $data['tampilkan_logo_kiri'] = false;
        }

        // 2) Simpan file (logika tetap sama, hanya input 'logo_kiri' yang dihilangkan)
        $map = [
            'logo_kanan' => 'logo_kanan_path',
            'header'     => 'header_path',
            'cap'        => 'cap_path',
            // 'logo_kiri' tidak ada lagi di map
        ];
        foreach ($map as $input => $col) {
            if ($r->hasFile($input)) {
                $data[$col] = $r->file($input)->store('kop', 'public');
            }
        }

        // 3) Hapus key file upload agar tidak dikirim ke update()
        unset($data['logo_kanan'], $data['header'], $data['cap']);

        // 4) Audit user (tetap sama)
        if (Schema::hasColumn('master_kop_surat', 'updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        // 5) Simpan
        $kop->update($data);

        return back()->with('success', 'Pengaturan kop surat berhasil diperbarui.');
    }
}
