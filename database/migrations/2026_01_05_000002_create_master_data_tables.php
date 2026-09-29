<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master data: periode PKL, jurusan, kelas, lokasi, settings,
 * pembimbing perusahaan, danimatextended profil pada mahasiswa/dosen/perusahaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Periode PKL ----------
        Schema::create('periode_pkl', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();          // "2026/2027"
            $table->string('nama', 100);                    // "PKL 2026/2027 Ganjil"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', [
                'draft', 'pendaftaran', 'penempatan',
                'pelaksanaan', 'evaluasi', 'selesai',
            ])->default('draft');
            $table->date('batas_pendaftaran')->nullable();
            $table->date('batas_laporan')->nullable();
            $table->unsignedInteger('kuota_total')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        // ---------- Jurusan ----------
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('jenjang', 50)->nullable();       // D3 / S1 / SMK
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // ---------- Kelas ----------
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->cascadeOnDelete();
            $table->string('nama', 50);                     // "TI-5A"
            $table->string('tingkat', 20)->nullable();       // "V" / "5"
            $table->string('wali_kelas', 100)->nullable();
            $table->unsignedInteger('kuota')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jurusan_id', 'nama']);
        });

        // ---------- Lokasi PKL ----------
        Schema::create('lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('alamat', 255);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // ---------- Settings ----------
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();
            $table->string('group', 40)->default('umum');  // umum, branding, kontak, pkl, penilaian, upload, keamanan
            $table->string('type', 20)->default('string'); // string, bool, int, json, text
            $table->string('label', 100)->nullable();
            $table->timestamps();

            $table->index('group');
        });

        // ---------- Pembimbing perusahaan ----------
        Schema::create('pembimbing_perusahaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('jabatan', 100)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('foto', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('perusahaan_id');
        });

        // ---------- Profil diperluas: mahasiswa ----------
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete()->after('nama');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete()->after('jurusan_id');
            $table->string('nim')->nullable()->change();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('email', 120)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'lulus', 'pindah'])->default('aktif');
        });

        // ---------- Profil diperluas: dosen ----------
        Schema::table('dosen', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete()->after('nama');
            $table->string('nip')->nullable()->change();
            $table->string('email', 120)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'pensiun'])->default('aktif');
        });

        // ---------- Profil diperluas: perusahaan ----------
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->string('logo', 255)->nullable();
            $table->string('bidang', 150)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->unsignedInteger('kuota_siswa')->default(0);
            $table->enum('status_kerja_sama', ['aktif', 'penuh', 'nonaktif'])->default('aktif');
            $table->text('deskripsi')->nullable();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi')->nullOnDelete();
        });

        // ---------- Pendaftaran PKL disambungkan ke periode_pkl ----------
        Schema::table('pendaftaran_pkl', function (Blueprint $table) {
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->nullOnDelete();
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('dosen')->nullOnDelete();
        });

        // Backfill periode_pkl dari nilai periode yang sudah ada
        $periods = DB::table('pendaftaran_pkl')
            ->select('periode')
            ->distinct()
            ->pluck('periode')
            ->filter()
            ->values();

        foreach ($periods as $period) {
            $exists = DB::table('periode_pkl')->where('kode', $period)->exists();
            if (! $exists) {
                DB::table('periode_pkl')->insert([
                    'kode' => $period,
                    'nama' => 'PKL ' . $period,
                    'tanggal_mulai' => now()->startOfYear()->toDateString(),
                    'tanggal_selesai' => now()->endOfYear()->toDateString(),
                    'status' => 'pelaksanaan',
                    'deskripsi' => 'Periode hasil backfill dari data pendaftaran lama.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Subquery harus berupa SQL mentah: menyisipkan objek Query Builder ke
        // dalam array update() membuat PDO gagal mem-bind nilainya.
        DB::table('pendaftaran_pkl')
            ->whereNotNull('periode')
            ->update([
                'periode_id' => DB::raw(
                    '(select `id` from `periode_pkl` where `periode_pkl`.`kode` = `pendaftaran_pkl`.`periode` limit 1)'
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('pendaftaran_pkl', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropForeign(['dosen_pembimbing_id']);
            $table->dropColumn(['periode_id', 'dosen_pembimbing_id']);
        });

        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn([
                'logo', 'bidang', 'kota', 'provinsi', 'website', 'email', 'telepon',
                'contact_person', 'kuota_siswa', 'status_kerja_sama', 'deskripsi', 'lokasi_id',
            ]);
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn([
                'jurusan_id', 'email', 'no_hp', 'jabatan', 'alamat', 'foto', 'status',
            ]);
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn([
                'jurusan_id', 'kelas_id', 'jenis_kelamin', 'tempat_lahir',
                'tanggal_lahir', 'email', 'no_hp', 'alamat', 'foto', 'status',
            ]);
        });

        Schema::dropIfExists('pembimbing_perusahaan');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('lokasi');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('jurusan');
        Schema::dropIfExists('periode_pkl');
    }
};
