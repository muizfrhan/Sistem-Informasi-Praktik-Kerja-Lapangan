<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fondasi role & permission.
 *
 * - Mengubah `users.role` dari enum ke varchar supaya role baru bisa ditambahkan
 *   tanpa mengubah tipe kolom.
 * - Menambah tabel roles / permissions / pivot + profil users.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah role dari enum ke varchar (agar role baru tidak perlu ALTER TYPE)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('mahasiswa')->change();
        });

        // 2. Tabel roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();          // slug: admin, dosen, mahasiswa, ...
            $table->string('label', 60);                   // "Administrator"
            $table->string('description', 255)->nullable();
            $table->unsignedTinyInteger('level')->default(5); // 1=puncak (admin) .. 5=staf biasa
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();          // "mahasiswa.absensi.view"
            $table->string('label', 100);
            $table->string('group', 40)->default('umum');  // absensi, jurnal, master, ...
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        // 4. Pivot role <-> permission
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // 5. Profil & status pada users
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable()->after('name');
            $table->string('no_hp', 25)->nullable()->after('email');
            $table->string('alamat', 255)->nullable()->after('no_hp');
            $table->string('foto', 255)->nullable()->after('alamat');
            $table->enum('status', ['aktif', 'nonaktif', 'ditunda'])->default('aktif')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->unsignedBigInteger('role_id')->nullable()->after('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        // Backfill: buat role dari nilai enum lama, lalu tautkan ke user yang ada
        $defaultRoles = [
            ['name' => 'admin', 'label' => 'Administrator', 'description' => 'Pengelola utama sistem', 'level' => 1],
            ['name' => 'dosen', 'label' => 'Dosen Pembimbing', 'description' => 'Pembimbing dari pihak sekolah', 'level' => 3],
            ['name' => 'mahasiswa', 'label' => 'Mahasiswa', 'description' => 'Peserta Praktik Kerja Lapangan', 'level' => 5],
        ];

        foreach ($defaultRoles as $role) {
            DB::table('roles')->insertOrIgnore($role + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach (DB::table('roles')->pluck('id', 'name') as $name => $id) {
            DB::table('users')->where('role', $name)->whereNull('role_id')->update(['role_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['username', 'no_hp', 'alamat', 'foto', 'status', 'last_login_at', 'role_id']);
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'mahasiswa', 'dosen'])->default('mahasiswa')->change();
        });
    }
};
