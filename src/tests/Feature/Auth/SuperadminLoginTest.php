<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Database\Seeders\V1\SuperadminSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * End-to-end test login Super Admin.
 *
 * Menjalankan SuperadminSeeder asli (V1) sehingga akun
 * username=superadmin / password=superadmin benar-benar di-seed,
 * lalu menguji login via username dan via email + pre-check roles.
 */
class SuperadminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        // Seed minimal: permission dashboard + branch aktif (prasyarat SuperadminSeeder)
        Permission::create(['name' => 'dashboard.view', 'guard_name' => 'web']);

        $company = Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Klinik Superadmin Test',
            'is_active' => true,
        ]);

        Branch::create([
            'uuid' => Str::uuid(),
            'short_name' => 'SUP',
            'name' => 'Cabang Superadmin',
            'is_active' => true,
            'company_uuid' => $company->uuid,
        ]);

        // Jalankan seeder superadmin asli
        $this->seed(SuperadminSeeder::class);
    }

    public function test_superadmin_user_is_created_by_seeder()
    {
        $user = User::where('email', 'superadmin@klinik.local')->first();

        $this->assertNotNull($user, 'Superadmin user harus dibuat oleh SuperadminSeeder.');
        $this->assertSame('superadmin', $user->username);
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue($user->hasRole('superadmin'));
    }

    public function test_superadmin_can_login_using_username()
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'superadmin',
        ]);

        $this->assertAuthenticatedAs(
            User::where('email', 'superadmin@klinik.local')->first()
        );
        $response->assertRedirect('/');
    }

    public function test_superadmin_can_login_using_email()
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@klinik.local',
            'password' => 'superadmin',
        ]);

        $this->assertAuthenticatedAs(
            User::where('email', 'superadmin@klinik.local')->first()
        );
        $response->assertRedirect('/');
    }

    public function test_superadmin_pre_check_returns_superadmin_role()
    {
        $response = $this->postJson('/login/pre-check', [
            'email' => 'superadmin',
            'password' => 'superadmin',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'roles' => [
                    ['name' => 'superadmin', 'label' => 'Super Administrator'],
                ],
            ]);
    }

    public function test_superadmin_cannot_login_with_wrong_password()
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_superadmin_login_redirects_to_root_as_landing()
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'superadmin',
        ]);

        $response->assertRedirect('/');
    }
}
