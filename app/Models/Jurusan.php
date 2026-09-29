<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    protected $fillable = ['kode', 'nama', 'jenjang', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'jurusan_id');
    }

    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class, 'jurusan_id');
    }
}
