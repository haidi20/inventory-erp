<?php

namespace App\Modules\Operational\Master\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Support\Str;

/**
 * UnitRepository
 * 
 * Implementasi repository untuk manajemen data Satuan (Unit).
 * Mengatur logika akses data, pencarian, dan persistence untuk model Unit.
 */
class UnitRepository implements UnitRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function query(): Builder
    {
        return Unit::query();
    }

    /**
     * @inheritDoc
     */
    public function getPageData(UnitEntity $entity): array
    {
        $sortableColumns = ['code', 'name'];
        $sortBy = in_array($entity->sort_by, $sortableColumns, true) ? $entity->sort_by : 'code';
        $sortDirection = strtolower((string) $entity->sort_direction) === 'desc' ? 'desc' : 'asc';

        $query = Unit::query()
            ->where('deleted_at', 0)
            ->when(!empty($entity->branch_uuid), fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
            ->when(!empty($entity->company_uuid), fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
            ->orderBy($sortBy, $sortDirection);

        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['code', 'name'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("LOWER(code) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(name) LIKE ?", ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => UnitEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(UnitEntity $entity): ?UnitEntity
    {
        $query = Unit::query()->where('uuid', $entity->uuid);

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }
        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        $unit = $query->first();

        // Go fallback logic: try without branch/company scoping if not found and uuid is provided
        if (!$unit && !empty($entity->uuid)) {
            $unit = Unit::query()->where('uuid', $entity->uuid)->first();
        }

        return $unit ? UnitEntity::fromModel($unit) : null;
    }

    /**
     * @inheritDoc
     */
    public function getByCode(UnitEntity $entity): ?UnitEntity
    {
        $unit = Unit::query()
            ->whereRaw("LOWER(code) = ?", [strtolower($entity->code)])
            ->where('branch_uuid', $entity->branch_uuid)
            ->where('company_uuid', $entity->company_uuid)
            ->first();

        return $unit ? UnitEntity::fromModel($unit) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(UnitEntity $entity): UnitEntity
    {
        DB::beginTransaction();
        try {
            $unit = Unit::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'code' => $entity->code,
                    'name' => $entity->name,
                    'symbol' => $entity->symbol,
                    'description' => $entity->description,
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'is_active' => $entity->is_active,
                    'is_primary' => $entity->is_primary,
                    'is_base_global' => $entity->is_base_global,
                ]
            );

            DB::commit();
            return UnitEntity::fromModel($unit->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(UnitEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Unit::query()
                ->where('uuid', $entity->uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
