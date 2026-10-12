<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Models\PriceLevel;
use Illuminate\Support\Str;

use App\Modules\Operational\Master\Entities\ProductPriceEntity;

/**
 * Class ItemPriceRepository
 *
 * Implementasi repository harga barang dan level harga menggunakan Eloquent.
 */
class ItemPriceRepository implements ItemPriceRepositoryInterface
{
    public function listForExport(array $filter): array
    {
        $query = Product::query()->with('prices');

        if (!empty($filter['company_uuid'])) {
            $query->where('company_uuid', $filter['company_uuid']);
        }

        return $query->get()->map(fn (Product $product) => [
            'item_code' => $product->code,
            'item_name' => $product->name,
            'item_base_price_buy' => (float) ($product->base_price_buy ?? 0),
            'item_base_price_sell' => (float) ($product->base_price_sell ?? 0),
            'prices' => $product->prices,
        ])->all();
    }

    public function getActivePriceLevels(?string $companyUuid = null): array
    {
        return PriceLevel::query()
            ->where('is_active', true)
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->orderBy('name')
            ->get(['uuid', 'name'])
            ->toArray();
    }

    public function getByItemCode(string $code): array|object|null
    {
        $product = Product::query()->where('code', $code)->first();

        return $product ? ['uuid' => $product->uuid] : null;
    }

    public function updateBasePrices(string $itemUuid, float $buyPrice, float $sellPrice, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            $product = Product::where('uuid', $itemUuid)->first();
            if (!$product) {
                DB::commit();
                return false;
            }

            DB::commit();
            return (bool) $product->update([
                'base_price_buy' => $buyPrice,
                'base_price_sell' => $sellPrice,
                'updated_by' => $actorUuid,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function upsertPrice(ProductPriceEntity $entity): ?ProductPriceEntity
    {
        DB::beginTransaction();
        try {
            $model = ProductPrice::updateOrCreate(
                [
                    'item_uuid'       => $entity->item_uuid,
                    'price_level_uuid' => $entity->price_level_uuid,
                    'branch_uuid'     => $entity->branch_uuid,
                    'company_uuid'    => $entity->company_uuid,
                ],
                [
                    'uuid'       => $entity->uuid ?? (string) Str::uuid(),
                    'price'      => $entity->price,
                    'is_active'  => $entity->is_active,
                    'started_at' => $entity->start_date,
                ]
            );

            DB::commit();
            return ProductPriceEntity::fromModel($model);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function findPrice(string $itemUuid, string $unitUuid, string $priceLevelUuid, string $branchUuid, ?string $companyUuid = null): ?ProductPriceEntity
    {
        $query = ProductPrice::where('item_uuid', $itemUuid)
            ->where('price_level_uuid', $priceLevelUuid)
            ->where('branch_uuid', $branchUuid);

        if ($companyUuid) {
            $query->where('company_uuid', $companyUuid);
        }

        $model = $query->first();
        return $model ? ProductPriceEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function updateBasePrice(string $itemUuid, float $price): bool
    {
        DB::beginTransaction();
        try {
            $product = Product::where('uuid', $itemUuid)->first();
            if (!$product) {
                DB::commit();
                return false;
            }

            DB::commit();
            return (bool) $product->update(['base_price_buy' => $price]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
