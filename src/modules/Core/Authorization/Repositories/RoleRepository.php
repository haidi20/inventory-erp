<?php

namespace App\Modules\Core\Authorization\Repositories;

use App\Modules\Core\Authorization\Entities\RoleEntity;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * RoleRepository
 * 
 * Implementasi repository untuk manajemen Role dan hak akses (RBAC).
 * Menangani pemberian wewenang kepada user melalui profil role yang terukur.
 */
class RoleRepository implements RoleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getRoles(RoleEntity $entity): array
    {
        $query = Role::query()->where('deleted_at', 0);

        if (!empty($entity->search)) {
            $s = strtolower($entity->search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$s}%"])
                  ->orWhereRaw("LOWER(label) LIKE ?", ["%{$s}%"]);
            });
        }

        $paginator = $query->orderBy('label', 'ASC')
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn ($role) => RoleEntity::fromModel($role), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findById(string $uuid): ?RoleEntity
    {
        $role = Role::query()
            ->with(['permissions' => fn($q) => $q->where('deleted_at', 0)])
            ->where('uuid', $uuid)
            ->where('deleted_at', 0)
            ->first();

        return $role ? RoleEntity::fromModel($role) : null;
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?RoleEntity
    {
        $role = Role::query()
            ->with(['permissions' => fn($q) => $q->where('deleted_at', 0)])
            ->where('name', $name)
            ->where('deleted_at', 0)
            ->first();

        return $role ? RoleEntity::fromModel($role) : null;
    }

    /**
     * @inheritDoc
     */
    public function store(RoleEntity $entity): RoleEntity
    {
        DB::beginTransaction();
        try {
            $role = Role::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => trim($entity->name),
                'label' => trim($entity->label),
                'created_by' => $entity->actor_uuid,
            ]);

            $this->syncPermissions($role->uuid, $entity->permission_ids, $entity->actor_uuid);

            DB::commit();
            return RoleEntity::fromModel($role->load('permissions'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function update(RoleEntity $entity): RoleEntity
    {
        DB::beginTransaction();
        try {
            $role = Role::query()->findOrFail(trim($entity->uuid));
            $role->update([
                'name' => trim($entity->name),
                'label' => trim($entity->label),
                'updated_by' => $entity->actor_uuid,
            ]);

            $this->syncPermissions($role->uuid, $entity->permission_ids, $entity->actor_uuid);

            DB::commit();
            return RoleEntity::fromModel($role->load('permissions'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid, string $actorUuid): void
    {
        $role = Role::query()->where('uuid', $uuid)->firstOrFail();
        $role->deleted_by = $actorUuid;
        $role->save();
        $role->delete();
    }

    /**
     * @inheritDoc
     */
    public function syncPermissions(string $roleUuid, array $permissionIds, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            // Simple sync logic
            DB::table('role_has_permissions')->where('role_uuid', $roleUuid)->delete();

            foreach ($permissionIds as $pId) {
                if (empty($pId)) continue;
                DB::table('role_has_permissions')->insert([
                    'role_uuid' => $roleUuid,
                    'permission_uuid' => $pId,
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllPermissions(): array
    {
        return Permission::query()->where('deleted_at', 0)->orderBy('group', 'ASC')->orderBy('name', 'ASC')->get()->toArray();
    }
}
