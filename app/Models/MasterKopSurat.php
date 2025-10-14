<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKopSurat extends Model
{
    protected $table = 'master_kop_surat';
    
    // PENTING: Gunakan $fillable atau $guarded
    protected $guarded = ['id']; // Atau gunakan $fillable dengan list kolom
    
    protected $casts = [
        'tampilkan_logo_kanan' => 'boolean',
    ];
    
    public function getDefaultHeaderData()
    {
        return [
            'nama_fakultas' => $this->nama_fakultas ?? 'FAKULTAS ILMU KOMPUTER',
            'alamat_lengkap' => $this->alamat_lengkap ?? 'Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234',
            'telepon_lengkap' => $this->telepon_lengkap ?? 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265',
            'email_website' => $this->email_website ?? 'e-mail: unika@unika.ac.id http://www.unika.ac.id/',
        ];
    }
}
