<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UserRepository
 *
 * Implementasi repository untuk manajemen User dan hak akses.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginate(UserEntity $entity): LengthAwarePaginator
    {
        return $this->buildQuery($entity)
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);
    }

    /**
     * {@inheritDoc}
     */
    public function findByUuid(string $uuid): ?UserEntity
    {
        $model = User::query()
            ->with(['roles', 'employee'])
            ->where('uuid', $uuid)
            ->first();

        return $model ? UserEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?UserEntity
    {
        $model = User::query()
            ->with(['roles'])
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->first();

        return $model ? UserEntity::fromModel($model) : null;
    }

    public function findByLoginIdentifier(string $identifier): ?UserEntity
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }
        if (str_contains($identifier, '@')) {
            return $this->findByEmail($identifier);
        }
        $model = User::query()->where('username', $identifier)->first();
        if (! $model) {
            return $this->findByEmail($identifier);
        }

        return $model ? UserEntity::fromModel($model) : null;
    }

    public function updatePassword(string $uuid, string $password): void
    {
        User::query()->where('uuid', $uuid)->update(['password' => Hash::make($password)]);
    }

    public function uuidExists(string $uuid): bool
    {
        return User::query()->where('uuid', $uuid)->exists();
    }

    public function emailExistsInCompany(string $email, string $companyUuid, ?string $ignoreUuid = null): bool
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->where('company_uuid', $companyUuid)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    public function roleUuidsExist(array $roleUuids): bool
    {
        if ($roleUuids === []) {
            return false;
        }

        return Role::query()->whereIn('uuid', $roleUuids)->count() === count(array_unique($roleUuids));
    }

    /**
     * {@inheritDoc}
     */
    public function save(UserEntity $entity): UserEntity
    {
        DB::beginTransaction();
        try {
            $user = null;
            if ($entity->uuid) {
                $user = User::withTrashed()->where('uuid', $entity->uuid)->first();
            }

            $data = [
                'name' => trim($entity->name),
                'email' => strtolower(trim($entity->email)),
                'username' => filled($entity->username) ? trim($entity->username) : null,
            ];

            if (! empty($entity->password)) {
                $data['password'] = Hash::make($entity->password);
            }

            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update($data);
            } else {
                $data['uuid'] = $entity->uuid ?? (string) Str::uuid();
                $user = User::query()->create($data);
            }

            $this->syncOrganizationPivots($user, $entity);

            // Sync multiple roles
            $roleUuids = ! empty($entity->role_ids) ? $entity->role_ids : array_filter([$entity->role_uuid]);
            if (! empty($roleUuids)) {
                $roles = Role::whereIn('uuid', $roleUuids)->get();
                if ($roles->isNotEmpty()) {
                    $user->syncRoles($roles);
                }
            }

            if ($entity->employee_uuid) {
                Employee::query()->where('user_uuid', $user->uuid)->update(['user_uuid' => null]);
                Employee::query()->where('uuid', $entity->employee_uuid)->update(['user_uuid' => $user->uuid]);
            }

            DB::commit();

            return UserEntity::fromModel($user->load(['roles', 'employee']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(UserEntity $entity): void
    {
        $user = User::query()
            ->where('uuid', $entity->uuid)
            ->when(
                ! empty($entity->company_uuid),
                fn ($query) => $query->whereHas(
                    'companies',
                    fn ($company) => $company->where('companies.uuid', $entity->company_uuid)
                )
            )
            ->firstOrFail();

        if ($user->uuid === $entity->actor_uuid) {
            return;
        }

        DB::beginTransaction();
        try {
            // Suffix email with timestamp to avoid unique collision after soft delete
            $user->update([
                'email' => $user->email.'-del-'.time(),
            ]);

            $user->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getForExport(UserEntity $entity): Collection
    {
        return $this->buildQuery($entity)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllRoles(): Collection
    {
        return Role::query()
            ->whereNotIn('name', ['admin', 'administrator'])
            ->orderBy('label')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getSummaryData(?string $companyUuid = null): array
    {
        $total = User::query()
            ->when(! empty($companyUuid), fn ($query) => $query->whereHas(
                'companies',
                fn ($company) => $company->where('companies.uuid', $companyUuid)
            ))
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'administrator']))
            ->count();
        $dokter = User::query()
            ->when(! empty($companyUuid), fn ($query) => $query->whereHas(
                'companies',
                fn ($company) => $company->where('companies.uuid', $companyUuid)
            ))
            ->whereHas('roles', fn ($q) => $q->where('name', 'dokter'))
            ->count();

        return [
            'total' => $total,
            'dokter' => $dokter,
            'other' => $total - $dokter,
        ];
    }

    /**
     * Build base query with filters.
     */
    private function buildQuery(UserEntity $entity)
    {
        $query = User::query()
            ->with(['roles', 'employee'])
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'administrator']));

        if (! empty($entity->company_uuid)) {
            $query->whereHas(
                'companies',
                fn ($company) => $company->where('companies.uuid', $entity->company_uuid)
            );
        }

        if ($entity->search) {
            $query->where(function ($q) use ($entity) {
                $s = "%{$entity->search}%";
                if ($entity->search_column === 'name') {
                    $q->where('name', 'LIKE', $s);
                } elseif ($entity->search_column === 'email') {
                    $q->where('email', 'LIKE', $s);
                } else {
                    $q->where('name', 'LIKE', $s)
                        ->orWhere('email', 'LIKE', $s);
                }
            });
        }

        if ($entity->role_filter) {
            $query->whereHas('roles', function ($q) use ($entity) {
                if (Str::isUuid($entity->role_filter)) {
                    $q->where('roles.uuid', $entity->role_filter);
                } else {
                    $q->where('roles.name', $entity->role_filter);
                }
            });
        }

        $sortableColumns = ['name', 'email'];
        $sortBy = in_array($entity->sort_by, $sortableColumns, true) ? $entity->sort_by : 'name';
        $sortDirection = $entity->sort_direction === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }

    private function resolveCompanyUuid(UserEntity $entity): ?string
    {
        if (! empty($entity->company_uuid)) {
            return $entity->company_uuid;
        }

        if (empty($entity->branch_uuid)) {
            return null;
        }

        return Branch::query()
            ->where('uuid', $entity->branch_uuid)
            ->value('company_uuid');
    }

    private function syncOrganizationPivots(User $user, UserEntity $entity): void
    {
        $companyUuid = $this->resolveCompanyUuid($entity);
        if (empty($companyUuid)) {
            return;
        }

        UserCompany::query()
            ->where('user_uuid', $user->uuid)
            ->update(['is_default' => false]);

        UserCompany::query()->updateOrCreate(
            ['user_uuid' => $user->uuid, 'company_uuid' => $companyUuid],
            ['is_default' => true]
        );

        if (empty($entity->branch_uuid)) {
            return;
        }

        UserBranch::query()
            ->where('user_uuid', $user->uuid)
            ->where('company_uuid', $companyUuid)
            ->update(['is_default' => false]);

        UserBranch::query()->updateOrCreate(
            [
                'user_uuid' => $user->uuid,
                'company_uuid' => $companyUuid,
                'branch_uuid' => $entity->branch_uuid,
            ],
            ['is_default' => true]
        );
    }
}
