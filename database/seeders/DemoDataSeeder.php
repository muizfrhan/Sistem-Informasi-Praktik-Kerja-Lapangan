<?php

namespace Database\Seeders;

use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Lokasi;
use App\Models\Mahasiswa;
use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh agar seluruh modul bisa langsung dicoba.
 * Menghapus user contoh lama lebih dulu supaya idempotent.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Master data ----------
        $ti = Jurusan::firstOrCreate(
            ['kode' => 'TI'],
            ['nama' => 'Teknik Informatika', 'jenjang' => 'D3', 'aktif' => true]
        );

        $akuntansi = Jurusan::firstOrCreate(
            ['kode' => 'AK'],
            ['nama' => 'Akuntansi', 'jenjang' => 'D3', 'aktif' => true]
        );

        $kelas = Kelas::firstOrCreate(
            ['jurusan_id' => $ti->id, 'nama' => 'TI-5A'],
            ['tingkat' => '5', 'wali_kelas' => 'Dosen Pembimbing', 'kuota' => 32, 'aktif' => true]
        );

        $lokasi = Lokasi::firstOrCreate(
            ['nama' => 'Kantor Pusat Mitra'],
            [
                'alamat' => 'Jl. Contoh No. 1, Jakarta',
                'latitude' => -6.2000000,
                'longitude' => 106.8166667,
                'radius_meter' => 150,
                'aktif' => true,
            ]
        );

        // ---------- Periode PKL ----------
        $tahun = (int) date('Y');
        $periode = PeriodePkl::firstOrCreate(
            ['kode' => $tahun . '/' . ($tahun + 1)],
            [
                'nama' => 'PKL ' . $tahun . '/' . ($tahun + 1),
                'tanggal_mulai' => $tahun . '-01-15',
                'tanggal_selesai' => $tahun . '-06-30',
                'status' => 'pelaksanaan',
                'batas_pendaftaran' => $tahun . '-01-31',
                'batas_laporan' => $tahun . '-07-31',
                'kuota_total' => 200,
                'deskripsi' => 'Periode PKL aktif untuk demonstrasi seluruh modul.',
            ]
        );

        // ---------- Perusahaan ----------
        $perusahaan = Perusahaan::firstOrCreate(
            ['nama' => 'PT Contoh Mitra Teknologi'],
            [
                'alamat' => 'Jl. Contoh No. 1',
                'no_hp' => '081234567890',
                'telepon' => '021-5550123',
                'email' => 'hrd@contohmitra.test',
                'website' => 'https://contohmitra.test',
                'bidang' => 'Pengembangan perangkat lunak',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'contact_person' => 'Budi Santoso',
                'kuota_siswa' => 10,
                'status_kerja_sama' => 'aktif',
                'deskripsi' => 'Mitra industri contoh untuk uji seluruh modul PKL.',
                'lokasi_id' => $lokasi->id,
            ]
        );

        // ---------- Akun contoh ----------
        $akun = [
            ['admin@sipkl.test', 'Admin SIPKL', 'admin', 'admin@sipkl.test'],
            ['pimpinan@sipkl.test', 'Kepala Sekolah', 'pimpinan', '198001011990031001'],
            ['koordinator@sipkl.test', 'Koordinator PKL', 'koordinator', '198502021991032002'],
            ['dosen@sipkl.test', 'Dosen Pembimbing', 'dosen', '198304122011011001'],
            ['mhs@sipkl.test', 'Mahasiswa', 'mahasiswa', '2310631145'],
        ];

        foreach ($akun as [$email, $nama, $role, $identitas]) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'username' => str($email)->before('@')->toString(),
                    'password' => 'password',
                    'role' => $role,
                    'role_id' => \App\Models\Role::where('name', $role)->value('id'),
                    'status' => 'aktif',
                ]);
            }

            if ($role === 'dosen' && ! $user->dosen) {
                Dosen::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $nama,
                        'nip' => $identitas,
                        'program_studi' => 'Teknik Informatika',
                        'jurusan_id' => $ti->id,
                        'email' => $email,
                        'no_hp' => '081200000001',
                        'jabatan' => 'Dosen Pembimbing',
                        'status' => 'aktif',
                    ]
                );
            }

            if ($role === 'mahasiswa' && ! $user->mahasiswa) {
                Mahasiswa::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $nama,
                        'nim' => $identitas,
                        'program_studi' => 'Teknik Informatika',
                        'jurusan_id' => $ti->id,
                        'kelas_id' => $kelas->id,
                        'kelas' => $kelas->nama,
                        'semester' => '5',
                        'jenis_kelamin' => 'L',
                        'email' => $email,
                        'no_hp' => '081200000002',
                        'status' => 'aktif',
                    ]
                );
            }
        }

        // ---------- Bimbingan contoh ----------
        $dosen = Dosen::first();
        $mhs = Mahasiswa::first();

        if ($dosen && $mhs && ! Bimbingan::where('dosen_id', $dosen->id)->exists()) {
            Bimbingan::create([
                'mahasiswa_id' => $mhs->id,
                'dosen_id' => $dosen->id,
                'tanggal_bimbingan' => now()->subDays(7),
                'catatan' => 'Evaluasi progres awal, pembahasan rencana dan project plan.',
                'status' => 'disetujui',
            ]);

            Bimbingan::create([
                'mahasiswa_id' => $mhs->id,
                'dosen_id' => $dosen->id,
                'tanggal_bimbingan' => now()->addDays(3),
                'catatan' => 'Review mingguan berikutnya.',
                'status' => 'pending',
            ]);
        }

        $this->command?->info('Data demo SIPKL siap. Akun contoh: admin / pimpinan / koordinator / dosen / mhs @sipkl.test (password: password).');
    }
}
