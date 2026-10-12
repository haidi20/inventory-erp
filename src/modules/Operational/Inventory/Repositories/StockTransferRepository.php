<?php

namespace App\Modules\Operational\Inventory\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Inventory\Entities\StockTransferEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferListResultEntity;

class StockTransferRepository implements StockTransferRepositoryInterface
{
    public function getPageData(StockTransferFilterEntity $filter): StockTransferListResultEntity
    {
        $perPage = $filter->per_page ?? 10;
        $query = DB::table('stock_transfers')->where('deleted_at', 0);

        if ($filter->company_uuid) {
            $query->where('company_uuid', $filter->company_uuid);
        }

        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        $total = $query->count();
        $data = $query->offset(($filter->page - 1) * $perPage)
                      ->limit($perPage)
                      ->orderBy('date', 'desc')
                      ->get();

        return new StockTransferListResultEntity(
            items: $data->map(fn($m) => StockTransferEntity::fromModel($m))->toArray(),
            total: $total,
            per_page: $perPage,
            current_page: $filter->page
        );
    }

    public function getByUuid(string $uuid, string $branchUuid, string $companyUuid): ?StockTransferEntity
    {
        $transfer = DB::table('stock_transfers')
            ->where('uuid', $uuid)
            ->where('company_uuid', $companyUuid)
            ->where('branch_uuid', $branchUuid)
            ->where('deleted_at', 0)
            ->first();

        if ($transfer) {
            $entity = StockTransferEntity::fromModel($transfer);
            $items = DB::table('stock_transfer_items')
                ->where('transfer_uuid', $uuid)
                ->where('deleted_at', 0)
                ->get();
            $entity->items = $items->toArray();
            return $entity;
        }

        return null;
    }

    public function store(StockTransferEntity $entity): ?StockTransferEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'uuid' => $entity->uuid,
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'source_location_uuid' => $entity->source_location_uuid,
                'destination_location_uuid' => $entity->destination_location_uuid,
                'no_pag' => $entity->document_no,
                'date' => $entity->date,
                'status' => $entity->status->value,
                'notes' => $entity->notes,
                'updated_at' => time(),
                'updated_by' => $entity->actor_uuid,
            ];

            if (empty($entity->uuid)) {
                $data['created_at'] = time();
                $data['created_by'] = $entity->actor_uuid;
            }

            DB::table('stock_transfers')->updateOrInsert(['uuid' => $entity->uuid], $data);

            if (!empty($entity->items)) {
                DB::table('stock_transfer_items')->where('transfer_uuid', $entity->uuid)->delete();
                foreach ($entity->items as $item) {
                DB::table('stock_transfer_items')->insert([
                    'uuid' => is_array($item)
                        ? ($item['uuid'] ?? (string) \Illuminate\Support\Str::uuid())
                        : ($item->uuid ?? (string) \Illuminate\Support\Str::uuid()),
                    'transfer_uuid' => $entity->uuid,
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'product_uuid' => is_array($item) ? $item['product_uuid'] : $item->product_uuid,
                    'unit_uuid' => is_array($item) ? $item['unit_uuid'] : $item->unit_uuid,
                    'qty' => is_array($item) ? $item['qty'] : $item->qty,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'created_by' => $entity->actor_uuid,
                    'updated_by' => $entity->actor_uuid,
                ]);
                }
            }

            DB::commit();
            return $this->getByUuid($entity->uuid, $entity->branch_uuid, $entity->company_uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            $exists = DB::table('stock_transfers')
                ->where('uuid', $uuid)
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->where('deleted_at', 0)
                ->first();

            if (!$exists) {
                DB::commit();
                return false;
            }

            $now = time();
            DB::table('stock_transfer_items')
                ->where('transfer_uuid', $uuid)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $actorUuid,
                ]);
            DB::table('stock_transfers')
                ->where('uuid', $uuid)
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $actorUuid,
                ]);
            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getNextSequence(string $branchUuid, string $companyUuid, string $prefix, string $period): int
    {
        $count = DB::table('stock_transfers')
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->where('no_pag', 'like', $prefix . '-' . substr($period, 2, 2) . '-' . substr($period, 5, 2) . '-%')
            ->where('deleted_at', 0)
            ->count();
        return $count + 1;
    }
}
