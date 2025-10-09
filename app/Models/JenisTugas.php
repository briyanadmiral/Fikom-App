<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTugas extends Model
{
    protected $table = 'jenis_tugas';
    
    protected $fillable = ['nama', 'kode', 'deskripsi', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function subTugas()
    {
        return $this->hasMany(SubTugas::class, 'jenis_tugas_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
