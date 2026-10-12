<?php

namespace Tests\Feature\Repositories\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Entities\LoginEntity;
use App\Modules\Core\Identity\Repositories\AuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuthRepository $authRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authRepository = new AuthRepository();
    }

    public function test_find_by_email_returns_user_with_roles_and_permissions()
    {
        $email = 'test@example.com';
        $user = User::factory()->create([
            'email' => $email,
        ]);

        $foundUser = $this->authRepository->findByEmail($email);

        $this->assertNotNull($foundUser);
        $this->assertEquals(strtolower($email), $foundUser->email);
        $this->assertTrue($foundUser->relationLoaded('roles'));
    }

    public function test_attempt_login_returns_token_on_success()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $loginEntity = new LoginEntity($user->email, $password);

        $token = $this->authRepository->attemptLogin($loginEntity);

        $this->assertNotNull($token);
        $this->assertIsString($token);
    }

    public function test_attempt_login_returns_null_on_failure()
    {
        $loginEntity = new LoginEntity('nonexistent@example.com', 'wrongpassword');

        $token = $this->authRepository->attemptLogin($loginEntity);

        $this->assertNull($token);
    }

    public function test_logout_successfully_logs_out_user()
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        $this->assertNotNull(Auth::guard('api')->user());

        $this->authRepository->logout();

        $this->assertNull(Auth::guard('api')->user());
    }

    public function test_refresh_returns_new_token()
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        $newToken = $this->authRepository->refresh();

        $this->assertNotNull($newToken);
        $this->assertNotEquals($token, $newToken);
    }

    public function test_get_user_returns_currently_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::guard('api')->login($user);

        $authenticatedUser = $this->authRepository->getUser();

        $this->assertNotNull($authenticatedUser);
        $this->assertEquals($user->uuid, $authenticatedUser->uuid);
    }

    public function test_get_token_ttl_returns_integer()
    {
        $ttl = $this->authRepository->getTokenTTL();

        $this->assertIsInt($ttl);
        $this->assertEquals(config('jwt.ttl', 60), $ttl);
    }
}
