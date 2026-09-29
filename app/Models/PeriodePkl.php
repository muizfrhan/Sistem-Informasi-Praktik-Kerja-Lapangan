<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodePkl extends Model
{
    protected $table = 'periode_pkl';

    protected $fillable = [
        'kode', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'status',
        'batas_pendaftaran', 'batas_laporan', 'kuota_total', 'deskripsi', 'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'batas_pendaftaran' => 'date',
        'batas_laporan' => 'date',
    ];

    /** Periode yang sedang menerima pendaftaran. */
    public function scopeAktifPendaftaran($query)
    {
        return $query->where('status', 'pendaftaran');
    }

    /** Periode yang sedang berjalan (pelaksanaan). */
    public function scopeAktifPelaksanaan($query)
    {
        return $query->where('status', 'pelaksanaan');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['pendaftaran', 'penempatan', 'pelaksanaan', 'evaluasi']);
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(PendaftaranPkl::class, 'periode_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'periode_id');
    }

    public function jurnal(): HasMany
    {
        return $this->hasMany(Jurnal::class, 'periode_id');
    }

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'periode_id');
    }

    public function sedangBerjalan(): bool
    {
        return now()->betweenIncluded(
            $this->tanggal_mulai->startOfDay(),
            $this->tanggal_selesai->endOfDay()
        );
    }

    public static function aktif(): ?self
    {
        return static::aktif()->orderByDesc('tanggal_mulai')->first()
            ?? static::orderByDesc('tanggal_mulai')->first();
    }
}
