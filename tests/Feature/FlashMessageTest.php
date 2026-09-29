<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Notifikasi hasil aksi (flash message).
 *
 * Dashboard-reading notification ini dulu tidak pernah dirender sama sekali:
 * layout aplikasi tidak punya elemen flash, jadi pesan "berhasil disimpan"
 * dari setiap controller hilang begitu saja. Test di bawah mengunci bahwa
 * layout kembali menampilkannya.
 */
class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'email' => 'flash@sipkl.test',
            'password' => 'password',
        ]);
    }

    public function test_layout_aplikasi_menampilkan_pesan_flash(): void
    {
        $this->actingAs($this->user())
            ->withSession(['success' => 'Data berhasil disimpan.'])
            ->get(route('perangkat'))
            ->assertOk()
            ->assertSee('sipkl-flash', false)
            ->assertSee('Data berhasil disimpan.');
    }

    public function test_layout_aplikasi_menampilkan_pesan_gagal(): void
    {
        $this->actingAs($this->user())
            ->withSession(['error' => 'Gagal menyimpan data.'])
            ->get(route('perangkat'))
            ->assertOk()
            ->assertSee('Gagal menyimpan data.');
    }

    public function test_layout_guest_menampilkan_pesan_flash(): void
    {
        $this->withSession(['success' => 'Kata sandi berhasil diubah.'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('sipkl-flash', false)
            ->assertSee('Kata sandi berhasil diubah.');
    }

    public function test_pesan_validasi_ikut_ditampilkan(): void
    {
        // Validasi sungguhan: kirim data tidak valid ke endpoint profil.
        $this->actingAs($this->user())
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), ['name' => '', 'email' => 'bukan-email'])
            ->assertSessionHasErrors(['name', 'email']);

        $html = $this->get(route('profile.edit'))->assertOk()->getContent();

        $this->assertStringContainsString('sipkl-flash', $html);
        $this->assertStringContainsString('warning', $html);
    }

    public function test_tidak_ada_pesan_saat_tidak_ada_flash(): void
    {
        $this->actingAs($this->user())
            ->get(route('perangkat'))
            ->assertOk()
            ->assertDontSee('sipkl-flash', false);
    }

    public function test_pesan_login_dan_logout_ada(): void
    {
        $user = $this->user();

        $this->post(route('login'), ['login' => $user->email, 'password' => 'password'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->post(route('logout'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('success');
    }

    public function test_pesan_bukan_kunci_mesin(): void
    {
        // Nilai flash harus kalimat yang bisa dibaca user, bukan kunci internal
        // seperti 'profile-updated' atau 'passwords.sent'.
        $kunciMesin = ['profile-updated', 'foto-updated', 'password-updated', 'passwords.sent', 'passwords.user'];

        $files = glob(app_path('Http/Controllers/**/*.php')) ?: [];
        foreach ($files as $file) {
            $isi = (string) file_get_contents($file);

            preg_match_all("/->with\(\s*'(?:status|success)'\s*,\s*'([^']*)'/", $isi, $m);

            foreach ($m[1] as $pesan) {
                $this->assertNotContains($pesan, $kunciMesin, "Pesan flash di {$file} masih berupa kunci mesin: {$pesan}");
            }
        }
    }
}
