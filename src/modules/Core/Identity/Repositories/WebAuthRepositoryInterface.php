<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Identity\Entities\UserEntity;

interface WebAuthRepositoryInterface
{
    public function validate(string $email, string $password): bool;

    public function attempt(string $email, string $password, bool $remember): ?UserEntity;

    public function loginUsingUuid(string $uuid): ?UserEntity;

    public function logout(): void;

    public function issueApiToken(UserEntity $user): ?string;
}
