<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Models\ItemConversion;
use App\Modules\Operational\Master\Models\ItemUom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Modules\Operational\Master\Entities\ItemUomEntity;

/**
 * Class ItemConversionRepository
 *
 * Implementasi repository konversi satuan barang (UOM) menggunakan Eloquent.
 * Diselaraskan dengan skema database aktual (uom_code, to_qty, conversion_ratio).
 */
class ItemConversionRepository implements ItemConversionRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function save(ItemConversionEntity $entity): ?ItemConversionEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $entity->uuid ?? (string) Str::uuid();

            $model = ItemConversion::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'item_uuid' => $entity->item_uuid ?? null,
                    'from_unit_uuid' => $entity->from_unit_uuid,
                    'to_unit_uuid' => $entity->to_unit_uuid,
                    'conversion_ratio' => $entity->conv_ratio,
                    'is_active' => $entity->is_active ?? true,
                ]
            );

            DB::commit();
            return ItemConversionEntity::fromModel($model->load(['item', 'fromUnit', 'toUnit']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(ItemConversionEntity $entity): void
    {
        DB::beginTransaction();
        try {
            ItemConversion::where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
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
    public function getByUnits(string $fromUnit, string $toUnit, string $branchUuid, string $companyUuid, ?string $itemUuid = null): ?ItemConversionEntity
    {
        $query = ItemConversion::where('from_unit_uuid', $fromUnit)
            ->where('to_unit_uuid', $toUnit)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid);

        if ($itemUuid) {
            $query->where('item_uuid', $itemUuid);
        }

        $model = $query->with(['item', 'fromUnit', 'toUnit'])->first();
        return $model ? ItemConversionEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function getPageData(ItemConversionEntity $entity): array
    {
        $query = ItemConversion::query()
            ->with(['item', 'fromUnit', 'toUnit'])
            ->leftJoin('items', 'item_conversions.item_uuid', '=', 'items.uuid')
            ->leftJoin('units as u1', 'item_conversions.from_unit_uuid', '=', 'u1.uuid')
            ->leftJoin('units as u2', 'item_conversions.to_unit_uuid', '=', 'u2.uuid')
            ->select('item_conversions.*');

        if (!empty($entity->branch_uuid)) {
            $query->where('item_conversions.branch_uuid', $entity->branch_uuid);
        }

        if (!empty($entity->company_uuid)) {
            $query->where('item_conversions.company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->search)) {
            $like = '%' . Str::lower($entity->search) . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(items.name) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(u1.name) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(u2.name) LIKE ?', [$like]);
            });
        }

        $sortBy = $entity->sort_by ?? 'updated_at';
        if ($sortBy === 'updated_at') $sortBy = 'item_conversions.updated_at';
        
        $sortDir = $entity->sort_dir ?? 'desc';

        $paginator = $query->orderBy($sortBy, $sortDir)
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => ItemConversionEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function saveUom(array $data): ?ItemUomEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $data['uuid'] ?? (string) Str::uuid();

            $uom = ItemUom::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'item_uuid' => $data['item_uuid'] ?? null,
                    'branch_uuid' => $data['branch_uuid'] ?? null,
                    'company_uuid' => $data['company_uuid'] ?? null,
                    'uom_code' => $data['uom_code'] ?? 'UOM',
                    'to_qty' => $data['to_qty'] ?? 1.0,
                    'barcode' => $data['barcode'] ?? null,
                    'is_base_uom' => $data['is_base_uom'] ?? false,
                ]
            );
            DB::commit();
            return ItemUomEntity::fromModel($uom);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getUomsByItem(string $itemUuid, string $branchUuid, string $companyUuid): array
    {
        return ItemUom::where('item_uuid', $itemUuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getUomByLegacyID(int $legacyId): ?ItemUomEntity
    {
        $uom = ItemUom::where('id', $legacyId)->first();

        return $uom ? ItemUomEntity::fromModel($uom) : null;
    }
}
