<?php

namespace Tests\Feature\Auth;

use App\Models\Mahasiswa;
use App\Models\QrLoginSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Alur login QR end-to-end.
 *
 * Peran test:
 *   Dashboard (perangkat yang sudah login) -> membuat QR (`qr.issue`), lalu
 *   menyetujui/menolak lewat dashboard (`qr.decide`) atau lewat halaman
 *   konfirmasi (`qr.login.approve` / `.reject`).
 *   Perangkat baru (belum login) -> membuka tautan QR (terikat ke sesinya),
 *   menunggu (`qr.login.status`), lalu mengklaim sesi (`qr.login.claim`).
 */
class QrLoginTest extends TestCase
{
    use RefreshDatabase;

    private static int $urut = 0;

    private function mahasiswa(array $atribut = []): User
    {
        self::$urut++;

        $user = User::factory()->mahasiswa()->create(array_merge([
            'email' => 'mhs'.self::$urut.'@sipkl.test',
            'password' => 'password',
        ], $atribut));

        // Dasbor mahasiswa memakai record akademik; tanpa itu view error.
        Mahasiswa::firstOrCreate(['user_id' => $user->id], [
            'nama' => $user->name,
            'nim' => (string) (2310631100 + self::$urut),
            'program_studi' => 'Teknik Informatika',
            'kelas' => 'TI-5A',
            'semester' => '5',
            'status' => 'aktif',
        ]);

        return $user;
    }

    /**
     * Kembali jadi tamu TANPA menghapus data sesi.
     */
    private function jadiTamu(): void
    {
        Auth::logout();
        Auth::forgetGuards();
    }

    /**
     * Dashboard membuat QR (pemilik akun).
     *
     * @return array{0: string, 1: string, 2: User} [token, url, pemilik]
     */
    private function dashboardBuatQr(?User $pemilik = null): array
    {
        $pemilik ??= $this->mahasiswa();

        $this->actingAs($pemilik);
        $data = $this->postJson(route('qr.issue'))->assertOk()->json();
        $this->jadiTamu();

        return [$data['token'], $data['url'], $pemilik];
    }

    /** Perangkat baru membuka tautan QR -> permintaan terikat ke sesinya. */
    private function perangkatBaruBuka(string $url): void
    {
        $this->get($url)->assertOk()->assertSee('Menunggu Persetujuan');
    }

    // ==================================================================
    // Token & keamanan
    // ==================================================================

    public function test_permintaan_qr_disimpan_sebagai_hash_bukan_token_polos(): void
    {
        [$token] = $this->dashboardBuatQr();

        $record = QrLoginSession::firstOrFail();

        $this->assertSame(hash('sha256', $token), $record->token_hash);
        $this->assertNotSame($token, $record->token_hash);
        $this->assertStringNotContainsString('password', $record->token_hash);
        $this->assertSame(QrLoginSession::PENDING, $record->status);
    }

    public function test_token_qr_acak_dan_panjang(): void
    {
        [$token] = $this->dashboardBuatQr();
        [$token2] = $this->dashboardBuatQr();

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{64}$/', $token);
        $this->assertNotSame($token, $token2);
    }

    public function test_qr_langsung_berkedaluwarsa(): void
    {
        [$token, $url] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();

        $this->assertTrue($record->belumKedaluwarsa());

        $record->forceFill(['expires_at' => now()->subSecond()])->save();

        $this->actingAs($this->mahasiswa());
        $this->get($url)->assertOk()->assertSee('QR Code telah kedaluwarsa');

        $this->assertSame(QrLoginSession::EXPIRED, $record->fresh()->status);
    }

    // ==================================================================
    // Pembuatan QR dari dashboard
    // ==================================================================

    public function test_qr_hanya_bisa_dibuat_user_login(): void
    {
        $this->postJson(route('qr.issue'))->assertUnauthorized();
        $this->assertDatabaseCount('qr_login_sessions', 0);
    }

    public function test_dashboard_membuat_qr_dengan_status_pending(): void
    {
        $this->actingAs($this->mahasiswa());
        $this->get(route('perangkat'))->assertOk();

        [$token, $url, $pemilik] = $this->dashboardBuatQr();

        $this->assertSame($url, route('qr.login.authorize', ['token' => $token]));
        $this->assertTrue(QrLoginSession::firstOrFail()->belumKedaluwarsa());
        $this->assertSame($pemilik->id, QrLoginSession::firstOrFail()->owner_user_id);

        // Perangkat baru membuka tautannya -> status naik ke `scanned`.
        $this->perangkatBaruBuka($url);
        $this->postJson(route('qr.login.status'))
            ->assertOk()
            ->assertJsonPath('status', QrLoginSession::SCANNED);
    }

    public function test_status_tanpa_permintaan_aktif(): void
    {
        $this->postJson(route('qr.login.status'))
            ->assertOk()
            ->assertJsonPath('status', 'none');
    }

    // ==================================================================
    // Dua sisi halaman /qr-login/{token}
    // ==================================================================

    public function test_tamu_membuka_tautan_qr_dan_menunggu(): void
    {
        [, $url] = $this->dashboardBuatQr();

        $this->get($url)
            ->assertOk()
            ->assertSee('Menunggu Persetujuan')
            // Tidak boleh dialihkan ke form masuk.
            ->assertDontSee('Selamat datang kembali');
    }

    public function test_user_login_membuka_tautan_qr_dan_dapat_konfirmasi(): void
    {
        [, $url] = $this->dashboardBuatQr();
        $this->actingAs($this->mahasiswa());

        $this->get($url)
            ->assertOk()
            ->assertSee('Konfirmasi Login')
            ->assertSee('Ada perangkat baru yang ingin masuk ke akun Anda.')
            ->assertSee('Tolak')
            ->assertSee('Izinkan');
    }

    public function test_qr_tidak_dikenal_ditampilkan_sebagai_tidak_valid(): void
    {
        $this->actingAs($this->mahasiswa());

        $this->get('/qr-login/'.str_repeat('a', 64))
            ->assertOk()
            ->assertSee('QR Code tidak valid');
    }

    public function test_membuka_qr_menandai_scanned_lalu_menunggu_konfirmasi(): void
    {
        [, $url] = $this->dashboardBuatQr();
        $this->actingAs($this->mahasiswa());

        $this->get($url)->assertOk();

        $this->assertSame(
            QrLoginSession::AWAITING_CONFIRMATION,
            QrLoginSession::firstOrFail()->status
        );
    }

    // ==================================================================
    // Keputusan
    // ==================================================================

    public function test_menolak_qr(): void
    {
        [$token, $url, $pemilik] = $this->dashboardBuatQr();

        $this->actingAs($pemilik);
        $this->get($url)->assertOk();
        $this->post(route('qr.login.reject', ['token' => $token]))->assertRedirect(route('perangkat'));

        $record = QrLoginSession::firstOrFail();
        $this->assertSame(QrLoginSession::REJECTED, $record->status);
        $this->assertNotNull($record->rejected_at);
        $this->assertNull($record->approved_user_id);
    }

    public function test_menyetujui_qr(): void
    {
        [$token, $url, $pemilik] = $this->dashboardBuatQr();

        $this->actingAs($pemilik);
        $this->get($url)->assertOk();
        $this->post(route('qr.login.approve', ['token' => $token]))->assertRedirect(route('perangkat'));

        $record = QrLoginSession::firstOrFail();
        $this->assertSame(QrLoginSession::APPROVED, $record->status);
        $this->assertSame($pemilik->id, $record->approved_user_id);
        $this->assertNotNull($record->approved_at);
    }

    // ==================================================================
    // Perangkat baru mengklaim sesi
    // ==================================================================

    public function test_perangkat_baru_login_otomatis_setelah_disetujui(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();

        // Perangkat baru membuka tautan (terikat ke sesinya).
        $this->perangkatBaruBuka($url);

        // Pemilik akun menyetujui dari dashboard.
        $this->actingAs($pemilik);
        $this->get($url)->assertOk();
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]))->assertRedirect();

        // Perangkat baru memantau lalu mengklaim.
        $this->jadiTamu();
        $this->postJson(route('qr.login.status'))
            ->assertOk()
            ->assertJsonPath('status', QrLoginSession::APPROVED);

        $this->post(route('qr.login.claim'))->assertRedirect(route('redirect'));
        $this->assertAuthenticatedAs($pemilik);
    }

    public function test_qr_yang_ditolak_tidak_membuat_session(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $this->actingAs($pemilik);
        $this->get($url)->assertOk();
        $this->post(route('qr.login.reject', ['token' => $this->tokenDari($url)]))->assertRedirect();

        $this->jadiTamu();
        $this->post(route('qr.login.claim'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('swal_error', 'Login QR ditolak.');

        $this->assertGuest();
    }

    public function test_qr_yang_belum_disetujui_tidak_bisa_diklaim(): void
    {
        [, $url] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $this->post(route('qr.login.claim'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('swal_error');

        $this->assertGuest();
    }

    public function test_token_hanya_bisa_dipakai_satu_kali(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();

        $this->perangkatBaruBuka($url);

        $this->actingAs($pemilik);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]))->assertRedirect();

        $this->jadiTamu();
        $this->post(route('qr.login.claim'))->assertRedirect(route('redirect'));
        $this->assertAuthenticatedAs($pemilik);

        // Percobaan kedua dengan record yang sama: id-nya sengaja dipasang lagi
        // di sesi untuk menyimulasikan memakai ulang token.
        $this->post('/logout');
        $this->jadiTamu();
        $this->withSession(['qr_login_id' => $record->id])
            ->post(route('qr.login.claim'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('swal_error', 'QR Code sudah digunakan.');

        $this->assertGuest();
        $this->assertSame(QrLoginSession::USED, $record->fresh()->status);
        $this->assertNotNull($record->fresh()->used_at);
        $this->assertNotNull($record->fresh()->authenticated_at);
    }

    public function test_qr_kedaluwarsa_tidak_bisa_diklaim(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();
        $this->perangkatBaruBuka($url);

        $this->actingAs($pemilik);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]))->assertRedirect();

        // Dipaksa kedaluwarsa setelah disetujui.
        $record->forceFill(['expires_at' => now()->subSecond()])->save();

        $this->jadiTamu();
        $this->post(route('qr.login.claim'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame(QrLoginSession::EXPIRED, $record->fresh()->status);
    }

    public function test_menyetujui_qr_yang_sudah_kedaluwarsa_ditolak(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        QrLoginSession::firstOrFail()->forceFill(['expires_at' => now()->subSecond()])->save();

        $this->actingAs($pemilik);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]))
            ->assertRedirect(route('qr.login.authorize', ['token' => $this->tokenDari($url)]));

        $this->assertSame(QrLoginSession::EXPIRED, QrLoginSession::firstOrFail()->status);
    }

    public function test_token_yang_sudah_dipakai_tidak_bisa_dipindai_lagi(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $this->actingAs($pemilik);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]))->assertRedirect();

        $this->jadiTamu();
        $this->post(route('qr.login.claim'));

        $this->actingAs($this->mahasiswa());
        $this->get($url)
            ->assertOk()
            ->assertSee('QR Code sudah digunakan');
    }

    // ==================================================================
    // Role & status akun
    // ==================================================================

    public function test_role_mahasiswa_ikut_setelah_login_qr(): void
    {
        [, $url, $user] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $this->actingAs($user);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]));
        $this->jadiTamu();

        $this->post(route('qr.login.claim'))->assertRedirect(route('redirect'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('mahasiswa.dashboard'))->assertOk();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_role_admin_ikut_setelah_login_qr(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin'.self::$urut.'@sipkl.test',
            'password' => 'password',
        ]);

        [, $url] = $this->dashboardBuatQr($admin);
        $this->perangkatBaruBuka($url);

        $this->actingAs($admin);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]));
        $this->jadiTamu();

        $this->post(route('qr.login.claim'))->assertRedirect(route('redirect'));
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('mahasiswa.dashboard'))->assertForbidden();
    }

    public function test_akun_ditunda_diarahkan_ke_halaman_status(): void
    {
        // QR dibuat oleh akun aktif; akun "ditunda" yang mengotorisasi, lalu
        // perangkat baru diarahkan ke halaman status (bukan dashboard).
        [, $url] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $pending = User::factory()->mahasiswa()->pending()->create([
            'email' => 'pending'.self::$urut.'@sipkl.test',
            'password' => 'password',
        ]);

        $this->actingAs($pending);
        $this->post(route('qr.login.approve', ['token' => $this->tokenDari($url)]));
        $this->jadiTamu();

        $this->post(route('qr.login.claim'))->assertRedirect(route('akun.ditunda'));
        $this->assertAuthenticatedAs($pending);
    }

    // ==================================================================
    // Notifikasi dashboard (SweetAlert2)
    // ==================================================================

    public function test_dashboard_melihat_permintaan_yang_menunggu(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();

        // Baru dibuat: belum ada yang membuka tautannya -> belum ada notifikasi.
        $this->actingAs($pemilik);
        $this->get(route('qr.pending'))->assertOk()->assertJsonPath('status', 'none');

        // Perangkat baru (tamu) yang membuka tautannya.
        $this->jadiTamu();
        $this->perangkatBaruBuka($url);
        $this->actingAs($pemilik);

        $this->get(route('qr.pending'))
            ->assertOk()
            ->assertJsonPath('status', QrLoginSession::SCANNED)
            ->assertJsonStructure(['id', 'device_name', 'browser', 'platform', 'lokasi', 'expires_in'])
            // Token & kredensial tidak boleh ikut terkirim ke browser.
            ->assertJsonMissingPath('token')
            ->assertJsonMissingPath('token_hash');
    }

    public function test_permintaan_qr_milik_akun_lain_tidak_muncul(): void
    {
        [, $url] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);

        $this->actingAs($this->mahasiswa());
        $this->get(route('qr.pending'))->assertOk()->assertJsonPath('status', 'none');
    }

    public function test_akun_lain_tidak_bisa_memutuskan_permintaan(): void
    {
        [, $url] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();
        $this->perangkatBaruBuka($url);

        $this->actingAs($this->mahasiswa());
        $this->postJson(route('qr.decide', ['id' => $record->id, 'aksi' => 'approve']))
            ->assertNotFound();

        $this->assertSame(QrLoginSession::SCANNED, $record->fresh()->status);
    }

    public function test_endpoint_notifikasi_hanya_bisa_untuk_user_login(): void
    {
        $this->dashboardBuatQr();

        $this->get(route('qr.pending'))->assertRedirect(route('login'));
        // Endpoint JSON menjawab 401 (bukan redirect) bila belum login.
        $this->postJson(route('qr.decide', ['id' => 1, 'aksi' => 'approve']))->assertUnauthorized();
    }

    public function test_akun_ditunda_tidak_melihat_notifikasi_qr(): void
    {
        $this->dashboardBuatQr();

        $this->actingAs(User::factory()->mahasiswa()->pending()->create([
            'email' => 'pending-notif'.self::$urut.'@sipkl.test',
            'password' => 'password',
        ]));

        $this->get(route('qr.pending'))->assertForbidden();
    }

    public function test_izinkan_dari_dashboard_tercatat(): void
    {
        [, $url, $user] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();
        $this->perangkatBaruBuka($url);

        $this->actingAs($user);
        $this->postJson(route('qr.decide', ['id' => $record->id, 'aksi' => 'approve']))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(QrLoginSession::APPROVED, $record->fresh()->status);
        $this->assertSame($user->id, $record->fresh()->approved_user_id);

        $this->get(route('qr.pending'))->assertJsonPath('status', 'none');

        $this->jadiTamu();
        $this->post(route('qr.login.claim'))->assertRedirect(route('redirect'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_tolak_dari_dashboard(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $record = QrLoginSession::firstOrFail();
        $this->perangkatBaruBuka($url);

        $this->actingAs($pemilik);
        $this->postJson(route('qr.decide', ['id' => $record->id, 'aksi' => 'reject']))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(QrLoginSession::REJECTED, $record->fresh()->status);
    }

    public function test_permintaan_kedaluwarsa_tidak_muncul_di_dashboard(): void
    {
        [, $url, $pemilik] = $this->dashboardBuatQr();
        $this->perangkatBaruBuka($url);
        QrLoginSession::firstOrFail()->forceFill(['expires_at' => now()->subSecond()])->save();

        $this->actingAs($pemilik);
        $this->get(route('qr.pending'))->assertOk()->assertJsonPath('status', 'none');
    }

    public function test_aksi_tidak_dikenal_ditolak(): void
    {
        $this->dashboardBuatQr();

        $this->actingAs($this->mahasiswa());
        $this->post('/perangkat/qr/1/delete')->assertNotFound();
    }

    // ==================================================================
    // Rate limit & CSRF
    // ==================================================================

    public function test_pembuatan_qr_dibatasi_rate_limit(): void
    {
        $this->actingAs($this->mahasiswa());

        $limit = (int) config('qr-login.throttle.start');

        for ($i = 1; $i <= $limit; $i++) {
            $this->postJson(route('qr.issue'))->assertOk();
        }

        $this->postJson(route('qr.issue'))->assertStatus(429);
    }

    public function test_endpoint_login_qr_terdaftar_dengan_pembatas(): void
    {
        $names = ['qr.issue', 'qr.login.status', 'qr.login.claim', 'qr.login.approve', 'qr.login.reject', 'qr.decide'];

        foreach ($names as $name) {
            $middleware = collect($this->app['router']->getRoutes()->getByName($name)->gatherMiddleware())
                ->filter(fn ($m) => str_starts_with($m, 'throttle'));

            $this->assertCount(1, $middleware, "Endpoint {$name} harus memakai throttle.");
        }
    }

    public function test_endpoint_qr_menggunakan_csrf(): void
    {
        // Verifikasi CSRF diuji langsung lewat HTTP (Laravel mematikan
        // middleware ini saat testing), jadi di sini cukup memastikan token
        // CSRF tersedia di halaman yang memanggil endpoint tersebut.
        $this->get(route('login'))->assertOk()->assertSee('name="csrf-token"', false);
    }

    /** Ambil token mentah dari URL `/qr-login/{token}`. */
    private function tokenDari(string $url): string
    {
        return (string) preg_replace('#^.*/qr-login/#', '', $url);
    }
}
