<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data User dan Role.
 */
interface UserRepositoryInterface
{
    /**
     * Mendapatkan daftar user dengan filter pencarian dan role (Paginasi).
     */
    public function paginate(UserEntity $entity): LengthAwarePaginator;

    /**
     * Mencari user berdasarkan UUID.
     */
    public function findByUuid(string $uuid): ?UserEntity;

    /**
     * Mencari user berdasarkan email secara case-insensitive.
     */
    public function findByEmail(string $email): ?UserEntity;
    public function findByLoginIdentifier(string $identifier): ?UserEntity;
    public function uuidExists(string $uuid): bool;
    public function updatePassword(string $uuid, string $password): void;
    public function emailExistsInCompany(string $email, string $companyUuid, ?string $ignoreUuid = null): bool;
    public function roleUuidsExist(array $roleUuids): bool;

    /**
     * Membuat atau memperbarui user (Upsert).
     */
    public function save(UserEntity $entity): UserEntity;

    /**
     * Menghapus user (Soft Delete).
     */
    public function delete(UserEntity $entity): void;

    /**
     * Mendapatkan data user untuk keperluan ekspor.
     */
    public function getForExport(UserEntity $entity): Collection;

    /**
     * Mendapatkan daftar role yang tersedia.
     */
    public function getAllRoles(): Collection;
    
    /**
     * Mendapatkan data summary user.
     */
    public function getSummaryData(?string $companyUuid = null): array;
}
