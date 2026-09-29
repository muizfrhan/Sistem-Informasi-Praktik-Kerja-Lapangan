<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => '08'.fake()->numerify('##########'),
            // Proyek ini menghapus kolom `email_verified_at`
            // (migration 2025_05_27_171842), jadi tidak dibuat di factory.
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Default factory: akun mahasiswa aktif (peran paling sering diuji).
            'role' => 'mahasiswa',
            'role_id' => null,
            'status' => 'aktif',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function dosen(): static
    {
        return $this->state(fn () => ['role' => 'dosen']);
    }

    public function mahasiswa(): static
    {
        return $this->state(fn () => ['role' => 'mahasiswa']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'ditunda']);
    }
}
