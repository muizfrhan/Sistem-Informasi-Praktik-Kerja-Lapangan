<?php

// Import berbagai class dan controller yang dibutuhkan
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImportMahasiswaController;
use App\Http\Controllers\Admin\ImportDosenController;
use App\Http\Controllers\Admin\AdminMahasiswaController;
use App\Http\Controllers\Admin\AdminDosenController;
use App\Http\Controllers\Admin\PerusahaanController;
use App\Http\Controllers\Admin\LaporanController as DosenLaporanController;
use App\Http\Controllers\Admin\FormatLaporanController;
use App\Http\Controllers\Admin\PendaftaranPklController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\SertifikatController;
use App\Http\Controllers\Admin\SidangController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PenilaianKategoriController;
use App\Http\Controllers\Executive\ExecutiveDashboardController;

// Dosen
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\BimbinganPklController;
use App\Http\Controllers\Dosen\MahasiswaBimbinganController;
use App\Http\Controllers\Dosen\NilaiController;
use App\Http\Controllers\Dosen\SupervisionController;
use App\Http\Controllers\Dosen\TugasController as DosenTugasController;
use App\Http\Controllers\Dosen\PenilaianController;

// Mahasiswa
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\LaporanController as MahasiswaLaporanController;
use App\Http\Controllers\Mahasiswa\PendaftaranController;
use App\Http\Controllers\Mahasiswa\BimbinganController;
use App\Http\Controllers\Mahasiswa\AbsensiController;
use App\Http\Controllers\Mahasiswa\JurnalController;
use App\Http\Controllers\Mahasiswa\IzinController;
use App\Http\Controllers\Mahasiswa\TugasController;
use App\Http\Controllers\Mahasiswa\SertifikatController as MahasiswaSertifikatController;
use App\Http\Controllers\Chat\PercakapanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Public\SertifikatVerifikasiController;

// =======================
// Halaman awal
// =======================
// Landing page publik SIPKL (tanpa middleware, tetap terbuka untuk tamu)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Redirect setelah login berdasarkan role user
Route::get('/redirect', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'redirectKeDasbor'])
    ->middleware('auth')
    ->name('redirect');;

// Breeze Auth (route bawaan autentikasi Laravel Breeze)
require __DIR__ . '/auth.php';

// =======================
// Profil
// =======================
// Route untuk edit, update, dan hapus profil user (hanya jika sudah login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =======================
// Admin
// =======================
// Semua route admin hanya bisa diakses oleh user dengan role admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // CRUD Mahasiswa (tambah manual & import)
    Route::get('/mahasiswa', [AdminMahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/create', [AdminMahasiswaController::class, 'create'])->name('mahasiswa.create');
    Route::post('/mahasiswa', [AdminMahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::get('/mahasiswa/import', [ImportMahasiswaController::class, 'create'])->name('mahasiswa.import.form');
    Route::post('/mahasiswa/import', [ImportMahasiswaController::class, 'store'])->name('mahasiswa.import');
    Route::delete('/mahasiswa/{id}', [AdminMahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    Route::get('/mahasiswa/{mahasiswa}/edit', [AdminMahasiswaController::class, 'edit'])->name('mahasiswa.edit');
    Route::put('/mahasiswa/{mahasiswa}', [AdminMahasiswaController::class, 'update'])->name('mahasiswa.update');

    // CRUD Dosen (tambah manual & import)
    Route::get('/dosen', [AdminDosenController::class, 'index'])->name('dosen.index');
    Route::get('/dosen/create', [AdminDosenController::class, 'create'])->name('dosen.create');
    Route::post('/dosen', [AdminDosenController::class, 'store'])->name('dosen.store');
    Route::get('/dosen/import', [ImportDosenController::class, 'create'])->name('dosen.import.form');
    Route::post('/dosen/import', [ImportDosenController::class, 'store'])->name('dosen.import');
    Route::delete('/dosen/{id}', [AdminDosenController::class, 'destroy'])->name('dosen.destroy');
    Route::get('/dosen/{dosen}/edit', [AdminDosenController::class, 'edit'])->name('dosen.edit');
    Route::put('/dosen/{dosen}', [AdminDosenController::class, 'update'])->name('dosen.update');

    // CRUD Perusahaan (tanpa show)
    Route::resource('perusahaan', PerusahaanController::class)->except('show');

    // Verifikasi Pendaftaran PKL
    Route::get('/pendaftaran', [PendaftaranPklController::class, 'index'])->name('pendaftaran.index');
    Route::post('/pendaftaran/{id}/verifikasi', [PendaftaranPklController::class, 'verifikasi'])->name('pendaftaran.verifikasi');

    // Verifikasi Laporan PKL
    Route::get('/laporan/verifikasi', [DosenLaporanController::class, 'index'])->name('laporan.verifikasi');
    Route::post('/laporan/verifikasi/{id}', [DosenLaporanController::class, 'verifikasi'])->name('laporan.verifikasi.process');

    // Upload & hapus format laporan
    Route::get('/format-laporan', [FormatLaporanController::class, 'index'])->name('format.index');
    Route::post('/format-laporan', [FormatLaporanController::class, 'upload'])->name('format.upload');
    Route::delete('/format-laporan/{id}', [FormatLaporanController::class, 'destroy'])->name('format.destroy');

    // Manajemen akun: setujui pendaftaran mandiri & nonaktifkan
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::patch('/pengguna/{user}/status', [PenggunaController::class, 'updateStatus'])->name('pengguna.status');
});

// =======================
// Dosen
// =======================
// Semua route dosen hanya bisa diakses oleh user dengan role dosen
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    // Dashboard dosen
    Route::get('/home', [DosenDashboardController::class, 'index'])->name('dashboard');
    // Daftar mahasiswa bimbingan
    Route::get('/mahasiswa-bimbingan', [MahasiswaBimbinganController::class, 'index'])->name('mahasiswa.bimbingan');
    // Jadwal bimbingan PKL
    Route::get('/jadwal-bimbingan', [BimbinganPklController::class, 'index'])->name('bimbingan');
    Route::post('/jadwal-bimbingan/{id}/verifikasi', [BimbinganPklController::class, 'verifikasi'])->name('bimbingan.verifikasi');
    // Input nilai PKL
    Route::get('/input-nilai', [NilaiController::class, 'index'])->name('nilai');
    Route::post('/input-nilai', [NilaiController::class, 'store'])->name('nilai.store');

    // ---------- Modul supervision (absensi, jurnal, izin) ----------
    Route::get('/monitoring', [SupervisionController::class, 'dashboard'])->name('monitoring');
    Route::get('/monitoring/{mahasiswa}', [SupervisionController::class, 'showMahasiswa'])->name('monitoring.show');

    Route::get('/absensi', [SupervisionController::class, 'absensi'])->name('absensi.index');
    Route::post('/absensi/{absensi}/konfirmasi', [SupervisionController::class, 'konfirmasiAbsensi'])->name('absensi.konfirmasi');

    Route::get('/jurnal', [SupervisionController::class, 'jurnal'])->name('jurnal.index');
    Route::get('/jurnal/{jurnal}', [SupervisionController::class, 'showJurnal'])->name('jurnal.show');
    Route::post('/jurnal/{jurnal}/review', [SupervisionController::class, 'reviewJurnal'])->name('jurnal.review');
    Route::post('/jurnal/{jurnal}/komentar', [SupervisionController::class, 'komentarJurnal'])->name('jurnal.komentar');

    Route::get('/izin', [SupervisionController::class, 'izin'])->name('izin.index');
    Route::post('/izin/{izin}/proses', [SupervisionController::class, 'prosesIzin'])->name('izin.proses');

    // ---------- Tugas PKL ----------
    Route::get('/tugas', [DosenTugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/create', [DosenTugasController::class, 'create'])->name('tugas.create');
    Route::post('/tugas', [DosenTugasController::class, 'store'])->name('tugas.store');
    Route::get('/tugas/{tugas}', [DosenTugasController::class, 'show'])->name('tugas.show');
    Route::put('/tugas/{tugas}', [DosenTugasController::class, 'update'])->name('tugas.update');
    Route::delete('/tugas/{tugas}', [DosenTugasController::class, 'destroy'])->name('tugas.destroy');
    Route::post('/tugas/{tugas}/review', [DosenTugasController::class, 'review'])->name('tugas.review');
    Route::post('/submission/{submission}/review', [DosenTugasController::class, 'review'])->name('submission.review');

    // ---------- Penilaian configurable ----------
    Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('/penilaian/{mahasiswa}', [PenilaianController::class, 'create'])->name('penilaian.create');
    Route::post('/penilaian/{mahasiswa}', [PenilaianController::class, 'store'])->name('penilaian.store');
    Route::post('/penilaian/{penilaian}/final', [PenilaianController::class, 'finalisasi'])->name('penilaian.final');
});

// =======================
// Mahasiswa
// =======================
// Semua route mahasiswa hanya bisa diakses oleh user dengan role mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    // Dashboard mahasiswa
    Route::get('/home', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    // Pendaftaran PKL
    Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy'])->name('pendaftaran.destroy');
    // Lihat daftar perusahaan
    Route::get('/perusahaan', [MahasiswaDashboardController::class, 'perusahaan'])->name('perusahaan');
    // Laporan PKL
    Route::get('/laporan', [MahasiswaLaporanController::class, 'index'])->name('laporan');
    Route::post('/laporan/upload', [MahasiswaLaporanController::class, 'upload'])->name('laporan.upload');
    // Bimbingan PKL
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan');
    Route::post('/bimbingan', [BimbinganController::class, 'store'])->name('bimbingan.store');
    // Download format laporan
    Route::get('/format-laporan', function () {
        $format = \App\Models\FormatLaporan::latest()->get();
        return view('mahasiswa.format.index', compact('format'));
    })->name('format');

    // ---------- Absensi ----------
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn'])->name('absensi.checkin');
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');
    Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');

    // ---------- Jurnal harian ----------
    Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/create', [JurnalController::class, 'create'])->name('jurnal.create');
    Route::post('/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
    Route::get('/jurnal/{jurnal}', [JurnalController::class, 'show'])->name('jurnal.show');
    Route::get('/jurnal/{jurnal}/edit', [JurnalController::class, 'edit'])->name('jurnal.edit');
    Route::put('/jurnal/{jurnal}', [JurnalController::class, 'update'])->name('jurnal.update');
    Route::delete('/jurnal/{jurnal}', [JurnalController::class, 'destroy'])->name('jurnal.destroy');
    Route::post('/jurnal/{jurnal}/komentar', [JurnalController::class, 'komentar'])->name('jurnal.komentar');

    // ---------- Izin / sakit ----------
    Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
    Route::get('/izin/create', [IzinController::class, 'create'])->name('izin.create');
    Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
    Route::delete('/izin/{izin}', [IzinController::class, 'destroy'])->name('izin.destroy');

    // ---------- Tugas PKL ----------
    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/{tugas}', [TugasController::class, 'show'])->name('tugas.show');
    Route::post('/tugas/{tugas}/submit', [TugasController::class, 'submit'])->name('tugas.submit');

    // ---------- Sertifikat ----------
    Route::get('/sertifikat', [MahasiswaSertifikatController::class, 'index'])->name('sertifikat.index');
    Route::get('/sertifikat/{sertifikat}', [MahasiswaSertifikatController::class, 'show'])->name('sertifikat.show');
    Route::get('/sertifikat/{sertifikat}/download', [MahasiswaSertifikatController::class, 'download'])->name('sertifikat.download');

    // ---------- Notifikasi & pengumuman ----------
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notifikasi}/dibaca', [NotifikasiController::class, 'tandaiDibaca'])->name('notifikasi.dibaca');
    Route::post('/notifikasi/dibaca-semua', [NotifikasiController::class, 'tandaiSemuaDibaca'])->name('notifikasi.dibaca-semua');
});

// =======================
//|Notifikasi (semua role)
// =======================
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/badge', [NotifikasiController::class, 'badge'])->name('notifikasi.badge');
    Route::post('/notifikasi/{notifikasi}/dibaca', [NotifikasiController::class, 'tandaiDibaca'])->name('notifikasi.dibaca');
    Route::post('/notifikasi/dibaca-semua', [NotifikasiController::class, 'tandaiSemuaDibaca'])->name('notifikasi.dibaca-semua');
});

// =======================
// Verifikasi Sertifikat (publik, tanpa login)
// =======================
Route::get('/certificate/verify/{kode?}', [SertifikatVerifikasiController::class, 'show'])
    ->name('certificate.verify');
Route::get('/certificate/verify/{kode}/cari', [SertifikatVerifikasiController::class, 'cari'])
    ->name('certificate.cari');

// =======================
// Pimpinan & Koordinator
// =======================
Route::middleware(['auth', 'role:pimpinan'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
    Route::get('/dashboard', [ExecutiveDashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [ExecutiveDashboardController::class, 'laporan'])->name('laporan');
    Route::get('/export/{jenis}', [ExecutiveDashboardController::class, 'export'])->name('export');
});

Route::middleware(['auth', 'role:koordinator'])->prefix('koordinator')->name('koordinator.')->group(function () {
    Route::get('/dashboard', [MonitoringController::class, 'koordinator'])->name('dashboard');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
    Route::get('/monitoring/mahasiswa/{mahasiswa}', [MonitoringController::class, 'show'])->name('per-monitoring');
    Route::post('/monitoring/segarkan', [MonitoringController::class, 'segarkan'])->name('monitoring.segarkan');
    Route::post('/pendaftaran/{pendaftaran}/verifikasi', [PendaftaranPklController::class, 'verifikasi'])->name('pendaftaran.verifikasi');
});

// =======================
// Admin — modul baru
// =======================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Periode PKL
    Route::resource('periode', PeriodeController::class)->except('show');

    // Monitoring
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
    Route::get('/monitoring/{mahasiswa}', [MonitoringController::class, 'show'])->name('monitoring.show');
    Route::post('/monitoring/segarkan', [MonitoringController::class, 'segarkan'])->name('monitoring.segarkan');

    // Sidang / presentasi
    Route::resource('sidang', SidangController::class)->except('show');
    Route::post('/sidang/{sidang}/peserta', [SidangController::class, 'tambahPeserta'])->name('sidang.peserta.tambah');
    Route::delete('/sidang/{sidang}/peserta/{peserta}', [SidangController::class, 'hapusPeserta'])->name('sidang.peserta.hapus');
    Route::post('/sidang/{sidang}/penguji', [SidangController::class, 'tambahPenguji'])->name('sidang.penguji.tambah');
    Route::post('/sidang/{sidang}/nilai', [SidangController::class, 'nilai'])->name('sidang.nilai');

    // Sertifikat
    Route::get('/sertifikat', [SertifikatController::class, 'index'])->name('sertifikat.index');
    Route::get('/sertifikat/create', [SertifikatController::class, 'create'])->name('sertifikat.create');
    Route::post('/sertifikat', [SertifikatController::class, 'store'])->name('sertifikat.store');
    Route::get('/sertifikat/{sertifikat}', [SertifikatController::class, 'show'])->name('sertifikat.show');
    Route::get('/sertifikat/{sertifikat}/download', [SertifikatController::class, 'download'])->name('sertifikat.download');
    Route::post('/sertifikat/{sertifikat}/cabut', [SertifikatController::class, 'cabut'])->name('sertifikat.cabut');
    Route::get('/sertifikat/{sertifikat}/qr', [SertifikatController::class, 'qr'])->name('sertifikat.qr');

    // Konfigurasi penilaian
    Route::resource('kategori-penilaian', PenilaianKategoriController::class)->except('show');
    Route::post('/kategori-penilaian/komponen', [PenilaianKategoriController::class, 'updateKomponen'])
        ->name('kategori-penilaian.komponen');

    // Audit log
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-log/export', [AuditLogController::class, 'export'])->name('audit.export');

    // System health
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system.health');

    // Pengaturan
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/{setting}/hapus', [SettingsController::class, 'destroy'])->name('settings.destroy');

    // Pengumuman
    Route::get('/pengumuman', [\App\Http\Controllers\Admin\PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [\App\Http\Controllers\Admin\PengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [\App\Http\Controllers\Admin\PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\Admin\PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
});