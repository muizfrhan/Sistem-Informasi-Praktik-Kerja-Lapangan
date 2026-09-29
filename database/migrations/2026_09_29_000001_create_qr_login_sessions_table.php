<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permintaan login QR.
 *
 * Satu baris = satu QR yang ditampilkan di perangkat baru (Device B).
 * Token TIDAK pernah disimpan apa adanya: kolom `token_hash` menyimpan
 * SHA-256 dari token, sedangkan token aslinya hanya ada di dalam gambar QR
 * milik Device B. Password / kredensial tidak pernah ikut tersimpan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_login_sessions', function (Blueprint $table) {
            $table->id();

            // SHA-256 token 64 karakter, unik & tidak bisa ditebak balik.
            $table->string('token_hash', 64)->unique();

            // pending | scanned | awaiting_confirmation | approved | rejected |
            // authenticated | expired | used
            $table->string('status', 32)->default('pending')->index();

            // Identitas Device B (perminta), dibaca user saat konfirmasi.
            $table->string('device_name', 120)->nullable();
            $table->string('browser', 80)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('ip', 45)->nullable();

            // Siapa yang mengotorisasi, dan kapan.
            $table->foreignId('approved_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('authenticated_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->string('authenticated_ip', 45)->nullable();

            // Token hanya berlaku singkat dan hanya bisa dipakai satu kali.
            // dateTime (bukan timestamp) supaya MySQL tidak memasang default
            // '0000-00-00 00:00:00' yang ditolak pada mode strict.
            $table->dateTime('expires_at')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_login_sessions');
    }
};
