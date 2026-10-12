<?php

namespace App\Modules\Core\Authorization\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Core\Authorization\Entities\PermissionEntity;
use App\Modules\Core\Authorization\Entities\PermissionFilterEntity;
use App\Modules\Core\Authorization\Entities\PermissionListResultEntity;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function getPermissions(PermissionFilterEntity $filter): PermissionListResultEntity
    {
        $query = DB::table('permissions')->where('deleted_at', 0);

        if (!empty($filter->search)) {
            $query->where(function($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter->search . '%')
                  ->orWhere('label', 'like', '%' . $filter->search . '%');
            });
        }

        $total = $query->count();
        $data = $query->offset(($filter->page - 1) * $filter->per_page)
                      ->limit($filter->per_page)
                      ->orderBy('name', 'asc')
                      ->get()
                      ->map(fn($item) => $this->mapToObjectEntity($item))
                      ->toArray();

        return new PermissionListResultEntity(
            items: $data,
            total: $total,
            per_page: $filter->per_page,
            current_page: $filter->page
        );
    }

    public function findById(string $uuid): ?PermissionEntity
    {
        $item = DB::table('permissions')
            ->where('uuid', $uuid)
            ->where('deleted_at', 0)
            ->first();
            
        return $item ? $this->mapToObjectEntity($item) : null;
    }

    public function store(PermissionEntity $entity): bool
    {
        DB::beginTransaction();
        try {
            $data = [
                'uuid' => $entity->uuid,
                'name' => $entity->name,
                'label' => $entity->label,
                'guard_name' => $entity->guard_name,
                'created_at' => time(),
                'updated_at' => time(),
                'created_by' => $entity->created_by,
            ];
            DB::commit();
            return DB::table('permissions')->insert($data);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $uuid, PermissionEntity $entity): bool
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $entity->name,
                'label' => $entity->label,
                'updated_at' => time(),
                'updated_by' => $entity->updated_by,
            ];
            DB::commit();
            return DB::table('permissions')->where('uuid', $uuid)->update($data) >= 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(string $uuid): bool
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return DB::table('permissions')->where('uuid', $uuid)->update(['deleted_at' => time()]) > 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function mapToObjectEntity(object $item): PermissionEntity
    {
        return new PermissionEntity(
            uuid: $item->uuid,
            name: $item->name,
            label: $item->label,
            guard_name: $item->guard_name,
            created_by: $item->created_by,
            updated_by: $item->updated_by
        );
    }
}
