<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangPeserta extends Model
{
    protected $table = 'sidang_peserta';

    protected $fillable = ['sidang_id', 'mahasiswa_id', 'status'];

    public function sidang(): BelongsTo
    {
        return $this->belongsTo(Sidang::class, 'sidang_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
