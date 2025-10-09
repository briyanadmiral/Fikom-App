<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasDetail extends Model
{
    protected $table = 'tugas_detail';
    
    protected $fillable = [
        'sub_tugas_id',
        'nama',
        'deskripsi',
        'urutan'
    ];
    
    public function subTugas(): BelongsTo
    {
        return $this->belongsTo(SubTugas::class, 'sub_tugas_id');
    }
    
    public function tugasHeaders()
    {
        return $this->hasMany(TugasHeader::class, 'detail_tugas_id');
    }
}
