<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = [
        'user_id', 'nama', 'nip', 'jurusan_id', 'program_studi',
        'email', 'no_hp', 'jabatan', 'alamat', 'foto', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function bimbingan(): HasMany
    {
        return $this->hasMany(Bimbingan::class);
    }

    public function nilaiPkl(): HasMany
    {
        return $this->hasMany(NilaiPkl::class);
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'dosen_id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'dosen_id');
    }

    public function pengujiSidang(): HasMany
    {
        return $this->hasMany(SidangPenguji::class, 'user_id', 'user_id');
    }

    // ---------- Helper ----------

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /** Mahasiswa yang berada di bawah bimbingan dosen ini. */
    public function mahasiswaBimbingan()
    {
        return Mahasiswa::whereIn(
            'id',
            Bimbingan::where('dosen_id', $this->id)->distinct()->pluck('mahasiswa_id')
        );
    }

    public function sudahMenilai(Mahasiswa $mahasiswa): bool
    {
        return $this->nilaiPkl()->where('mahasiswa_id', $mahasiswa->id)->exists();
    }
}
