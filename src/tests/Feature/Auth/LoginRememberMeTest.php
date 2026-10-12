<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Test fitur "Remember Me" pada login.
 *
 * Memastikan alur remember akun bekerja end-to-end:
 * 1. Login dengan remember=true → remember_token tersimpan di DB + cookie remember_* di-set.
 * 2. Login tanpa remember → token tidak berubah, cookie tidak di-set.
 * 3. Cookie recaller dari login sebelumnya bisa meng-autentikasi ulang user
 *    tanpa sesi aktif (simulasi sesi kadaluarsa) — inti dari "bisa remember akun".
 * 4. Logout → remember_token di-cycle (cookie lama mati) + cookie recaller dihapus.
 *
 * Catatan: user dibuat langsung via factory (bukan SuperadminSeeder) karena
 * seeder tersebut masih meng-insert kolom legacy branch_uuid/company_uuid
 * yang sudah tidak ada di tabel users.
 */
class LoginRememberMeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $permission = Permission::create(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->user = User::factory()->create([
            'email' => 'remember@klinik.local',
            'password' => Hash::make('superadmin'),
            'is_active' => true,
        ]);
        $this->user->assignRole($role);
    }

    /**
     * Nama cookie recaller diambil langsung dari guard agar
     * selalu mengikuti konvensi framework: remember_<guard>_<sha1(SessionGuard)>.
     */
    private function recallerCookieName(): string
    {
        return Auth::guard()->getRecallerName();
    }

    private function loginWithRemember(): TestResponse
    {
        return $this->post('/login', [
            'email' => 'remember@klinik.local',
            'password' => 'superadmin',
            'remember' => '1',
        ]);
    }

    public function test_login_with_remember_saves_token_and_sets_cookie()
    {
        $response = $this->loginWithRemember();

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->user);

        $this->user->refresh();
        $this->assertNotEmpty(
            $this->user->remember_token,
            'remember_token harus terisi di DB setelah login dengan remember=true.'
        );

        $cookie = $response->getCookie($this->recallerCookieName());
        $this->assertNotNull(
            $cookie,
            'Cookie remember_* harus di-set saat login dengan remember=true.'
        );

        // Format value ter-dekripsi: <uuid>|<remember_token>|<hash password>
        $parts = explode('|', (string) $cookie->getValue());
        $this->assertCount(3, $parts, 'Cookie recaller harus berformat uuid|token|hash.');
        $this->assertSame($this->user->uuid, $parts[0], 'Bagian pertama cookie harus berisi uuid user.');
        $this->assertSame($this->user->remember_token, $parts[1], 'Bagian kedua cookie harus sama dengan remember_token di DB.');
    }

    public function test_login_without_remember_does_not_set_cookie_or_change_token()
    {
        $tokenBefore = $this->user->remember_token;

        $response = $this->post('/login', [
            'email' => 'remember@klinik.local',
            'password' => 'superadmin',
            // tanpa 'remember'
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->user);

        $this->user->refresh();
        $this->assertSame(
            $tokenBefore,
            $this->user->remember_token,
            'remember_token tidak boleh berubah saat login tanpa remember.'
        );
        $this->assertNull(
            $response->getCookie($this->recallerCookieName()),
            'Cookie remember_* tidak boleh di-set saat login tanpa remember.'
        );
    }

    public function test_remember_cookie_reauthenticates_user_after_session_lost()
    {
        $login = $this->loginWithRemember();

        $cookie = $login->getCookie($this->recallerCookieName());
        $this->assertNotNull($cookie, 'Login dengan remember harus menghasilkan cookie recaller.');

        $tokenAtLogin = $this->user->refresh()->remember_token;

        // Simulasi sesi hilang (browser dibuka lagi): kosongkan session store,
        // lalu request berikutnya hanya membawa cookie recaller.
        $this->app['session.store']->flush();

        // POST /logout dipakai sebagai probe karena butuh middleware auth:
        // jika recall via cookie bekerja, user ter-autentikasi dan logout
        // benar-benar dijalankan → remember_token di-cycle di DB.
        $response = $this->withCookies([
            $this->recallerCookieName() => $cookie->getValue(),
        ])->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->user->refresh();
        $this->assertNotSame(
            $tokenAtLogin,
            $this->user->remember_token,
            'Logout via recall cookie harus menjalankan cycle remember_token — '
            .'jika token tidak berubah, artinya recall cookie gagal.'
        );
    }

    public function test_logout_cycles_token_and_clears_remember_cookie()
    {
        $login = $this->loginWithRemember();

        $cookie = $login->getCookie($this->recallerCookieName());
        $this->assertNotNull($cookie);

        $tokenAtLogin = $this->user->refresh()->remember_token;

        $response = $this->withCookies([
            $this->recallerCookieName() => $cookie->getValue(),
        ])->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->user->refresh();
        $this->assertNotSame(
            $tokenAtLogin,
            $this->user->remember_token,
            'Logout harus meng-cycle (mengganti) remember_token agar cookie lama tidak berlaku lagi.'
        );

        $rawCookie = $response->getCookie($this->recallerCookieName(), false);
        $this->assertNotNull($rawCookie, 'Logout harus mengirim perintah penghapusan cookie recaller.');
        $this->assertTrue(
            $rawCookie->getExpiresTime() < time() || $rawCookie->getValue() === null,
            'Cookie recaller harus kedaluwarsa / dikosongkan saat logout.'
        );
    }
}
