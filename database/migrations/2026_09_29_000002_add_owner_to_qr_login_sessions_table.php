<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemilik QR Login.
 *
 * QR dibuat dari dashboard, jadi harus diketahui siapa akunnya. Kolom ini
 * dipakai untuk:
 *   - membatasi notifikasi "Izinkan / Tolak" hanya ke perangkat milik pemilik
 *     QR tersebut (bukan ke semua akun yang sedang login), dan
 *   - mencatat Devices mana yang benar-benar meminta akses.
 *
 * `nullOnDelete()`: kalau akun dihapus, permintaannya ikut hilang supaya
 * tidak ada permintaan yatim yang bisa disetujui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_login_sessions', function (Blueprint $table) {
            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('token_hash')
                ->constrained('users')
                ->nullOnDelete();

            // Polling dashboard selalu memfilter owner + status + masa berlaku.
            $table->index(['owner_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('qr_login_sessions', function (Blueprint $table) {
            $table->dropIndex(['owner_user_id', 'status']);
            $table->dropConstrainedForeignId('owner_user_id');
        });
    }
};
