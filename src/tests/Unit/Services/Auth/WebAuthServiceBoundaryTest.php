<?php

namespace Tests\Unit\Services\Auth;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Repositories\WebAuthRepositoryInterface;
use App\Modules\Core\Identity\Services\WebAuthService;
use Mockery;
use Tests\TestCase;

class WebAuthServiceBoundaryTest extends TestCase
{
    private $repository;
    private WebAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(WebAuthRepositoryInterface::class);
        $this->service = new WebAuthService(
            $this->repository,
            Mockery::mock(\App\Modules\Core\Identity\Repositories\OwnerRegistrationRepositoryInterface::class),
            Mockery::mock(\App\Modules\Core\Organization\Services\CompanyService::class),
            Mockery::mock(\App\Modules\Core\Organization\Services\BranchService::class),
            Mockery::mock(\App\Modules\Core\Identity\Services\UserService::class),
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeUserEntity(): UserEntity
    {
        return new UserEntity(
            uuid: 'test-uuid-001',
            name: 'Test User',
            email: 'test@example.com',
            username: 'testuser',
            branch_uuid: 'branch-001',
            company_uuid: 'company-001',
        );
    }

    public function test_attempt_login_returns_user_entity_not_model()
    {
        $entity = $this->makeUserEntity();
        $this->repository->shouldReceive('attempt')
            ->once()
            ->with('test@example.com', 'password', true)
            ->andReturn($entity);

        $result = $this->service->attemptLogin($entity, 'password', true);

        $this->assertInstanceOf(UserEntity::class, $result);
        $this->assertNotInstanceOf(\App\Modules\Core\Identity\Models\User::class, $result);
        $this->assertSame('test-uuid-001', $result->uuid);
    }

    public function test_attempt_login_returns_null_on_failure()
    {
        $entity = $this->makeUserEntity();
        $this->repository->shouldReceive('attempt')
            ->once()
            ->with('test@example.com', 'wrong-password', false)
            ->andReturn(null);

        $result = $this->service->attemptLogin($entity, 'wrong-password', false);

        $this->assertNull($result);
    }

    public function test_login_registered_user_returns_user_entity_not_model()
    {
        $entity = $this->makeUserEntity();
        $this->repository->shouldReceive('loginUsingUuid')
            ->once()
            ->with('test-uuid-001')
            ->andReturn($entity);

        $result = $this->service->loginRegisteredUser('test-uuid-001');

        $this->assertInstanceOf(UserEntity::class, $result);
        $this->assertNotInstanceOf(\App\Modules\Core\Identity\Models\User::class, $result);
    }

    public function test_issue_api_token_accepts_user_entity()
    {
        $entity = $this->makeUserEntity();
        $this->repository->shouldReceive('issueApiToken')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof UserEntity))
            ->andReturn('jwt.token.here');

        $result = $this->service->issueApiToken($entity);

        $this->assertIsString($result);
        $this->assertSame('jwt.token.here', $result);
    }

    public function test_issue_api_token_returns_null_when_repository_fails()
    {
        $entity = $this->makeUserEntity();
        $this->repository->shouldReceive('issueApiToken')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof UserEntity))
            ->andReturn(null);

        $result = $this->service->issueApiToken($entity);

        $this->assertNull($result);
    }
}