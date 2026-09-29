<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Foto profil: unggah, ganti, dan hapus.
 *
 * Cakupannya mencakup lapis keamanan upload:
 *   - hanya gambar JPG/PNG/WEBP,
 *   - MIME asli diperiksa (bukan sekadar nama berkas),
 *   - ukuran dibatasi,
 *   - nama berkas diacak,
 *   - berkas lama baru dihapus setelah yang baru tersimpan.
 */
class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private static int $urut = 0;

    private function user(): User
    {
        self::$urut++;

        return User::factory()->create([
            'email' => 'foto' . self::$urut . '@sipkl.test',
            'password' => 'password',
        ]);
    }

    /** PNG 1x1 yang benar-benar valid. */
    private function png(string $name = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 10, 10);
    }

    // ==================================================================
    // Unggah
    // ==================================================================

    public function test_bisa_unggah_foto_profil(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success', 'Foto profil berhasil disimpan.');

        $user->refresh();

        $this->assertNotNull($user->foto);
        Storage::disk('public')->assertExists($user->foto);
    }

    public function test_nama_berkas_diacak_dan_tidak_apa_adanya_nama_asli(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png('nama-asli-saya.png'),
        ]);

        $path = $user->refresh()->foto;

        $this->assertStringNotContainsString('nama-asli-saya', $path);
        $this->assertStringContainsString((string) $user->id, $path);
        $this->assertStringEndsWith('.png', $path);
    }

    public function test_foto_tersimpan_di_folder_foto_profil(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $this->assertStringStartsWith(User::FOTO_FOLDER . '/', $user->refresh()->foto);
    }

    public function test_tanpa_foto_tetap_bisa_memperbarui_nama_dan_email(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('Nama Baru', $user->refresh()->name);
        $this->assertNull($user->foto);
    }

    // ==================================================================
    // Ganti
    // ==================================================================

    public function test_mengganti_foto_hapus_foto_lama(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png('lama.png'),
        ]);

        $lama = $user->refresh()->foto;
        $this->assertNotNull($lama);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png('baru.png'),
        ]);

        $baru = $user->refresh()->foto;

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);   // file lama benar-benar hilang
        Storage::disk('public')->assertExists($baru);
    }

    // ==================================================================
    // Hapus
    // ==================================================================

    public function test_bisa_hapus_foto_profil(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $path = $user->refresh()->foto;

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'hapus_foto' => '1',
        ])->assertRedirect(route('profile.edit'));

        $this->assertNull($user->refresh()->foto);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_memilih_foto_baru_membatalkan_permintaan_hapus(): void
    {
        $user = $this->user();

        // pasang foto dulu
        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png('lama.png'),
        ]);

        // lalu unggah yang baru SEKALIGUS hapus_foto=1 -> foto baru yang menang
        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'hapus_foto' => '1',
            'foto' => $this->png('baru.png'),
        ]);

        $this->assertNotNull($user->refresh()->foto);
    }

    // ==================================================================
    // Keamanan
    // ==================================================================

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('foto');

        $this->assertNull($user->refresh()->foto);
    }

    public function test_skrip_php_disamarkan_sebagai_png_ditolak(): void
    {
        $user = $this->user();

        // UploadedFile::fake() selalu melaporkan MIME dari NAMA berkas, jadi
        // tidak bisa menguji pemeriksaan MIME asli. Berkas asli dipakai di
        // sini, dengan klaim browser yang berbohong (image/png).
        $asli = tempnam(sys_get_temp_dir(), 'evil') . '.png';
        file_put_contents($asli, "<?php system('id'); ?>");

        $berkas = new UploadedFile($asli, 'evil.png', 'image/png', null, true);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $berkas,
        ])->assertSessionHasErrors('foto');

        $this->assertNull($user->refresh()->foto);
        $this->assertEmpty(Storage::disk('public')->allFiles());

        @unlink($asli);
    }

    public function test_foto_terlalu_besar_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => UploadedFile::fake()->image('besar.png')->size(6000), // 6MB
        ])->assertSessionHasErrors('foto');

        $this->assertNull($user->refresh()->foto);
    }

    public function test_hanya_pemilik_yang_bisa_mengubah_fotonya(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $path = $user->refresh()->foto;
        $this->assertNotNull($path);

        // akun lain yang mencoba menimpa -> datanya sendiri, bukan milik user pertama
        $lain = $this->user();
        $lain->forceFill(['email' => 'lain@sipkl.test'])->save();

        $this->actingAs($lain)->patch(route('profile.update'), [
            'name' => 'Orang Lain',
            'email' => 'lain@sipkl.test',
            'foto' => $this->png(),
        ])->assertRedirect(route('profile.edit'));

        $this->assertNotSame($path, $lain->refresh()->foto);
    }

    // ==================================================================
    // Akun & tampilan
    // ==================================================================

    public function test_foto_ikut_terhapus_bersama_akun(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $path = $user->refresh()->foto;

        $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertRedirect('/');

        Storage::disk('public')->assertMissing($path);
    }

    public function test_foto_url_null_bila_belum_pasang_foto(): void
    {
        $user = $this->user();

        $this->assertNull($user->foto_url);
        $this->assertFalse($user->hasFoto());
    }

    public function test_foto_url_null_bila_berkas_sudah_hilang(): void
    {
        $user = $this->user();

        // path diisi tapi berkasnya tidak ada di disk
        $user->forceFill(['foto' => 'foto-profil/0/hilang.png'])->save();

        $this->assertNull($user->fresh()->foto_url);
    }

    public function test_inisial_nama_dipakai_bila_tidak_ada_foto(): void
    {
        $a = $this->user();
        $a->forceFill(['name' => 'Budi Santoso'])->save();
        $this->assertSame('BS', $a->fresh()->inisial);

        $b = $this->user();
        $b->forceFill(['name' => 'Siti'])->save();
        $this->assertSame('SI', $b->fresh()->inisial);
    }

    public function test_navbar_menampilkan_foto_bila_ada(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('perangkat'))->assertOk()->assertDontSee('Foto Budi', false);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'foto' => $this->png(),
        ]);

        $this->actingAs($user)->get(route('perangkat'))
            ->assertOk()
            ->assertSee('Foto Budi Santoso', false);
    }

    public function test_halaman_profil_muat_komponen_pemilih_foto(): void
    {
        $this->actingAs($this->user())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('photoPicker', false)
            ->assertSee('name="foto"', false)
            ->assertSee('multipart/form-data', false);
    }

    public function test_hapus_foto_membutuhkan_password_yang_benar(): void
    {
        $user = $this->user();

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'salah'])
            ->assertSessionHasErrors('password', null, 'userDeletion');

        $this->assertAuthenticated();
    }
}
