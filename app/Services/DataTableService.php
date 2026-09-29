<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\PeriodePkl;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use Illuminate\Support\Collection;

/**
 * Data tabel generik: search, filter, sort, pagination.
 * Dipakai semua halaman index agar konsisten (lihat modul.table_ux).
 */
class DataTableService
{
    /** Kolom yang boleh disortir (whitelist mencegah SQL injection lewat nama kolom). */
    private const SORTABLE = [
        'nama', 'nim', 'nip', 'email', 'status', 'created_at', 'updated_at',
        'tanggal', 'nilai', 'total', 'nomor', 'deadline', 'judul', 'kode',
        'nilai_akhir', 'tanggal_terbit', 'indikator', 'jenis', 'program_studi',
        'kelas', 'semester', 'no_hp', 'nama_perusahaan', 'tanggal_mulai',
    ];

    /**
     * Terapkan search + filter + sort + paginate pada sebuah query.
     *
     * @param  array{search?:string, searchColumns?:array, perPage?:int, sort?:string, direction?:string, filters?:array}  $params
     */
    public function apply($query, array $params, array $searchColumns = [])
    {
        // Search
        $search = trim((string) ($params['search'] ?? ''));
        if ($search !== '' && $searchColumns) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $col) {
                    $q->orWhere($col, 'like', '%' . $search . '%');
                }
            });
        }

        // Filter sederhana: kolom => nilai (skip jika kosong)
        foreach ($params['filters'] ?? [] as $column => $value) {
            if ($value === null || $value === '' || ! in_array($column, self::SORTABLE, true)) {
                continue;
            }

            if (is_array($value)) {
                $value = array_filter($value);
                if ($value) {
                    $query->whereIn($column, $value);
                }
            } else {
                $query->where($column, $value);
            }
        }

        // Sort
        $sort = $params['sort'] ?? null;
        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $direction = strtolower($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sort, $direction);
        }

        // Pagination
        $perPage = (int) ($params['perPage'] ?? 15);
        $perPage = max(5, min(100, $perPage));

        return $query->paginate($perPage)->withQueryString();
    }

    /** Deteksi apakah pengguna meminta urutan berikutnya (untuk header tabel). */
    public function nextDirection(string $currentSort, string $currentDirection, string $column): string
    {
        if ($currentSort !== $column) {
            return 'asc';
        }

        return $currentDirection === 'asc' ? 'desc' : 'asc';
    }

    /** Ambil URL dengan parameter baru (untuk link sort & filter). */
    public function url(array $overrides = []): string
    {
        $params = array_merge(request()->query(), $overrides);

        foreach ($params as $k => $v) {
            if ($v === null || $v === '') {
                unset($params[$k]);
            }
        }

        return request()->fullUrlWithQuery($params);
    }

    /** Ringkasan angka untuk kartu statistik. */
    public function ringkas(PeriodePkl $periode): array
    {
        return [
            'total_mahasiswa' => Mahasiswa::count(),
            'mahasiswa_aktif' => \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
                ->whereIn('status', ['diterima', 'placed', 'active'])
                ->count(),
            'total_perusahaan' => \App\Models\Perusahaan::where('status_kerja_sama', '!=', 'nonaktif')->count(),
            'total_tugas' => Tugas::count(),
            'total_submission' => TugasSubmission::count(),
        ];
    }

    /** Ekspor collection ke array untuk CSV/Excel. */
    public function untukEkspor(Collection $rows, array $columns): array
    {
        return $rows->map(fn($row) => collect($columns)->mapWithKeys(function ($value) use ($row) {
            if (is_callable($value)) {
                return [$value($row) => null];
            }
            return [$value => $row->{$value}];
        })->all())->all();
    }
}
