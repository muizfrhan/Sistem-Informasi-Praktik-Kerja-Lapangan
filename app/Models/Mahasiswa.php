<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id', 'nama', 'nim', 'jurusan_id', 'kelas_id', 'program_studi',
        'kelas', 'semester', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'email', 'no_hp', 'alamat', 'foto', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function kelasRelasi(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function laporanPkl(): HasMany
    {
        return $this->hasMany(LaporanPkl::class);
    }

    public function bimbingan(): HasMany
    {
        return $this->hasMany(Bimbingan::class);
    }

    public function pendaftaranPkl(): HasMany
    {
        return $this->hasMany(PendaftaranPkl::class);
    }

    /** Pendaftaran PKL terbaru (untuk ringkasan dashboard). */
    public function pendaftaranTerakhir(): HasOne
    {
        return $this->hasOne(PendaftaranPkl::class)->latestOfMany();
    }

    public function nilaiPkl(): HasMany
    {
        return $this->hasMany(NilaiPkl::class);
    }

    // ---------- Relasi modul baru ----------

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function jurnal(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class);
    }

    public function tugas(): BelongsToMany
    {
        return $this->belongsToMany(Tugas::class, 'tugas_target', 'mahasiswa_id', 'tugas_id')
            ->withTimestamps();
    }

    public function tugasSubmission(): HasMany
    {
        return $this->hasMany(TugasSubmission::class);
    }

    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class);
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class);
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class);
    }

    // ---------- Properti Turunan ----------

    /** Inisial untukavatar fallback. */
    public function getInisialAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->nama)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first . $last);
    }

    /** Nama untuk ditampilkan lengkap dengan NIM. */
    public function getLabelLengkapAttribute(): string
    {
        return trim($this->nama . ' (' . ($this->nim ?? 'tanpa NIM') . ')');
    }

    public function absensiHariIni(): ?Absensi
    {
        return $this->absensi()->whereDate('tanggal', today())->first();
    }

    public function izinAktif(): ?Izin
    {
        return $this->izin()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_selesai', '>=', today())
            ->first();
    }
}
