<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditLogController extends BaseModuleController
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->toString();
                $q->where(fn($x) => $x->where('nama', 'like', "%{$s}%")
                    ->orWhere('keterangan', 'like', "%{$s}%")
                    ->orWhere('action', 'like', "%{$s}%"));
            })
            ->when($request->filled('module'), fn($q) => $q->where('module', $request->string('module')))
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('dari'), fn($q) => $q->whereDate('created_at', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate('created_at', '<=', $request->date('sampai')))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $modul = AuditLog::distinct()->orderBy('module')->pluck('module');
        $aksi = AuditLog::distinct()->orderBy('action')->pluck('action');

        return view('admin.audit.index', compact('logs', 'modul', 'aksi'));
    }

    /** Ekspor CSV. */
    public function export(Request $request)
    {
        $query = AuditLog::query()
            ->when($request->filled('module'), fn($q) => $q->where('module', $request->string('module')))
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('dari'), fn($q) => $q->whereDate('created_at', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate('created_at', '<=', $request->date('sampai')))
            ->orderByDesc('id');

        $nama = 'audit-log-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'User', 'Role', 'Aksi', 'Modul', 'Keterangan', 'IP']);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->created_at->format('Y-m-d H:i:s'),
                        $r->nama,
                        $r->role,
                        $r->action,
                        $r->module,
                        $r->keterangan,
                        $r->ip,
                    ]);
                }
            });

            fclose($handle);
        }, $nama, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
