<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'tanggal', 'judul', 'deskripsi',
        'jam_mulai', 'jam_selesai', 'output', 'kendala', 'solusi', 'dokumentasi',
        'status', 'reviewed_by', 'reviewed_at', 'catatan_reviewer', 'revisi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'reviewed_at' => 'datetime',
        'dokumentasi' => 'array',
        'revisi' => 'integer',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(JurnalKomentar::class, 'jurnal_id');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'reviewed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draf',
            'submitted' => 'Menunggu Review',
            'reviewed' => 'Sudah Direview',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
            default => ucfirst($this->status),
        };
    }
}
