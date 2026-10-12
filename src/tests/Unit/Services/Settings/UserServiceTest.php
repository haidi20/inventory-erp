<?php

namespace Tests\Unit\Services\Settings;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Repositories\UserRepositoryInterface;
use App\Modules\Core\Identity\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    protected UserService $service;
    protected UserRepositoryInterface $repository;
    protected Branch $branch;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create([
            'short_name' => 'KDM',
            'company_uuid' => $this->company->uuid,
        ]);
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->service = new UserService($this->repository);
    }

    public function test_store_user_calls_repository(): void
    {
        $role = Role::create(['uuid' => 'role-uuid', 'name' => 'admin', 'label' => 'Admin']);

        $entity = UserEntity::fromArray([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'role_ids' => [$role->uuid],
        ]);

        $this->repository->expects($this->once())
            ->method('roleUuidsExist')
            ->with([$role->uuid])
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (UserEntity $saved) => $saved->email === $entity->email))
            ->willReturn($entity);

        $result = $this->service->save($entity);
        $this->assertSame('test@example.com', $result->email);
    }

    public function test_store_user_fails_validation(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->save(UserEntity::fromArray([]));
    }
}
