<?php

namespace Tests\Unit\Repository;

use App\Modules\Core\Identity\Entities\LoginEntity;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Repositories\AuthRepository;
use Tests\Traits\ManualDatabaseSetup;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use ManualDatabaseSetup;

    private AuthRepository $repository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthRepository();

        // Kolom legacy users.branch_uuid sudah di-drop (multi-org via pivot
        // user_branches). Login JWT hanya butuh email + password.
        $this->user = User::factory()->create([
            'email' => 'repo@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_attempt_login_returns_token_for_valid_credentials()
    {
        $entity = new LoginEntity('repo@example.com', 'password123');
        $token = $this->repository->attemptLogin($entity);
        $this->assertIsString($token);
    }

    public function test_attempt_login_returns_null_for_invalid_credentials()
    {
        $entity = new LoginEntity('repo@example.com', 'wrongpassword');
        $result = $this->repository->attemptLogin($entity);
        $this->assertNull($result);
    }
}
