<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenilaianKategori extends Model
{
    protected $table = 'penilaian_kategori';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'bobot', 'urutan', 'aktif'];

    protected $casts = [
        'bobot' => 'decimal:2',
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class, 'kategori_id');
    }
}
