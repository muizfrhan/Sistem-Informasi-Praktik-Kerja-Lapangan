<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    protected $table = 'lokasi';

    protected $fillable = ['nama', 'alamat', 'latitude', 'longitude', 'radius_meter', 'aktif'];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_meter' => 'integer',
        'aktif' => 'boolean',
    ];

    public function perusahaan(): HasMany
    {
        return $this->hasMany(Perusahaan::class, 'lokasi_id');
    }
}
