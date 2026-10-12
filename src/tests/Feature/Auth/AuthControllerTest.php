<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        config(['inertia.testing.ensure_pages_exist' => false]);

        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);

        $role = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        // Setup common user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->user->assignRole($role);

        UserCompany::create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid' => $branch->uuid,
            'is_default' => true,
        ]);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertOk()->assertInertia(
            fn ($page) => $page->component('Core/Identity/presentations/views/Login')
        );
    }

    public function test_login_with_remember_issues_recaller_cookie_and_persists_token()
    {
        $this->user->forceFill(['remember_token' => null])->save();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertNotNull($this->user->fresh()->remember_token);
    }

    public function test_login_without_remember_does_not_issue_recaller_cookie()
    {
        $this->user->forceFill(['remember_token' => null])->save();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
        $this->assertNull($this->user->fresh()->remember_token);
    }

    public function test_remember_me_accepts_string_truthy_value_from_form()
    {
        $this->user->forceFill(['remember_token' => null])->save();

        // Checkbox HTML mengirim nilai string ("1"), bukan boolean —
        // $request->boolean('remember') harus tetap mengenalinya.
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertNotNull($this->user->fresh()->remember_token);
    }

    public function test_recaller_cookie_reauthenticates_after_session_lost()
    {
        $this->user->forceFill(['remember_token' => null])->save();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        // Ambil cookie recaller dari respons login untuk tahu namanya.
        $recallerName = Auth::guard('web')->getRecallerName();
        $this->assertNotNull($response->getCookie($recallerName), 'Login dengan remember harus mengeluarkan cookie recaller.');

        // Simulasi sesi hilang (browser ditutup / sesi expired):
        // store sesi "array" di test persist antar-request, jadi harus di-flush;
        // guard juga di-reset agar tidak memegang user lama di memori.
        session()->flush();
        Auth::guard('web')->forgetUser();
        $this->assertGuest();

        // Kirim cookie recaller seperti browser: payload id|token|password
        // (test client yang mengenkripsi, sama seperti cookie asli).
        $fresh = $this->user->fresh();
        $recaller = implode('|', [$fresh->getAuthIdentifier(), $fresh->remember_token, $fresh->password]);

        $this->withCookie($recallerName, $recaller)->get('/');

        $this->assertAuthenticatedAs($fresh);
    }

    public function test_invalid_recaller_cookie_does_not_authenticate()
    {
        $recallerName = Auth::guard('web')->getRecallerName();

        $this->withCookie($recallerName, 'garbage-not-a-recaller')->get('/');

        $this->assertGuest();
    }

    public function test_logout_clears_recaller_cookie_and_cycles_token()
    {
        $this->user->forceFill(['remember_token' => null])->save();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $recallerName = Auth::guard('web')->getRecallerName();
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $tokenAfterLogin = $this->user->fresh()->remember_token;
        $this->assertNotNull($tokenAfterLogin);

        // Kirim cookie recaller seperti browser nyata (cookie jar tidak persist
        // antar-request di feature test), supaya logout meng-expire-nya.
        $fresh = $this->user->fresh();
        $recaller = implode('|', [$fresh->getAuthIdentifier(), $fresh->remember_token, $fresh->password]);

        $response = $this->withCookie($recallerName, $recaller)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertCookieExpired($recallerName);
        $this->assertNotSame($tokenAfterLogin, $this->user->fresh()->remember_token);
    }

    public function test_change_password_updates_password_and_allows_login_with_new_password()
    {
        $this->actingAs($this->user);

        $response = $this->post('/change-password', [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('cp_success', 'Password berhasil diubah.');
        $this->assertTrue(Hash::check('new-password-123', $this->user->fresh()->password));

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'new-password-123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_change_password_validates_confirmation_and_minimum_length()
    {
        $this->actingAs($this->user);

        $response = $this->from('/')->post('/change-password', [
            'password' => '12345',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password123', $this->user->fresh()->password));
    }

    public function test_change_password_requires_authentication()
    {
        $response = $this->post('/change-password', [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_register_screen_renders_vue_component()
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->component('Core/Identity/presentations/views/Register')
                    ->has('timezones')
            );
    }

    public function test_register_creates_company_timezone_branch_and_owner()
    {
        $response = $this->post('/register', [
            'company_name' => 'Klinik Vue Baru',
            'timezone' => 'Asia/Makassar',
            'name' => 'Pemilik Klinik',
            'email' => 'owner@example.com',
            'username' => 'ownerklinik',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $company = Company::query()->where('name', 'Klinik Vue Baru')->first();
        $user = User::query()->where('email', 'owner@example.com')->first();

        $this->assertNotNull($company);
        $this->assertSame('Asia/Makassar', $company->timezone);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('owner'));
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('branches', [
            'company_uuid' => $company->uuid,
            'name' => 'Klinik Vue Baru',
        ]);
        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('user_branches', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        // Pengecekan redirect ke dashboard setelah login
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_login()
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        // Redirect back dengan error
        $response->assertSessionHas('error');
    }

    public function test_logout_works_properly()
    {
        $this->actingAs($this->user);

        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_pre_check_route_returns_json()
    {
        $response = $this->postJson('/login/pre-check', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'roles' => [
                    ['name' => 'Admin'],
                ],
            ]);
    }
}
