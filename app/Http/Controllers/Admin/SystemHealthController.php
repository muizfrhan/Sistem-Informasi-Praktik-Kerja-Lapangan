<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Pemeriksaan kesehatan sistem: database, storage, aplikasi, antrean, email.
 * Tidak melakukan perubahan apa pun (read-only).
 */
class SystemHealthController extends BaseModuleController
{
    public function index(): View
    {
        $checks = [
            $this->cekDatabase(),
            $this->cekStorage(),
            $this->cekAplikasi(),
            $this->cekAntrean(),
            $this->cekEmail(),
            $this->cekKurrenciesi(),
            $this->cekJadwal(),
        ];

        $ringkasan = [
            'total' => count($checks),
            'healthy' => collect($checks)->where('status', 'healthy')->count(),
            'warning' => collect($checks)->where('status', 'warning')->count(),
            'error' => collect($checks)->where('status', 'error')->count(),
        ];

        return view('admin.health.index', compact('checks', 'ringkasan'));
    }

    private function cekDatabase(): array
    {
        try {
            $mulai = microtime(true);
            DB::select('select 1');
            $ms = round((microtime(true) - $mulai) * 1000, 2);

            $ukuran = 0;
            try {
                $db = config('database.connections.mysql.database');
                $row = DB::selectOne(
                    "select round(sum(data_length+index_length)/1024/1024, 2) as size
                     from information_schema.tables where table_schema = ?",
                    [$db]
                );
                $ukuran = (float) ($row->size ?? 0);
            } catch (\Throwable) {
                // abaikan, hanya bonus info
            }

            return $this->hasil('Database', 'MySQL ' . config('database.connections.mysql.database'), $ms . ' ms', 'healthy', [
                'Ukuran database' => $ukuran > 0 ? $ukuran . ' MB' : 'tidak diketahui',
                'Koneksi' => 'berhasil',
            ]);
        } catch (\Throwable $e) {
            return $this->hasil('Database', 'Koneksi gagal', $e->getMessage(), 'error');
        }
    }

    private function cekStorage(): array
    {
        $root = storage_path('app/public');
        $writable = is_writable($root);

        $total = 0;
        $jumlah = 0;

        try {
            $files = Storage::disk('public')->allFiles();
            $jumlah = count($files);
            foreach ($files as $f) {
                $total += Storage::disk('public')->size($f);
            }
        } catch (\Throwable) {
            // abaikan
        }

        return $this->hasil(
            'Storage',
            $writable ? 'Dapat ditulis' : 'Tidak dapat ditulis',
            round($total / 1024 / 1024, 2) . ' MB dalam ' . $jumlah . ' berkas',
            $writable ? 'healthy' : 'error'
        );
    }

    private function cekAplikasi(): array
    {
        return $this->hasil(
            'Aplikasi',
            'Laravel ' . app()->version(),
            'PHP ' . PHP_VERSION,
            app()->isProduction() ? 'healthy' : 'warning',
            [
                'APP_ENV' => app()->environment(),
                'APP_DEBUG' => config('app.debug') ? 'aktif (jangan aktif di production)' : 'nonaktif',
                'APP_URL' => config('app.url'),
                'Timezone' => config('app.timezone'),
            ]
        );
    }

    private function cekAntrean(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            return $this->hasil(
                'Antrean Kerja',
                $pending . ' pekerjaan menunggu',
                $failed . ' pekerjaan gagal',
                $failed > 0 ? 'error' : ($pending > 100 ? 'warning' : 'healthy')
            );
        } catch (\Throwable $e) {
            return $this->hasil('Antrean Kerja', 'tidak tersedia', $e->getMessage(), 'warning');
        }
    }

    private function cekEmail(): array
    {
        $mailer = config('mail.default');

        if ($mailer === 'log') {
            return $this->hasil(
                'Email',
                'Mailer: log',
                'Email tidak benar-benar dikirim (ditulis ke storage/logs)',
                'warning',
                ['Catatan' => 'Untuk production, set MAIL_MAILER=smtp dan isi kredensial SMTP.']
            );
        }

        return $this->hasil('Email', 'Mailer: ' . $mailer, 'konfigurasi aktif', 'healthy');
    }

    private function cekKurrenciesi(): array
    {
        try {
            $nilai = (float) \App\Models\PenilaianKategori::where('aktif', true)->sum('bobot');

            return $this->hasil(
                'Konfigurasi Penilaian',
                'Total bobot kategori: ' . $nilai . '%',
                $nilai == 100 ? 'Sudah sesuai' : 'Total bobot sebaiknya 100%',
                $nilai == 100 ? 'healthy' : 'warning'
            );
        } catch (\Throwable $e) {
            return $this->hasil('Konfigurasi Penilaian', 'tidak tersedia', $e->getMessage(), 'warning');
        }
    }

    private function cekJadwal(): array
    {
        $jadwal = app(\Illuminate\Console\Scheduling\Schedule::class)->events();
        $adaMonitoring = collect($jadwal)->contains(fn($e) => str_contains($e->command ?? '', 'sipkl'));
        $schedulerAktif = \Illuminate\Support\Facades\Artisan::call('schedule:list') === 0;

        return $this->hasil(
            'Penjadwalan Otomatis',
            $jadwal->count() . ' task terdaftar',
            $adaMonitoring ? 'Monitoring terjadwal' : 'Belum ada task monitoring terjadwal',
            $adaMonitoring ? 'healthy' : 'warning',
            [
                'Perintah cek' => 'php artisan schedule:work (Windows: jalankan manual)',
                'Task' => $jadwal->pluck('command')->filter()->take(5)->implode(', ') ?: '-',
            ]
        );
    }

    private function hasil(string $judul, string $nilai, string $catatan, string $status, array $detail = []): array
    {
        return [
            'judul' => $judul,
            'nilai' => $nilai,
            'catatan' => $catatan,
            'status' => $status,
            'detail' => $detail,
        ];
    }
}
