<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepositoryInterface;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentEntity;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use Illuminate\Support\Str;
use InvalidArgumentException;

use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentFilterEntity;
use App\Modules\Operational\Sales\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;

class StockBalanceService
{
    private StockRepositoryInterface $stockRepo;
    private StockAdjustmentRepositoryInterface $adjRepo;

    public function __construct(StockRepositoryInterface $stockRepo, StockAdjustmentRepositoryInterface $adjRepo)
    {
        $this->stockRepo = $stockRepo;
        $this->adjRepo = $adjRepo;
    }

    public function GetBalances(StockFilterEntity $filter)
    {
        return $this->stockRepo->getStockBalanceReport($filter);
    }

    public function ListAdjustments(StockAdjustmentFilterEntity $filter)
    {
        return $this->adjRepo->getAdjustments($filter);
    }

    public function saveMovement(StockMovementEntity $entity): StockMovementEntity
    {
        return $this->stockRepo->saveMovement($entity);
    }

    public function CreateAdjustment(StockAdjustmentEntity $entity): StockAdjustmentEntity
    {
        if (empty($entity->location_uuid)) {
            throw new InvalidArgumentException("gudang wajib dipilih");
        }
        if (empty($entity->items)) {
            throw new InvalidArgumentException("minimal satu item harus diisi");
        }

        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }
        
        $entity->adjustment_no = $entity->adjustment_no ?? 'ADJ-' . strtoupper(Str::random(8));
        $entity->date = $entity->date ?: time();

        return $this->adjRepo->storeAdjustment($entity);
    }

    public function PostAdjustment(StockAdjustmentEntity $entity): StockAdjustmentEntity
    {
        $adj = $this->adjRepo->getAdjustmentByUuid($entity->uuid, $entity->branch_uuid, $entity->company_uuid);
        if (!$adj) {
            throw new \Exception("data penyesuaian tidak ditemukan");
        }
        if ($adj->status?->value === 'posted') {
            return $adj;
        }

        $entity->branch_uuid = $adj->branch_uuid;
        $entity->company_uuid = $adj->company_uuid;
        $entity->location_uuid = $adj->location_uuid;
        $entity->adjustment_no = $adj->adjustment_no;
        $entity->date = (int) $adj->date;
        $entity->account_uuid = $adj->account_uuid;
        $entity->notes = $adj->notes;

        DB::beginTransaction();
        try {
            foreach ($adj->items ?? [] as $item) {
                $qty = $item->adjustment_qty;
                $movementType = $qty >= 0 ? 'IN' : 'OUT';

                $this->stockRepo->saveMovement(new StockMovementEntity(
                    branch_uuid: $adj->branch_uuid,
                    company_uuid: $adj->company_uuid,
                    product_uuid: $item->product_uuid,
                    location_uuid: $adj->location_uuid,
                    date: (int) $adj->date,
                    type: $movementType,
                    ref_type: StockMovementReferenceType::ADJUSTMENT,
                    ref_uuid: $adj->uuid,
                    unit_uuid: $item->measurement_uuid,
                    qty: abs($qty),
                    created_by: $entity->actor_uuid
                ));
            }

            $entity->status = TransactionStatus::POSTED;
            $posted = $this->adjRepo->storeAdjustment($entity);
            DB::commit();
            return $posted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
