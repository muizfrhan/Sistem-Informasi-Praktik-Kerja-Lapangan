<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sertifikat extends Model
{
    protected $table = 'sertifikat';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'nomor', 'kode_verifikasi', 'tanggal_terbit',
        'durasi_hari', 'nilai_akhir', 'predikat', 'file_pdf', 'status',
        'diterbitkan_oleh', 'diterbitkan_at',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'nilai_akhir' => 'decimal:2',
        'durasi_hari' => 'integer',
        'diterbitkan_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    public function verifikasiLogs(): HasMany
    {
        return $this->hasMany(VerifikasiSertifikat::class, 'sertifikat_id');
    }

    public function scopeTerbit($query)
    {
        return $query->where('status', 'terbit');
    }

    /** Nomor sertifikat berikutnya untuk sebuah periode. */
    public static function nomorBerikutnya(PeriodePkl $periode): string
    {
        $tahun = $periode->tanggal_mulai->year;
        $prefix = 'SIPKL/' . str_replace('/', '', $periode->kode) . '/';
        $count = static::where('periode_id', $periode->id)->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public static function kodeVerifikasiBaru(): string
    {
        return Str::upper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
    }
}
