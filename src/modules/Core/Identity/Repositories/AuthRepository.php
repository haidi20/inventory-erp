<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Entities\LoginEntity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthRepository
 *
 * Implementasi akses data autentikasi menggunakan Eloquent dan JWT.
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?User
    {
        return User::with(['roles.permissions'])
            ->whereRaw('LOWER(email) = ?', [Str::lower(Str::trim($email))])
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function attemptLogin(LoginEntity $entity): ?string
    {
        $credentials = [
            'email' => $entity->getEmail(),
            'password' => $entity->getPassword(),
        ];

        try {
            $token = Auth::guard('api')->attempt($credentials);
            return $token ?: null;
        } catch (\Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * @inheritDoc
     */
    public function refresh(): ?string
    {
        try {
            return Auth::guard('api')->refresh();
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?User
    {
        $user = Auth::guard('api')->user();
        return $user instanceof User ? $user : null;
    }

    /**
     * @inheritDoc
     */
    public function getTokenTTL(): int
    {
        return config('jwt.ttl', 60);
    }
}
