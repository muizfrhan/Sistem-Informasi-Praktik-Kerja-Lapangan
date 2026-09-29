<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Menyimpan berkas upload dengan validasi berlapis:
 *   1. Ekstensi di whitelist
 *   2. MIME asli (bukan dari header browser)
 *   3. Ukuran maksimum
 *   4. Nama file diacak (mencegah path traversal / upload berbahaya)
 */
class FileStorageService
{
    /** MIME yang benar-benar diterima per ekstensi. */
    private const MIME_WHITELIST = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/vnd.ms-office', 'application/x-msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ],
        'xls' => ['application/vnd.ms-excel', 'application/x-ms-excel', 'application/vnd.ms-office'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /**
     * Simpan satu berkas.
     *
     * @param  string  $folder  mis. "jurnal", "laporan", "tugas"
     * @param  string  $tipe    untuk tabel dokumen
     * @return array{path:string, mime:string, ukuran:int, url:string, dokumen:Dokumen}
     */
    public function simpan(
        UploadedFile $file,
        string $folder,
        string $nama = 'berkas',
        string $tipe = 'lainnya',
        ?int $userId = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): array {
        $this->validasi($file);

        $ext = strtolower($file->getClientOriginalExtension());
        $disk = 'public';
        $dir = trim($folder, '/') . '/' . now()->format('Y/m');

        // Nama file diacak: aman dari path traversal & tabrakan nama
        $namaAman = Str::slug(pathinfo($nama, PATHINFO_FILENAME)) ?: 'berkas';
        $namaAman = Str::limit($namaAman, 60, '');
        $filename = $namaAman . '-' . Str::random(12) . '.' . $ext;

        $path = $file->storeAs($dir, $filename, $disk);

        $dokumen = Dokumen::create([
            'user_id' => $userId ?? auth()->id(),
            'nama' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime' => $file->getMimeType(),
            'ukuran' => $file->getSize(),
            'tipe' => $tipe,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);

        return [
            'path' => $path,
            'mime' => $file->getMimeType(),
            'ukuran' => $file->getSize(),
            'url' => Storage::disk($disk)->url($path),
            'dokumen' => $dokumen,
        ];
    }

    /**
     * Simpan banyak berkas sekaligus (mis. dokumentasi jurnal).
     *
     * @return array<int, string> daftar path
     */
    public function simpanBanyak(array $files, string $folder, string $tipe = 'lainnya', ?int $userId = null): array
    {
        $paths = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $paths[] = $this->simpan($file, $folder, $file->getClientOriginalName(), $tipe, $userId)['path'];
        }

        return $paths;
    }

    /** Hapus berkas + record dokumen (jika ada). */
    public function hapus(?string $path, string $disk = 'public'): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        Dokumen::where('path', $path)->delete();
    }

    /** URL publik dari sebuah path. */
    public function url(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    // ------------------------------------------------------------------
    // Validasi
    // ------------------------------------------------------------------

    private function validasi(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (! array_key_exists($ext, self::MIME_WHITELIST)) {
            throw ValidationException::withMessages([
                'file' => "Format file .{$ext} tidak diizinkan. Gunakan: " .
                    implode(', ', array_keys(self::MIME_WHITELIST)) . '.',
            ]);
        }

        // Cek MIME asli hasil pemeriksaan isi file, bukan header browser
        $mimeAsli = $file->getMimeType();
        $diizinkan = self::MIME_WHITELIST[$ext];

        if (! in_array($mimeAsli, $diizinkan, true)) {
            throw ValidationException::withMessages([
                'file' => "Isi file tidak sesuai dengan ekstensi .{$ext} (terbaca: {$mimeAsli}).",
            ]);
        }

        $maksKb = (int) Setting::get('upload_maks_mb', 10) * 1024;
        if ($file->getSize() > $maksKb) {
            throw ValidationException::withMessages([
                'file' => 'Ukuran file melebihi batas ' . Setting::get('upload_maks_mb', 10) . 'MB.',
            ]);
        }
    }
}
