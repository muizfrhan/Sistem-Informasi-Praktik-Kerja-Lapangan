<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenilaianKomponen extends Model
{
    protected $table = 'penilaian_komponen';

    protected $fillable = ['kode', 'nama', 'bobot', 'aktif', 'sumber'];

    protected $casts = [
        'bobot' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'komponen', 'kode');
    }
}
