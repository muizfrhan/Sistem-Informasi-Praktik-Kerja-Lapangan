<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul operasional: absensi, jurnal harian, izin/sakit, tugas PKL,
 * monitoring, dan peringatan otomatis.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Absensi ----------
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_pkl')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'libur'])
                ->default('hadir');
            $table->unsignedInteger('durasi_menit')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('dalam_radius')->nullable();
            $table->foreignId('izin_id')->nullable();
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu siswa hanya boleh satu baris absensi per hari
            $table->unique(['mahasiswa_id', 'periode_id', 'tanggal'], 'absensi_unik_harian');
            $table->index(['periode_id', 'tanggal']);
            $table->index('status');
        });

        // ---------- Izin / Sakit ----------
        Schema::create('izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->nullOnDelete();
            $table->enum('jenis', ['izin', 'sakit', 'keluarga'])->default('izin');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            $table->string('lampiran', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('alasan_keputusan')->nullable();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'status']);
        });

        // foreign key ditambahkan setelah tabel izin ada
        Schema::table('absensi', function (Blueprint $table) {
            $table->foreign('izin_id')->references('id')->on('izin')->nullOnDelete();
        });

        // ---------- Jurnal harian ----------
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->nullOnDelete();
            $table->date('tanggal');
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('output')->nullable();
            $table->text('kendala')->nullable();
            $table->text('solusi')->nullable();
            $table->json('dokumentasi')->nullable();   // array path file
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'revision'])
                ->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->unsignedTinyInteger('revisi')->default(0);
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tanggal']);
            $table->index('status');
        });

        // ---------- Komentar jurnal ----------
        Schema::create('jurnal_komentar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnal')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('komentar');
            $table->timestamps();
        });

        // ---------- Tugas PKL ----------
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->dateTime('deadline');
            $table->json('attachment')->nullable();
            $table->boolean('wajib')->default(true);
            $table->timestamps();

            $table->index('deadline');
        });

        Schema::create('tugas_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tugas_id', 'mahasiswa_id']);
        });

        Schema::create('tugas_submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->json('file')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'submitted', 'reviewed', 'revision', 'completed'])
                ->default('pending');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['tugas_id', 'mahasiswa_id']);
        });

        // ---------- Monitoring ----------
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('progres_pkl', 5, 2)->default(0);        // 0-100
            $table->decimal('progres_absensi', 5, 2)->default(0);    // 0-100
            $table->decimal('progres_jurnal', 5, 2)->default(0);     // 0-100
            $table->decimal('progres_bimbingan', 5, 2)->default(0);  // 0-100
            $table->decimal('progres_laporan', 5, 2)->default(0);    // 0-100
            $table->enum('indikator', ['healthy', 'warning', 'critical'])->default('healthy');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'periode_id', 'tanggal'], 'monitoring_unik_harian');
            $table->index('indikator');
        });

        // ---------- Peringatan otomatis ----------
        Schema::create('peringatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('kode', 40);                 // jurnal_tidak_ada, absensi_rendah, ...
            $table->string('judul', 150);
            $table->text('pesan');
            $table->enum('level', ['info', 'warning', 'critical'])->default('warning');
            $table->string('tautan')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_at']);
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['izin_id']);
        });

        Schema::dropIfExists('peringatan');
        Schema::dropIfExists('monitoring');
        Schema::dropIfExists('tugas_submission');
        Schema::dropIfExists('tugas_target');
        Schema::dropIfExists('tugas');
        Schema::dropIfExists('jurnal_komentar');
        Schema::dropIfExists('jurnal');
        Schema::dropIfExists('izin');
        Schema::dropIfExists('absensi');
    }
};
