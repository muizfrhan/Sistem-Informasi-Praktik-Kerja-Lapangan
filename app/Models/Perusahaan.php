<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perusahaan extends Model
{
    protected $table = 'perusahaan';

    protected $fillable = [
        'nama', 'logo', 'bidang', 'alamat', 'kota', 'provinsi', 'website',
        'email', 'telepon', 'no_hp', 'contact_person', 'kuota_siswa',
        'status_kerja_sama', 'deskripsi', 'lokasi_id',
    ];

    protected function casts(): array
    {
        return [
            'kuota_siswa' => 'integer',
        ];
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function pembimbing(): HasMany
    {
        return $this->hasMany(PembimbingPerusahaan::class, 'perusahaan_id');
    }

    public function laporanPkl(): HasMany
    {
        return $this->hasMany(LaporanPkl::class);
    }

    public function pendaftaranPkl(): HasMany
    {
        return $this->hasMany(PendaftaranPkl::class);
    }

    /** Jumlah mahasiswa yang sedang ditempatkan (pendaftaran disetujui). */
    public function jumlahSiswaAktif(): int
    {
        return $this->pendaftaranPkl()
            ->whereIn('status', ['diterima', 'placed', 'active'])
            ->count();
    }

    public function sisaKuota(): int
    {
        return max(0, (int) $this->kuota_siswa - $this->jumlahSiswaAktif());
    }

    public function exceeded(): bool
    {
        return $this->jumlahSiswaAktif() > (int) $this->kuota_siswa;
    }

    public function isAktif(): bool
    {
        return $this->status_kerja_sama !== 'nonaktif';
    }
}
