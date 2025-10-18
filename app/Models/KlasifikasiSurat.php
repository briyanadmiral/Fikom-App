<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KlasifikasiSurat extends Model
{
    protected $table = 'klasifikasi_surat';
    
    protected $fillable = ['kode', 'deskripsi'];

    /**
     * Relasi ke Tugas Header
     */
    public function tugasHeaders()
    {
        return $this->hasMany(TugasHeader::class, 'klasifikasi_surat_id');
    }

    /**
     * Get next available code for a given prefix and golongan
     * 
     * @param string $prefix (e.g., 'A', 'B', 'C')
     * @param int $golongan (e.g., 1, 2, 10)
     * @return string (e.g., 'A.1.5', 'B.10.3')
     */
    public static function getNextCode($prefix, $golongan)
    {
        // Cari sub-nomor terakhir untuk kombinasi prefix + golongan
        // Contoh: Cari kode dengan pattern "B.10.*"
        $lastCode = self::where('kode', 'LIKE', $prefix . '.' . $golongan . '.%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(kode, ".", -1) AS UNSIGNED) DESC')
            ->value('kode');

        if (!$lastCode) {
            // Jika belum ada kode dengan prefix + golongan ini, mulai dari X.Y.1
            return $prefix . '.' . $golongan . '.1';
        }

        // Parse sub-nomor terakhir (e.g., B.10.13 → 13)
        $parts = explode('.', $lastCode);
        $subNumber = (int) ($parts[2] ?? 1);

        // Increment sub-number
        $subNumber++;

        return $prefix . '.' . $golongan . '.' . $subNumber;
    }

    /**
     * Get all unique prefixes (e.g., ['A', 'B', 'C'])
     */
    public static function getAvailablePrefixes()
    {
        return self::select(DB::raw('SUBSTRING_INDEX(kode, ".", 1) as prefix'))
            ->distinct()
            ->orderBy('prefix')
            ->pluck('prefix')
            ->toArray();
    }

    /**
     * Get all unique golongan for a given prefix
     * 
     * @param string $prefix
     * @return array
     */
    public static function getAvailableGolongan($prefix)
    {
        return self::where('kode', 'LIKE', $prefix . '.%')
            ->select(DB::raw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(kode, ".", 2), ".", -1) AS UNSIGNED) as golongan'))
            ->distinct()
            ->orderBy('golongan')
            ->pluck('golongan')
            ->toArray();
    }

    /**
     * Scope: Filter by prefix
     */
    public function scopeByPrefix($query, $prefix)
    {
        return $query->where('kode', 'LIKE', $prefix . '.%');
    }

    /**
     * Scope: Filter by prefix and golongan
     */
    public function scopeByPrefixGolongan($query, $prefix, $golongan)
    {
        return $query->where('kode', 'LIKE', $prefix . '.' . $golongan . '.%');
    }
}
