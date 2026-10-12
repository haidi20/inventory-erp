<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Entities\LoginEntity;

/**
 * Interface AuthRepositoryInterface
 *
 * Kontrak untuk akses data autentikasi.
 */
interface AuthRepositoryInterface
{
    /**
     * Mencari user berdasarkan email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Mencoba login dan mengembalikan token jika berhasil.
     *
     * @param LoginEntity $entity
     * @return string|null
     */
    public function attemptLogin(LoginEntity $entity): ?string;

    /**
     * Melakukan logout pengguna.
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Melakukan refresh token.
     *
     * @return string|null
     */
    public function refresh(): ?string;

    /**
     * Mendapatkan user yang sedang login.
     *
     * @return User|null
     */
    public function getUser(): ?User;

    /**
     * Mendapatkan TTL token dalam menit.
     *
     * @return int
     */
    public function getTokenTTL(): int;
}
