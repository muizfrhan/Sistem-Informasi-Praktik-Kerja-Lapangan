<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = [
        'periode_id', 'dosen_id', 'judul', 'deskripsi', 'deadline', 'attachment', 'wajib',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'attachment' => 'array',
        'wajib' => 'boolean',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(TugasTarget::class, 'tugas_id');
    }

    public function mahasiswa(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'tugas_target', 'tugas_id', 'mahasiswa_id')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TugasSubmission::class, 'tugas_id');
    }

    public function sudahLewat(): bool
    {
        return $this->deadline->isPast();
    }
}
