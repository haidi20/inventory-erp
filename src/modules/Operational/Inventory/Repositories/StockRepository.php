<?php

namespace App\Modules\Operational\Inventory\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockReportResultEntity;

class StockRepository implements StockRepositoryInterface
{
    public function getStockBalanceReport(StockFilterEntity $filter): StockReportResultEntity
    {
        $query = DB::table('item_balances')->whereNull('deleted_at');

        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        if ($filter->location_uuid) {
            $query->where('location_uuid', $filter->location_uuid);
        }

        $total = $query->count();
        $data = $query->offset(($filter->page - 1) * $filter->per_page)
                      ->limit($filter->per_page)
                      ->get()
                      ->toArray();

        return new StockReportResultEntity(
            items: $data,
            total: $total,
            per_page: $filter->per_page,
            current_page: $filter->page
        );
    }

    public function saveMovement(StockMovementEntity $entity): StockMovementEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'uuid' => $entity->uuid ?? (string) \Illuminate\Support\Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'product_uuid' => $entity->product_uuid,
                'location_uuid' => $entity->location_uuid,
                'qty' => $entity->qty,
                'type' => $entity->type,
                'reference_type' => $entity->ref_type?->value,
                'reference_uuid' => $entity->ref_uuid,
                'price' => $entity->cost_price,
                'user_uuid' => $entity->created_by,
                'created_at' => time(),
                'updated_at' => time(),
                'created_by' => $entity->created_by,
            ];

            DB::table('stock_movements')->insert($data);
            DB::commit();
            return $entity;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
