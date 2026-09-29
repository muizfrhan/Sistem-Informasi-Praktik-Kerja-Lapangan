<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jurnal;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\Peringatan;
use App\Models\TugasSubmission;
use App\Models\User;

/**
 * Membuat notifikasi in-app dan peringatan otomatis.
 */
class NotifikasiService
{
    /** Kirim notifikasi ke seorang user. */
    public function kirim(User $user, string $tipe, string $judul, string $pesan, array $data = [], ?string $tautan = null): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $user->id,
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan,
            'data' => $data ?: null,
            'tautan' => $tautan,
        ]);
    }

    /** Kirim notifikasi ke banyak user sekaligus. */
    public function kirimBanyak(iterable $users, string $tipe, string $judul, string $pesan, array $data = [], ?string $tautan = null): int
    {
        $jumlah = 0;

        foreach ($users as $user) {
            $this->kirim($user, $tipe, $judul, $pesan, $data, $tautan);
            $jumlah++;
        }

        return $jumlah;
    }

    public function unreadCount(User $user): int
    {
        return Notifikasi::untuk($user)->belumDibaca()->count();
    }

    public function tandaiDibaca(Notifikasi $notifikasi): void
    {
        if (! $notifikasi->dibaca_at) {
            $notifikasi->update(['dibaca_at' => now()]);
        }
    }

    public function tandaiSemuaDibaca(User $user): int
    {
        return Notifikasi::untuk($user)->belumDibaca()->update(['dibaca_at' => now()]);
    }

    // ------------------------------------------------------------------
    // Peringatan otomatis
    // ------------------------------------------------------------------

    /**
     * Periksa seluruh mahasiswa pada satu periode dan buat peringatan
     * bila ada kondisi yang perlu perhatian.
     *
     * @return array{diperiksa:int, dibuat:int, rincian:array<string,int>}
     */
    public function periksaPeringatan(Mahasiswa $mhs, int $batasJurnalHari = 3, float $batasAbsensi = 80.0): array
    {
        $dibuat = 0;
        $rincian = [];

        // 1. Jurnal kosong selama N hari
        $jurnalTerakhir = Jurnal::where('mahasiswa_id', $mhs->id)
            ->where('status', '!=', 'draft')
            ->max('tanggal');

        $hariKosong = $jurnalTerakhir
            ? \Illuminate\Support\Carbon::parse($jurnalTerakhir)->diffInDays(today())
            : null;

        if ($hariKosong !== null && $hariKosong >= $batasJurnalHari) {
            $dibuat += $this->buatPeringatan(
                $mhs,
                'jurnal_kosong',
                'Jurnal belum diisi',
                "Tidak ada jurnal yang diisi selama {$hariKosong} hari terakhir.",
                'warning',
                route('mahasiswa.jurnal.index')
            ) ? 1 : 0;
        }

        // 2. Kehadiran rendah
        $totalAbsensi = Absensi::where('mahasiswa_id', $mhs->id)->count();
        if ($totalAbsensi >= 5) {
            $hadir = Absensi::where('mahasiswa_id', $mhs->id)
                ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'libur'])
                ->count();
            $persentase = ($hadir / $totalAbsensi) * 100;

            if ($persentase < $batasAbsensi) {
                $dibuat += $this->buatPeringatan(
                    $mhs,
                    'absensi_rendah',
                    'Kehadiran rendah',
                    "Persentase kehadiran {$persentase}%, di bawah batas minimal " . round($batasAbsensi) . '%.',
                    'warning',
                    route('mahasiswa.absensi.index')
                ) ? 1 : 0;
            }
        }

        // 3. Alpha 3 kali berturut-turut
        $alphaTerbaru = Absensi::where('mahasiswa_id', $mhs->id)
            ->where('status', 'alpha')
            ->orderByDesc('tanggal')
            ->limit(3)
            ->pluck('tanggal')
            ->toArray();

        if (count($alphaTerbaru) === 3) {
            $berurutan = true;
            for ($i = 0; $i < 2; $i++) {
                if (\Illuminate\Support\Carbon::parse($alphaTerbaru[$i])
                    ->diffInDays(\Illuminate\Support\Carbon::parse($alphaTerbaru[$i + 1])) !== 1) {
                    $berurutan = false;
                    break;
                }
            }

            if ($berurutan) {
                $dibuat += $this->buatPeringatan(
                    $mhs,
                    'alpha_beruntun',
                    'Alpha berturut-turut',
                    'Terdapat 3 hari alpha berturut-turut. Segera hubungi pembimbing.',
                    'critical',
                    route('mahasiswa.absensi.index')
                ) ? 1 : 0;
            }
        }

        // 4. Tugas lewat deadline tanpa pengumpulan
        $tugasTelat = TugasSubmission::where('mahasiswa_id', $mhs->id)
            ->where('status', 'pending')
            ->whereHas('tugas', fn($q) => $q->where('deadline', '<', now()))
            ->count();

        if ($tugasTelat > 0) {
            $dibuat += $this->buatPeringatan(
                $mhs,
                'tugas_telat',
                'Tugas belum dikumpulkan',
                "Ada {$tugasTelat} tugas yang melewati deadline.",
                'warning',
                route('mahasiswa.tugas.index')
            ) ? 1 : 0;
        }

        $rincian = [
            'jurnal_kosong' => $hariKosong,
            'tugas_telat' => $tugasTelat,
        ];

        return ['diperiksa' => 1, 'dibuat' => $dibuat, 'rincian' => $rincian];
    }

    /**
     * Buat peringatan (idempotent per hari) lalu teruskan ke pembimbing & user terkait.
     */
    private function buatPeringatan(
        Mahasiswa $mhs,
        string $kode,
        string $judul,
        string $pesan,
        string $level,
        string $tautan
    ): bool {
        $ada = Peringatan::where('mahasiswa_id', $mhs->id)
            ->where('kode', $kode)
            ->whereDate('created_at', today())
            ->exists();

        if ($ada) {
            return false;
        }

        // Untuk mahasiswa
        Peringatan::create([
            'user_id' => $mhs->user_id,
            'mahasiswa_id' => $mhs->id,
            'kode' => $kode,
            'judul' => $judul,
            'pesan' => $pesan,
            'level' => $level,
            'tautan' => $tautan,
        ]);

        $this->kirim(
            $mhs->user,
            'sistem',
            $judul,
            $pesan,
            ['mahasiswa_id' => $mhs->id, 'kode' => $kode],
            $tautan
        );

        // Untuk pembimbing yang terkait
        $dosenIds = \App\Models\Bimbingan::where('mahasiswa_id', $mhs->id)
            ->distinct()
            ->pluck('dosen_id');

        foreach (\App\Models\Dosen::whereIn('id', $dosenIds)->with('user')->get() as $dosen) {
            if ($dosen->user) {
                Peringatan::create([
                    'user_id' => $dosen->user_id,
                    'mahasiswa_id' => $mhs->id,
                    'kode' => $kode,
                    'judul' => $judul . ' — ' . $mhs->nama,
                    'pesan' => $pesan,
                    'level' => $level,
                    'tautan' => $tautan,
                ]);
            }
        }

        return true;
    }
}
