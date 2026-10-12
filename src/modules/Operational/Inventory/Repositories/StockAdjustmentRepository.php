<?php

namespace App\Modules\Operational\Inventory\Repositories;

use Illuminate\Support\Str;
use App\Modules\Operational\Inventory\Models\StockAdjustment;
use App\Modules\Operational\Inventory\Models\StockAdjustmentItem;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentItemEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentListResultEntity;
use App\Modules\Operational\Sales\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;

class StockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    public function getAdjustments(StockAdjustmentFilterEntity $filter): StockAdjustmentListResultEntity
    {
        $query = StockAdjustment::query();

        if ($filter->company_uuid) {
            $query->where('company_uuid', $filter->company_uuid);
        }

        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        if ($filter->location_uuid) {
            $query->where('location_uuid', $filter->location_uuid);
        }

        $total = $query->count();
        $data = $query->offset(($filter->page - 1) * $filter->per_page)
                      ->limit($filter->per_page)
                      ->orderBy('date', 'desc')
                      ->get()
                      ->toArray();

        return new StockAdjustmentListResultEntity(
            data: $data,
            total: $total,
            per_page: $filter->per_page,
            current_page: $filter->page
        );
    }

    public function getAdjustmentByUuid(string $uuid, string $branchUuid, string $companyUuid): ?object
    {
        $query = StockAdjustment::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid);
        return $this->hydrateAdjustment($query->first());
    }

    public function storeAdjustment(StockAdjustmentEntity $entity): ?object
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }

        DB::beginTransaction();
        try {
        $data = [
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid,
            'location_uuid' => $entity->location_uuid,
            'adjustment_no' => $entity->adjustment_no,
            'reference_no' => $entity->reference_no,
            'date' => $entity->date,
            'status' => $entity->status,
            'notes' => $entity->notes,
            'account_uuid' => $entity->account_uuid,
            'is_value_adjustment' => $entity->is_value_adjustment,
            'updated_by' => $entity->actor_uuid,
        ];

        StockAdjustment::updateOrCreate(['uuid' => $entity->uuid], $data);

        if (!empty($entity->items)) {
            StockAdjustmentItem::where('adjustment_uuid', $entity->uuid)->delete();
            foreach ($entity->items as $item) {
                StockAdjustmentItem::create([
                    'uuid' => $item->uuid ?? (string) Str::uuid(),
                    'adjustment_uuid' => $entity->uuid,
                    'item_uuid' => $item->product_uuid,
                    'unit_uuid' => $item->measurement_uuid,
                    'price' => $item->price,
                    'book_qty' => $item->book_qty,
                    'actual_qty' => $item->actual_qty,
                    'adjustment_qty' => $item->adjustment_qty,
                    'reason' => $item->reason,
                    'notes' => $item->notes,
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'created_by' => $entity->actor_uuid,
                    'updated_by' => $entity->actor_uuid,
                ]);
            }
        }

            DB::commit();
            return $this->hydrateAdjustment(
                StockAdjustment::query()->where('uuid', $entity->uuid)->first()
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAdjustment(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = StockAdjustment::query()
                ->where('uuid', $uuid)
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->first();

            if (!$adjustment) {
                DB::commit();
                return false;
            }

            $now = time();
            StockAdjustmentItem::where('adjustment_uuid', $uuid)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $actorUuid,
                    'deleted_by' => $actorUuid,
                ]);
            StockAdjustment::query()
                ->where('uuid', $uuid)
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $actorUuid,
                    'deleted_by' => $actorUuid,
                ]);
            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function hydrateAdjustment(?object $adjustment): ?StockAdjustmentEntity
    {
        if (!$adjustment) {
            return null;
        }

        $entity = new StockAdjustmentEntity(
            uuid: $adjustment->uuid,
            branch_uuid: $adjustment->branch_uuid,
            company_uuid: $adjustment->company_uuid,
            location_uuid: $adjustment->location_uuid,
            adjustment_no: $adjustment->adjustment_no,
            reference_no: $adjustment->reference_no ?? null,
            date: (int) $adjustment->date,
            status: $adjustment->status instanceof TransactionStatus
                ? $adjustment->status
                : (TransactionStatus::tryFrom((string) $adjustment->status) ?? TransactionStatus::DRAFT),
            account_uuid: $adjustment->account_uuid,
            notes: $adjustment->notes,
            is_value_adjustment: (bool) ($adjustment->is_value_adjustment ?? false),
            actor_uuid: $adjustment->created_by ?? null,
        );

        $entity->items = StockAdjustmentItem::query()
            ->where('adjustment_uuid', $adjustment->uuid)
            ->get()
            ->map(fn (object $item) => new StockAdjustmentItemEntity(
                uuid: $item->uuid,
                product_uuid: $item->item_uuid,
                measurement_uuid: $item->unit_uuid,
                adjustment_qty: (float) $item->adjustment_qty,
                price: (float) $item->price,
                book_qty: (float) $item->book_qty,
                actual_qty: (float) $item->actual_qty,
                reason: $item->reason,
                notes: $item->notes,
            ))
            ->all();

        return $entity;
    }
}
