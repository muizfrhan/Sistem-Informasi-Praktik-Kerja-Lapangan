<?php

namespace App\Http\Controllers;

use App\Models\PeriodePkl;
use App\Services\DataTableService;
use Illuminate\Http\Request;

abstract class BaseModuleController extends Controller
{
    protected DataTableService $table;

    public function __construct()
    {
        $this->table = new DataTableService();
    }

    /** Periode aktif saat ini (dapat dipilih lewat ?periode=). */
    protected function periode(Request $request): PeriodePkl
    {
        if ($request->filled('periode')) {
            $periode = PeriodePkl::find($request->integer('periode'));
            if ($periode) {
                return $periode;
            }
        }

        return PeriodePkl::aktif() ?? PeriodePkl::orderByDesc('tanggal_mulai')->firstOrFail();
    }

    /** Parameter tabel standar dari request. */
    protected function params(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'sort' => $request->string('sort')->toString(),
            'direction' => $request->string('direction')->toString() ?: 'asc',
            'perPage' => $request->integer('perPage') ?: 15,
            'filters' => $request->only(['status', 'indikator', 'jenis', 'tahun', 'bulan']),
        ];
    }

    /** Validasi file upload generik (delegasi ke FileStorageService). */
    protected function aturanUpload(string $field = 'file'): array
    {
        return [
            $field => ['required', 'file', 'max:' . (config('sipkl.upload_maks_mb', 10) * 1024)],
        ];
    }
}
