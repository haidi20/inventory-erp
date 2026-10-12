<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRegisterFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
        config(['inertia.testing.ensure_pages_exist' => false]);
    }

    public function test_login_and_register_screens_render_vue_components(): void
    {
        $this->get('/login')->assertOk()->assertInertia(
            fn ($page) => $page->component('Core/Identity/presentations/views/Login')
        );

        $this->get('/register')->assertOk()->assertInertia(
            fn ($page) => $page
                ->component('Core/Identity/presentations/views/Register')
                ->has('timezones')
        );
    }

    public function test_registration_creates_company_timezone_branch_and_owner(): void
    {
        $response = $this->post('/register', $this->validPayload());

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $company = Company::query()->where('name', 'Klinik Alur Uji')->firstOrFail();
        $branch = Branch::query()->where('company_uuid', $company->uuid)->firstOrFail();
        $user = User::query()->where('email', 'owner.flow@example.com')->firstOrFail();

        $this->assertSame('Asia/Makassar', $company->timezone);
        $this->assertSame('Klinik Alur Uji', $branch->name);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->hasRole('owner'));
        $this->assertTrue($user->can('dashboard.view'));

        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('user_branches', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid' => $branch->uuid,
            'is_default' => true,
        ]);
    }

    public function test_registered_user_can_login_with_username_and_email(): void
    {
        $this->post('/register', $this->validPayload());
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'username' => 'owner_flow',
            'password' => 'password123',
        ]);
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'owner.flow@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticated();
    }

    public function test_login_rejects_invalid_password(): void
    {
        $this->post('/register', $this->validPayload());
        $this->post('/logout');

        $this->post('/login', [
            'email' => 'owner.flow@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_timezone(): void
    {
        $payload = $this->validPayload();
        $payload['timezone'] = 'Invalid/Timezone';

        $response = $this->from('/register')->post('/register', $payload);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('timezone');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'owner.flow@example.com']);
        $this->assertDatabaseMissing('companies', ['name' => 'Klinik Alur Uji']);
    }

    private function validPayload(): array
    {
        return [
            'company_name' => 'Klinik Alur Uji',
            'timezone' => 'Asia/Makassar',
            'name' => 'Pemilik Alur Uji',
            'email' => 'owner.flow@example.com',
            'username' => 'owner_flow',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }
}
