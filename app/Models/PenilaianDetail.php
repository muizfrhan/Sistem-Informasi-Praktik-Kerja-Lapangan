<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianDetail extends Model
{
    protected $table = 'penilaian_detail';

    protected $fillable = ['penilaian_id', 'kategori_id', 'nilai', 'komentar'];

    protected $casts = ['nilai' => 'decimal:2'];

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PenilaianKategori::class, 'kategori_id');
    }
}
