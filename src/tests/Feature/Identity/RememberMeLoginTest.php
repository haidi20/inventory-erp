<?php

namespace Tests\Feature\Identity;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test: fitur "Ingat saya" (remember me) pada login web.
 *
 * Alur yang diuji (end-to-end, bukan mock):
 *   Login.vue (checkbox) -> WebAuthController::login ($request->boolean('remember'))
 *   -> WebAuthService::attemptLogin -> WebAuthRepository::attempt
 *   -> Auth::attempt($credentials, $remember) [guard session 'web']
 *
 * Kunci desain: driver session 'array' (phpunit.xml) membuat SETIAP request
 * berdiri di sesi baru. Satu-satunya cara user ter-login lagi pada request
 * berikutnya adalah cookie remember_web_* -- persis skenario "sesi habis,
 * akun masih diingat".
 */
class RememberMeLoginTest extends TestCase
{
    use RefreshDatabase; // Jangan tambah migrate:fresh manual (jebakan VACUUM SQLite, lihat test_failures_audit.md RC-1/RC-5)

    private User $user;

    private string $recallerName;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->user = User::factory()->create([
            'email' => 'remember@example.com',
            'password' => 'password123', // otomatis di-hash via cast 'hashed'
        ]);

        // Nama cookie recaller dihitung sama seperti implementasi Laravel 12:
        // 'remember_web_' . sha1(static::class) pada SessionGuard.
        $this->recallerName = 'remember_web_' . sha1(SessionGuard::class);
    }

    /**
     * Simulasi browser yang kehilangan sesi (tab ditutup / sesi expired).
     *
     * Singleton 'session.store' menyimpan Store berisi login_web_* yang
     * di-cache di IoC container, sehingga forgetDrivers() saja tidak cukup
     * (binding lama tetap dipakai request berikutnya). Bindings harus
     * dibuang agar Store benar-benar baru; guard juga di-reset.
     */
    private function simulateLostSession(): void
    {
        $this->app->forgetInstance('session.store');
        $this->app->forgetInstance('session');
        $this->app['auth']->forgetGuards(); // buang cache guard (anti false positive)
    }

    /**
     * Ambil nilai mentah cookie recaller dari header response.
     */
    private function recallerCookieValue($response): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $this->recallerName) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    public function test_login_with_remember_sets_recaller_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticatedAs($this->user, 'web');
        $this->assertNotNull(
            $this->recallerCookieValue($response),
            "Cookie remember ({$this->recallerName}) tidak di-set saat remember=true"
        );
    }

    public function test_login_without_remember_does_not_set_recaller_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            // 'remember' sengaja tidak dikirim => false
        ]);

        $this->assertAuthenticatedAs($this->user, 'web');
        $this->assertNull(
            $this->recallerCookieValue($response),
            'Cookie remember di-set padahal checkbox tidak dicentang'
        );
    }

    public function test_remembered_user_is_logged_in_again_on_fresh_session(): void
    {
        // 1) Login dengan remember=true, simpan cookie recaller.
        $login = $this->post('/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);
        $recaller = $this->recallerCookieValue($login);
        $this->assertNotNull($recaller, 'Cookie remember tidak ada setelah login');

        // 2) Simulasikan sesi habis/tab ditutup: buang Store session & cache
        //    guard sehingga jalur satu-satunya adalah cookie recaller.
        $this->simulateLostSession();

        // 3) Sesi baru, kirim HANYA cookie recaller -- kondisi kembali
        //    setelah sesi habis.
        $this->withUnencryptedCookies([$this->recallerName => $recaller]);
        $this->get('/login');

        $this->assertAuthenticatedAs($this->user, 'web');
    }

    public function test_fresh_session_without_recaller_stays_guest(): void
    {
        $this->post('/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->simulateLostSession();

        // Sesi baru tanpa cookie recaller -> tetap guest (remember tidak bocor).
        $this->get('/login');

        $this->assertGuest('web');
    }
}
