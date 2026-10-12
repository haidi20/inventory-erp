<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Entities\PriceLevelEntity;
use App\Modules\Operational\Master\Models\PriceLevel;
use Illuminate\Support\Str;

class PriceLevelRepository implements PriceLevelRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(PriceLevelEntity $entity): array
    {
        $query = PriceLevel::query()
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"]);
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => PriceLevelEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid, ?string $companyUuid = null): ?PriceLevelEntity
    {
        $query = PriceLevel::query()
            ->where('uuid', trim($uuid))
            ->where('deleted_at', 0);

        if ($companyUuid) {
            $query->where('company_uuid', $companyUuid);
        }

        $model = $query->first();
        return $model ? PriceLevelEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceLevelEntity $entity): PriceLevelEntity
    {
        DB::beginTransaction();
        try {
            $priceLevel = PriceLevel::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'company_uuid' => $entity->company_uuid,
                    'branch_uuid' => $entity->branch_uuid,
                    'code' => $entity->code,
                    'name' => $entity->name,
                    'description' => $entity->description,
                    'is_active' => $entity->is_active ?? true,
                ]
            );

            DB::commit();
            return PriceLevelEntity::fromModel($priceLevel->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(PriceLevelEntity $entity): void
    {
        DB::beginTransaction();
        try {
            PriceLevel::query()
                ->where('uuid', trim($entity->uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getActivePriceLevels(?string $companyUuid = null): array
    {
        $query = PriceLevel::query()
            ->where('deleted_at', 0)
            ->where('is_active', true)
            ->orderBy('name', 'ASC');

        if ($companyUuid) {
            $query->where('company_uuid', $companyUuid);
        }

        $items = $query->get();
        return $items->map(fn($m) => PriceLevelEntity::fromModel($m))->toArray();
    }
}
