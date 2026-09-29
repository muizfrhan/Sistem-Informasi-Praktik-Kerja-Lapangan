<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian configurable, sidang/presentasi, sertifikat, notifikasi,
 * pengumuman, percakapan, audit log, dan dokumen.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Penilaian ----------
        Schema::create('penilaian_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();      // disiplin, tanggung_jawab, ...
            $table->string('nama', 80);
            $table->text('deskripsi')->nullable();
            $table->decimal('bobot', 5, 2)->default(0);// bobot dalam persen
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('penilaian_komponen', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();      // pembimbing_sekolah, perusahaan, sidang
            $table->string('nama', 80);
            $table->decimal('bobot', 5, 2)->default(0);// bobot dalam persen
            $table->boolean('aktif')->default(true);
            $table->string('sumber', 20)->default('manual'); // manual | otomatis
            $table->timestamps();
        });

        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->cascadeOnDelete();
            $table->string('komponen', 40);            // kode komponen penilaian
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->decimal('total', 5, 2)->nullable();
            $table->string('predikat', 20)->nullable();
            $table->enum('status', ['draft', 'final'])
                ->default('draft');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'periode_id', 'komponen'], 'penilaian_unik_komponen');
        });

        Schema::create('penilaian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('penilaian_kategori')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique(['penilaian_id', 'kategori_id']);
        });

        // ---------- Sidang / Presentasi ----------
        Schema::create('sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->nullable()->constrained('periode_pkl')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('ruangan', 80);
            $table->enum('status', ['jadwal', 'berlangsung', 'selesai', 'batal'])->default('jadwal');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'ruangan']);
        });

        Schema::create('sidang_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidang_id')->constrained('sidang')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->enum('status', ['terjadwal', 'hadir', 'tidak_hadir', 'selesai'])
                ->default('terjadwal');
            $table->timestamps();

            $table->unique(['sidang_id', 'mahasiswa_id']);
        });

        Schema::create('sidang_penguji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidang_id')->constrained('sidang')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('jabatan', 100)->nullable();
            $table->decimal('nilai_presentasi', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        // ---------- Sertifikat ----------
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_pkl')->cascadeOnDelete();
            $table->string('nomor', 40)->unique();      // SIPKL/2026/0001
            $table->string('kode_verifikasi', 64)->unique(); // token untuk QR
            $table->date('tanggal_terbit');
            $table->unsignedInteger('durasi_hari')->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 20)->nullable();
            $table->string('file_pdf', 255)->nullable();
            $table->enum('status', ['draft', 'terbit', 'dicabut'])->default('draft');
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diterbitkan_at')->nullable();
            $table->timestamps();

            $table->index(['periode_id', 'status']);
        });

        Schema::create('verifikasi_sertifikat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sertifikat_id')->constrained('sertifikat')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('sertifikat_id');
        });

        // ---------- Notifikasi ----------
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipe', 40);                // sistem, approval, pengingat, jurnal, absensi, bimbingan, penilaian
            $table->string('judul', 150);
            $table->text('pesan');
            $table->json('data')->nullable();
            $table->string('tautan')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_at']);
        });

        // ---------- Pengumuman ----------
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('isi');
            $table->string('lampiran', 255)->nullable();
            $table->enum('target', [
                'semua', 'mahasiswa', 'dosen', 'pembimbing_perusahaan', 'pimpinan', 'koordinator',
            ])->default('semua');
            $table->boolean('pin')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['target', 'published_at']);
        });

        // ---------- Percakapan (chat) ----------
        Schema::create('percakapan', function (Blueprint $table) {
            $table->id();
            $table->string('konteks', 30)->default('umum'); // bimbingan, laporan, absensi, umum
            $table->string('judul', 150)->nullable();
            $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswa')->cascadeOnDelete();
            $table->timestamp('pesan_terakhir_at')->nullable();
            $table->timestamps();
        });

        Schema::create('percakapan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('percakapan_id')->constrained('percakapan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('terakhir_dibaca')->nullable();
            $table->timestamps();

            $table->unique(['percakapan_id', 'user_id']);
        });

        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('percakapan_id')->constrained('percakapan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('isi');
            $table->string('attachment', 255)->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['percakapan_id', 'created_at']);
        });

        // ---------- Audit log ----------
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 100)->nullable();
            $table->string('role', 30)->nullable();
            $table->string('action', 40);              // create, update, delete, approve, ...
            $table->string('module', 50);              // absensi, jurnal, users, ...
            $table->text('keterangan')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['module', 'action']);
            $table->index('user_id');
        });

        // ---------- Dokumen ----------
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('path', 255);
            $table->string('disk', 20)->default('public');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->string('tipe', 30)->default('lainnya'); // laporan, tugas, izin, sertifikat, jurnal, ...
            $table->string('related_type', 40)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('pesan');
        Schema::dropIfExists('percakapan_peserta');
        Schema::dropIfExists('percakapan');
        Schema::dropIfExists('pengumuman');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('verifikasi_sertifikat');
        Schema::dropIfExists('sertifikat');
        Schema::dropIfExists('sidang_penguji');
        Schema::dropIfExists('sidang_peserta');
        Schema::dropIfExists('sidang');
        Schema::dropIfExists('penilaian_detail');
        Schema::dropIfExists('penilaian');
        Schema::dropIfExists('penilaian_komponen');
        Schema::dropIfExists('penilaian_kategori');
    }
};
