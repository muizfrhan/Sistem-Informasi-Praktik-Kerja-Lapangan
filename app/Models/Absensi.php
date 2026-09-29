<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'tanggal', 'jam_masuk', 'jam_pulang',
        'status', 'durasi_menit', 'catatan', 'latitude', 'longitude',
        'dalam_radius', 'izin_id', 'dikonfirmasi_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'durasi_menit' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'dalam_radius' => 'boolean',
    ];

    public const STATUS = ['hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'libur'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function izin(): BelongsTo
    {
        return $this->belongsTo(Izin::class, 'izin_id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }

    /** Hitung durasi (menit) dari jam masuk & jam pulang. */
    public function hitungDurasi(): ?int
    {
        if (! $this->jam_masuk || ! $this->jam_pulang) {
            return null;
        }

        $start = strtotime($this->jam_masuk);
        $end = strtotime($this->jam_pulang);

        if ($end <= $start) {
            $end += 86400; //日凌晨 shift malam
        }

        return (int) round(($end - $start) / 60);
    }
}
