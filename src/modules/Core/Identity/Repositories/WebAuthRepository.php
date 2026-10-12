<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Facades\Auth;

class WebAuthRepository implements WebAuthRepositoryInterface
{
    public function validate(string $email, string $password): bool
    {
        return Auth::validate(['email' => $email, 'password' => $password]);
    }

    public function attempt(string $email, string $password, bool $remember): ?UserEntity
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return null;
        }

        $user = Auth::user();

        return $user instanceof User ? UserEntity::fromModel($user) : null;
    }

    public function loginUsingUuid(string $uuid): ?UserEntity
    {
        Auth::loginUsingId($uuid);
        $user = Auth::user();

        return $user instanceof User ? UserEntity::fromModel($user) : null;
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function issueApiToken(UserEntity $user): ?string
    {
        $model = $user->uuid ? User::find($user->uuid) : null;
        if (!$model) {
            return null;
        }

        try {
            return Auth::guard('api')->login($model);
        } catch (\Throwable) {
            return null;
        }
    }
}
