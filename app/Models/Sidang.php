<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sidang extends Model
{
    protected $table = 'sidang';

    protected $fillable = [
        'periode_id', 'judul', 'tanggal', 'waktu_mulai', 'waktu_selesai',
        'ruangan', 'status', 'catatan',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(SidangPeserta::class, 'sidang_id');
    }

    public function penguji(): HasMany
    {
        return $this->hasMany(SidangPenguji::class, 'sidang_id');
    }
}
