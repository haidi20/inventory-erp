<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupTypeListResultEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use App\Modules\Operational\Master\Entities\LookupValueListResultEntity;
use App\Modules\Operational\Master\Models\LookupType;
use App\Modules\Operational\Master\Models\LookupValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * LookupRepository
 *
 * Implementasi repository untuk manajemen data referensi (Lookup).
 * Mengelola tipe lookup dan nilai-nilainya untuk standarisasi pilihan sistem.
 */
class LookupRepository implements LookupRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getTypes(): LookupTypeListResultEntity
    {
        $items = LookupType::query()
            ->orderBy('code', 'ASC')
            ->get()
            ->map(fn (LookupType $type): LookupTypeEntity => LookupTypeEntity::fromModel($type))
            ->all();

        return new LookupTypeListResultEntity($items, count($items), count($items), 1);
    }

    /**
     * {@inheritDoc}
     */
    public function getValuesByType(LookupTypeEntity $entity): LookupValueListResultEntity
    {
        $items = LookupValue::query()
            ->whereHas('lookupSet', function ($query) use ($entity) {
                $query->where('code', trim((string) $entity->code));
                if (! empty($entity->company_uuid)) {
                    $query->where('company_uuid', $entity->company_uuid);
                }
            })
            ->orderBy('order_priority', 'ASC')
            ->orderBy('code', 'ASC')
            ->get()
            ->map(fn (LookupValue $value): LookupValueEntity => LookupValueEntity::fromModel($value))
            ->all();

        return new LookupValueListResultEntity($items, count($items), count($items), 1);
    }

    /**
     * {@inheritDoc}
     */
    public function saveType(LookupTypeEntity $entity): LookupTypeEntity
    {
        DB::beginTransaction();
        try {
            $model = LookupType::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'code' => trim($entity->code),
                    'name' => trim($entity->name),
                    'description' => $entity->description ? trim($entity->description) : null,
                    'company_uuid' => $entity->company_uuid,
                    'is_active' => $entity->is_active ?? true,
                ]
            );

            DB::commit();

            return LookupTypeEntity::fromModel($model->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function saveValue(LookupValueEntity $entity): LookupValueEntity
    {
        DB::beginTransaction();
        try {
            $model = LookupValue::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'lookup_set_uuid' => $entity->lookup_type_uuid ?? $entity->lookup_type_uuid,
                    'code' => trim($entity->code),
                    'display_name' => trim($entity->value),
                    'value' => trim($entity->value),
                    'description' => $entity->description ? trim($entity->description) : null,
                    'order_priority' => $entity->order_priority,
                    'company_uuid' => $entity->company_uuid,
                    'is_active' => $entity->is_active ?? true,
                    'active_flag' => ($entity->is_active ?? true) ? 'Y' : 'N',
                ]
            );

            DB::commit();

            return LookupValueEntity::fromModel($model->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteValue(string $uuid): void
    {
        DB::beginTransaction();
        try {
            LookupValue::query()->where('uuid', trim($uuid))->firstOrFail()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteType(string $uuid): void
    {
        DB::beginTransaction();
        try {
            LookupType::query()->where('uuid', trim($uuid))->firstOrFail()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeByCode(LookupTypeEntity $entity): ?LookupTypeEntity
    {
        $model = LookupType::query()
            ->where('code', trim((string) $entity->code))
            ->when(! empty($entity->company_uuid), fn ($query) => $query->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? LookupTypeEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueByUuid(LookupValueEntity $entity): ?LookupValueEntity
    {
        $model = LookupValue::query()
            ->where('uuid', trim((string) $entity->uuid))
            ->first();

        return $model ? LookupValueEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueByCode(LookupValueEntity $entity): ?LookupValueEntity
    {
        $model = LookupValue::query()
            ->where('lookup_set_uuid', $entity->lookup_type_uuid ?? $entity->lookup_type_uuid)
            ->where('code', trim((string) $entity->code))
            ->first();

        return $model ? LookupValueEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypesPageData(LookupTypeEntity $entity): LookupTypeListResultEntity
    {
        $query = LookupType::query()
            ->when(! empty($entity->company_uuid), fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
            ->orderBy('code', 'ASC');

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['code', 'name', 'description'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return new LookupTypeListResultEntity(
            items: array_map(fn ($item): LookupTypeEntity => LookupTypeEntity::fromModel($item), $paginator->items()),
            total: $paginator->total(),
            per_page: $paginator->perPage(),
            current_page: $paginator->currentPage(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getValuesPageData(LookupValueEntity $entity): LookupValueListResultEntity
    {
        $query = LookupValue::query()
            ->where('lookup_set_uuid', $entity->lookup_type_uuid ?? $entity->lookup_type_uuid)
            ->orderBy('order_priority', 'ASC')
            ->orderBy('code', 'ASC');

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['code', 'value', 'description'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(value) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return new LookupValueListResultEntity(
            items: array_map(fn ($item): LookupValueEntity => LookupValueEntity::fromModel($item), $paginator->items()),
            total: $paginator->total(),
            per_page: $paginator->perPage(),
            current_page: $paginator->currentPage(),
        );
    }
}
