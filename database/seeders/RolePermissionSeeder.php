<?php

namespace Database\Seeders;

use App\Models\PenilaianKategori;
use App\Models\PenilaianKomponen;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed role, permission, dan konfigurasi penilaian awal.
 * Aman dijalankan berulang kali (idempotent).
 */
class RolePermissionSeeder extends Seeder
{
    /** Definisi role: [slug, label, deskripsi, level] */
    private const ROLES = [
        ['admin', 'Administrator', 'Pengelola utama sistem SIPKL', 1],
        ['pimpinan', 'Pimpinan Sekolah', 'Kepala sekolah: executive dashboard & laporan', 2],
        ['koordinator', 'Koordinator PKL', 'Mengelola periode, verifikasi, dan penempatan', 3],
        ['dosen', 'Dosen Pembimbing', 'Pembimbing dari pihak sekolah', 4],
        ['pembimbing_perusahaan', 'Pembimbing Perusahaan', 'Pembimbing dari pihak industri', 5],
        ['mahasiswa', 'Mahasiswa', 'Peserta Praktik Kerja Lapangan', 6],
    ];

    /**
     * Permission dikelompokkan per modul.
     * Format: "grup" => [ ['nama', 'label'], ... ]
     */
    private const PERMISSIONS = [
        'dashboard' => [
            ['dashboard.lihat', 'Lihat Dashboard'],
            ['dashboard.eksekutif', 'Lihat Executive Dashboard'],
        ],
        'master' => [
            ['master.pengguna', 'Kelola Pengguna'],
            ['master.mahasiswa', 'Kelola Data Mahasiswa'],
            ['master.dosen', 'Kelola Data Dosen'],
            ['master.jurusan', 'Kelola Jurusan'],
            ['master.kelas', 'Kelola Kelas'],
            ['master.periode', 'Kelola Periode PKL'],
            ['master.perusahaan', 'Kelola Perusahaan'],
            ['master.pembimbing_perusahaan', 'Kelola Pembimbing Perusahaan'],
            ['master.lokasi', 'Kelola Lokasi'],
            ['master.pengaturan', 'Kelola Pengaturan Sistem'],
        ],
        'pendaftaran' => [
            ['pendaftaran.lihat', 'Lihat Pendaftaran'],
            ['pendaftaran.kelola', 'Kelola Pendaftaran'],
            ['pendaftaran.verifikasi', 'Verifikasi Pendaftaran'],
        ],
        'penempatan' => [
            ['penempatan.lihat', 'Lihat Penempatan'],
            ['penempatan.kelola', 'Kelola Penempatan'],
        ],
        'absensi' => [
            ['absensi.lihat', 'Lihat Absensi'],
            ['absensi.kelola', 'Kelola Absensi'],
            ['absensi.input', 'Input Absensi Sendiri'],
            ['absensi.konfirmasi', 'Konfirmasi Absensi'],
        ],
        'jurnal' => [
            ['jurnal.lihat', 'Lihat Jurnal'],
            ['jurnal.kelola', 'Kelola Jurnal Sendiri'],
            ['jurnal.review', 'Review Jurnal'],
        ],
        'izin' => [
            ['izin.lihat', 'Lihat Izin'],
            ['izin.ajukan', 'Ajukan Izin'],
            ['izin.proses', 'Proses Izin'],
        ],
        'bimbingan' => [
            ['bimbingan.lihat', 'Lihat Bimbingan'],
            ['bimbingan.ajukan', 'Ajukan Bimbingan'],
            ['bimbingan.kelola', 'Kelola Bimbingan'],
        ],
        'tugas' => [
            ['tugas.lihat', 'Lihat Tugas'],
            ['tugas.buat', 'Buat Tugas'],
            ['tugas.submit', 'Kumpulkan Tugas'],
            ['tugas.review', 'Review Tugas'],
        ],
        'monitoring' => [
            ['monitoring.lihat', 'Lihat Monitoring'],
            ['monitoring.kelola', 'Kelola Monitoring'],
        ],
        'penilaian' => [
            ['penilaian.lihat', 'Lihat Penilaian'],
            ['penilaian.input', 'Input Penilaian'],
            ['penilaian.konfigurasi', 'Konfigurasi Penilaian'],
        ],
        'laporan' => [
            ['laporan.lihat', 'Lihat Laporan'],
            ['laporan.upload', 'Upload Laporan'],
            ['laporan.review', 'Review Laporan'],
            ['laporan.format', 'Kelola Format Laporan'],
        ],
        'sidang' => [
            ['sidang.lihat', 'Lihat Sidang'],
            ['sidang.kelola', 'Kelola Sidang'],
            ['sidang.nilai', 'Nilai Presentasi'],
        ],
        'sertifikat' => [
            ['sertifikat.lihat', 'Lihat Sertifikat'],
            ['sertifikat.terbitkan', 'Terbitkan Sertifikat'],
            ['sertifikat.cabut', 'Cabut Sertifikat'],
        ],
        'komunikasi' => [
            ['chat.lihat', 'Lihat Chat'],
            ['chat.kirim', 'Kirim Pesan'],
            ['pengumuman.lihat', 'Lihat Pengumuman'],
            ['pengumuman.kelola', 'Kelola Pengumuman'],
        ],
        'notifikasi' => [
            ['notifikasi.lihat', 'Lihat Notifikasi'],
        ],
        'sistem' => [
            ['audit.lihat', 'Lihat Audit Log'],
            ['backup.kelola', 'Kelola Backup'],
            ['import.data', 'Import Data'],
            ['export.data', 'Export Data'],
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as [$name, $label, $description, $level]) {
            Role::updateOrCreate(
                ['name' => $name],
                ['label' => $label, 'description' => $description, 'level' => $level, 'is_active' => true]
            );
        }

        foreach (self::PERMISSIONS as $group => $list) {
            foreach ($list as [$name, $label]) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    ['label' => $label, 'group' => $group]
                );
            }
        }

        $this->attachPermissions();
        $this->seedPenilaian();
        $this->seedSettings();
    }

    /**
     * Pemetaan permission per role.
     * Role yang tidak disebut = hanya permission dengan prefix yang sama.
     */
    private function attachPermissions(): void
    {
        $all = Permission::pluck('id', 'name');
        $allNames = $all->keys()->all();

        $map = [
            'admin' => $allNames, // semua permission

            'pimpinan' => [
                'dashboard.lihat', 'dashboard.eksekutif',
                'master.perusahaan', 'master.periode',
                'pendaftaran.lihat', 'penempatan.lihat', 'absensi.lihat',
                'jurnal.lihat', 'monitoring.lihat', 'penilaian.lihat',
                'laporan.lihat', 'sertifikat.lihat', 'pengumuman.lihat',
                'notifikasi.lihat', 'export.data',
            ],

            'koordinator' => [
                'dashboard.lihat',
                'master.periode', 'master.perusahaan', 'master.pembimbing_perusahaan', 'master.mahasiswa', 'master.dosen',
                'pendaftaran.lihat', 'pendaftaran.kelola', 'pendaftaran.verifikasi',
                'penempatan.lihat', 'penempatan.kelola',
                'absensi.lihat', 'jurnal.lihat', 'izin.proses',
                'bimbingan.lihat', 'bmbiobing.kelola',
                'monitoring.lihat', 'monitoring.kelola',
                'laporan.lihat', 'laporan.review', 'laporan.format',
                'sertifikat.lihat', 'sertifikat.terbitkan',
                'chat.lihat', 'chat.kirim', 'pengumuman.lihat', 'pengumuman.kelola',
                'notifikasi.lihat', 'export.data',
            ],

            'dosen' => [
                'dashboard.lihat',
                'absensi.lihat', 'jurnal.lihat', 'jurnal.review',
                'izin.lihat', 'izin.proses',
                'bimbingan.lihat', 'bmbiobing.kelola',
                'tugas.lihat', 'tugas.buat', 'tugas.review',
                'monitoring.lihat',
                'penilaian.lihat', 'penilaian.input',
                'laporan.lihat', 'laporan.review',
                'sidang.lihat', 'sidang.nilai',
                'chat.lihat', 'chat.kirim', 'pengumuman.lihat',
                'notifikasi.lihat',
            ],

            'pembimbing_perusahaan' => [
                'dashboard.lihat',
                'absensi.lihat', 'absensi.konfirmasi',
                'jurnal.lihat', 'jurnal.review',
                'bimbingan.lihat', 'bimbingan.ajukan',
                'tugas.lihat',
                'monitoring.lihat',
                'penilaian.lihat', 'penilaian.input',
                'chat.lihat', 'chat.kirim', 'notifikasi.lihat',
            ],

            'mahasiswa' => [
                'dashboard.lihat',
                'pendaftaran.lihat', 'pendaftaran.kelola',
                'absensi.input', 'jurnal.lihat', 'jurnal.kelola',
                'izin.lihat', 'izin.ajukan',
                'bimbingan.lihat', 'bimbingan.ajukan',
                'tugas.lihat', 'tugas.submit',
                'laporan.lihat', 'laporan.upload',
                'sertifikat.lihat',
                'sidang.lihat',
                'chat.lihat', 'chat.kirim', 'pengumuman.lihat',
                'notifikasi.lihat',
            ],
        ];

        // Perbaiki nama permission lama (jika seeder versi lama pernah jalan)
        $fix = [
            'bmbiobing.kelola' => 'bimbingan.kelola',
            'peniliation.konfigurasi' => 'penilaian.konfigurasi',
        ];

        foreach ($fix as $wrong => $right) {
            $id = DB::table('permissions')->where('name', $wrong)->value('id');
            if ($id) {
                DB::table('permissions')->where('id', $id)->update([
                    'name' => $right,
                    'label' => str_replace('_', ' ', ucwords(str_replace('_', ' ', $right))),
                ]);
            }
        }

        foreach ($map as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $ids = collect($permissions)
                ->flatMap(fn($p) => explode('|', $p))
                ->map(fn($p) => trim($p))
                ->filter()
                ->mapWithKeys(function ($n) use ($all) {
                    $n = $fix[$n] ?? $n;
                    return [$n => $all[$n] ?? DB::table('permissions')->where('name', $n)->value('id')];
                })
                ->filter()
                ->values()
                ->all();

            DB::table('permission_role')->where('role_id', $role->id)->delete();
            DB::table('permission_role')->insert(
                collect($ids)
                    ->map(fn($pid) => [
                        'role_id' => $role->id,
                        'permission_id' => $pid,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all()
            );
        }
    }

    private function seedPenilaian(): void
    {
        $kategori = [
            ['disiplin', 'Disiplin', 'Kepatuhan terhadap aturan dan jadwal kerja', 15],
            ['tanggung_jawab', 'Tanggung Jawab', 'Konsistensi dan akuntabilitas dalam menjalankan tugas', 15],
            ['komunikasi', 'Komunikasi', 'Efektivitas komunikasi dengan atasan dan rekan kerja', 10],
            ['kerja_sama', 'Kerja Sama', 'Kolaborasi dalam tim kerja', 10],
            ['sikap', 'Sikap', 'Sikap profesional dan sopan santun', 10],
            ['kompetensi', 'Kompetensi Teknis', 'Penguasaan bidang kerja sesuai penempatan', 20],
            ['kreativitas', 'Kreativitas', 'Ide baru dan solusi inovatif', 10],
            ['produktivitas', 'Produktivitas', 'Kuantitas dan kualitas hasil kerja', 5],
            ['kehadiran', 'Kehadiran', 'Ketepatan dan kelengkapan absensi', 5],
        ];

        foreach ($kategori as $i => [$kode, $nama, $deskripsi, $bobot]) {
            PenilaianKategori::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'deskripsi' => $deskripsi, 'bobot' => $bobot, 'urutan' => $i + 1, 'aktif' => true]
            );
        }

        $komponen = [
            ['pembimbing_sekolah', 'Nilai Pembimbing Sekolah', 40, 'manual'],
            ['perusahaan', 'Nilai Perusahaan', 40, 'manual'],
            ['sidang', 'Nilai Sidang / Presentasi', 20, 'manual'],
        ];

        foreach ($komponen as [$kode, $nama, $bobot, $sumber]) {
            PenilaianKomponen::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'bobot' => $bobot, 'aktif' => true, 'sumber' => $sumber]
            );
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            ['app_nama', 'SIPKL', 'umum', 'string', 'Nama Aplikasi'],
            ['app_tagline', 'Sistem Informasi Praktik Kerja Lapangan', 'umum', 'string', 'Tagline'],
            ['institusi_nama', '', 'umum', 'string', 'Nama Sekolah / Institusi'],
            ['institusi_alamat', '', 'umum', 'string', 'Alamat'],
            ['kontak_email', '', 'kontak', 'string', 'Email Institutional'],
            ['kontak_telepon', '', 'kontak', 'string', 'Telepon'],
            ['kontak_whatsapp', '', 'kontak', 'string', 'Nomor WhatsApp Admin'],
            ['pkl_jam_masuk', '08:00', 'pkl', 'string', 'Batas Jam Masuk'],
            ['pkl_toleransi_telat', '15', 'pkl', 'int', 'Toleransi Terlambat (menit)'],
            ['pkl_jam_pulang', '17:00', 'pkl', 'string', 'Batas Jam Pulang'],
            ['pkl_min_bimbingan_nilai', '4', 'pkl', 'int', 'Minimum sesi bingguan untuk input nilai'],
            ['pkl_maks_jurnal_harian', '1', 'pkl', 'int', 'Maksimal jurnal per hari'],
            ['upload_maks_mb', '10', 'upload', 'int', 'Maksimal ukuran upload (MB)'],
            ['upload_extensi', 'pdf,doc,docx,xls,xlsx,jpg,png', 'upload', 'string', 'Ekstensi yang diizinkan'],
            ['nilai_batas_bawah', '0', 'penilaian', 'int', 'Nilai minimum'],
            ['nilai_batas_atas', '100', 'penilaian', 'int', 'Nilai maksimum'],
            ['nilai_predikat_a', '90', 'penilaian', 'int', 'Batas bawah predikat A'],
            ['nilai_predikat_b', '80', 'penilaian', 'int', 'Batas bawah predikat B'],
            ['nilai_predikat_c', '70', 'penilaian', 'int', 'Batas bawah predikat C'],
            ['sertifikat_nomor_prefix', 'SIPKL', 'umum', 'string', 'Prefix nomor sertifikat'],
            ['peringatan_jurnal_hari', '3', 'peringatan', 'int', 'Batas hari jurnal kosong'],
            ['peringatan_absensi_persen', '80', 'peringatan', 'int', 'Batas minimum kehadiran (%)'],
        ];

        foreach ($defaults as [$key, $value, $group, $type, $label]) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'type' => $type, 'label' => $label]
            );
        }
    }
}
