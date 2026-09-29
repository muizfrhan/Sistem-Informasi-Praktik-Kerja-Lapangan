<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends BaseModuleController
{
    private const GROUP_LABEL = [
        'umum' => 'Identitas Aplikasi',
        'branding' => 'Branding',
        'kontak' => 'Kontak',
        'pkl' => 'Konfigurasi PKL',
        'penilaian' => 'Konfigurasi Penilaian',
        'upload' => 'Upload Berkas',
        'peringatan' => 'Ambang Batas Peringatan',
    ];

    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('id')->get()
            ->groupBy('group');

        return view('admin.settings.index', [
            'settings' => $settings,
            'groupLabel' => self::GROUP_LABEL,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $row = Setting::where('key', $key)->first();
            if (! $row) {
                continue;
            }
            $row->update(['value' => $value]);
        }

        \App\Models\AuditLog::catat('settings', 'update', count($validated['settings']) . ' pengaturan diperbarui');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();

        \App\Models\AuditLog::catat('settings', 'delete', 'Pengaturan ' . $setting->key . ' dihapus');

        return back()->with('success', 'Pengaturan berhasil dihapus.');
    }
}
