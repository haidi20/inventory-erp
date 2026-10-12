<?php

namespace Tests\Unit\Service;

use App\Modules\Core\Identity\Entities\LoginEntity;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Repositories\AuthRepositoryInterface;
use App\Modules\Core\Identity\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private $repository;
    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(AuthRepositoryInterface::class);
        $this->service = new AuthService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_throws_validation_exception_on_failure()
    {
        $entity = new LoginEntity('wrong@example.com', 'pass');
        $this->repository->shouldReceive('attemptLogin')->with($entity)->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->service->login($entity);
    }

    public function test_login_returns_token_array_on_success()
    {
        $entity = new LoginEntity('test@example.com', 'password');
        $token = 'dummy.jwt.token';
        $user = new User(['id' => 1, 'email' => 'test@example.com']);

        $this->repository->shouldReceive('attemptLogin')->with($entity)->andReturn($token);
        $this->repository->shouldReceive('getTokenTTL')->andReturn(60);
        $this->repository->shouldReceive('getUser')->andReturn($user);

        $result = $this->service->login($entity);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals($token, $result['access_token']);
        $this->assertEquals('bearer', $result['token_type']);
        $this->assertEquals(3600, $result['expires_in']); // 60 * 60
    }

    public function test_me_throws_validation_exception_if_no_user()
    {
        $this->repository->shouldReceive('getUser')->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->service->me();
    }
}
