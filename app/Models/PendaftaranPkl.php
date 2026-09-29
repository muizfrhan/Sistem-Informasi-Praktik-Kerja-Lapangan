<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendaftaranPkl extends Model
{
    protected $table = 'pendaftaran_pkl';

    protected $fillable = [
        'mahasiswa_id', 'perusahaan_id', 'bidang_pkl', 'periode', 'periode_id',
        'dosen_pembimbing_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function periodeRelasi(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    /** Status yang dianggap "masih berjalan". */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['diterima', 'placed', 'active']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function sudahDiterima(): bool
    {
        return in_array($this->status, ['diterima', 'placed', 'active'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Verifikasi',
            'diterima' => 'Diterima',
            'ditolak' => 'Ditolak',
            'placed' => 'Ditempatkan',
            'active' => 'Sedang PKL',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
