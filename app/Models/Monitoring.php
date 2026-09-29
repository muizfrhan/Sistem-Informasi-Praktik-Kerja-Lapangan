<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Monitoring extends Model
{
    protected $table = 'monitoring';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'tanggal',
        'progres_pkl', 'progres_absensi', 'progres_jurnal',
        'progres_bimbingan', 'progres_laporan',
        'indikator', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'progres_pkl' => 'decimal:2',
        'progres_absensi' => 'decimal:2',
        'progres_jurnal' => 'decimal:2',
        'progres_bimbingan' => 'decimal:2',
        'progres_laporan' => 'decimal:2',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function getSkorKeseluruhanAttribute(): float
    {
        $nilai = [
            (float) $this->progres_pkl,
            (float) $this->progres_absensi,
            (float) $this->progres_jurnal,
            (float) $this->progres_bimbingan,
            (float) $this->progres_laporan,
        ];

        return round(array_sum($nilai) / max(1, count($nilai)), 2);
    }
}
