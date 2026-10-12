<?php

namespace Tests\Unit\Services\Auth;

use App\Modules\Core\Identity\Entities\LoginEntity;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Repositories\AuthRepositoryInterface;
use App\Modules\Core\Identity\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private $authRepositoryMock;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authRepositoryMock = Mockery::mock(AuthRepositoryInterface::class);
        $this->authService = new AuthService($this->authRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_returns_token_on_success()
    {
        $loginEntity = new LoginEntity('test@example.com', 'password123');
        $token = 'valid-token';
        $user = User::factory()->make();

        $this->authRepositoryMock->shouldReceive('attemptLogin')->once()->with($loginEntity)->andReturn($token);
        $this->authRepositoryMock->shouldReceive('getUser')->once()->andReturn($user);
        $this->authRepositoryMock->shouldReceive('getTokenTTL')->once()->andReturn(60);

        $result = $this->authService->login($loginEntity);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals($token, $result['access_token']);
        $this->assertEquals('bearer', $result['token_type']);
    }

    public function test_login_throws_validation_exception_on_failure()
    {
        $loginEntity = new LoginEntity('test@example.com', 'wrongpassword');
        $this->authRepositoryMock->shouldReceive('attemptLogin')->once()->with($loginEntity)->andReturn(null);

        $this->expectException(ValidationException::class);

        $this->authService->login($loginEntity);
    }

    public function test_logout_calls_repository()
    {
        $this->authRepositoryMock->shouldReceive('logout')->once();

        $this->authService->logout();
        $this->assertTrue(true);
    }

    public function test_refresh_returns_new_token()
    {
        $token = 'new-token';
        $user = User::factory()->make();

        $this->authRepositoryMock->shouldReceive('refresh')->once()->andReturn($token);
        $this->authRepositoryMock->shouldReceive('getUser')->once()->andReturn($user);
        $this->authRepositoryMock->shouldReceive('getTokenTTL')->once()->andReturn(60);

        $result = $this->authService->refresh();

        $this->assertEquals($token, $result['access_token']);
    }

    public function test_me_returns_user_data()
    {
        $user = User::factory()->make(['uuid' => 'user-auth-service-test']);
        $this->authRepositoryMock->shouldReceive('getUser')->once()->andReturn($user);

        $result = $this->authService->me();

        $this->assertIsArray($result);
        $this->assertEquals($user->uuid, $result['uuid']);
    }

    public function test_me_throws_validation_exception_if_no_user()
    {
        $this->authRepositoryMock->shouldReceive('getUser')->once()->andReturn(null);

        $this->expectException(ValidationException::class);

        $this->authService->me();
    }

    public function test_resolve_landing_url_returns_correct_url_based_on_role()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->andReturnUsing(fn (string $role): bool => $role === 'dokter');
        $user->shouldReceive('hasPermissionTo')->andReturnUsing(
            fn (string $permission): bool => $permission === 'transaksi.tugas_dokter.view'
        );
        
        $url = $this->authService->resolveLandingUrl($user);

        $this->assertEquals('/transaksi/tugas-dokter', $url);
    }
}
