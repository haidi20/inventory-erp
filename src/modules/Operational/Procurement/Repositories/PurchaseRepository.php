<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseItemEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseListResultEntity;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function save(PurchaseRecordEntity $entity): PurchaseRecordEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $entity->uuid ?? (string) Str::uuid();

            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'supplier_uuid' => $entity->supplier_uuid,
                'purchase_order_uuid' => $entity->purchase_order_uuid,
                'invoice_no' => $entity->document_no,
                'purchase_date' => $entity->date,
                'status' => $entity->status->value,
                'total' => $entity->total_amount,
                'updated_at' => time(),
            ];

            if (empty($entity->uuid)) {
                $data['created_at'] = time();
                $data['created_by'] = $entity->actor_uuid;
            }

            $purchase = Purchase::updateOrCreate(['uuid' => $uuid], $data);

            $purchase->items()->delete();
            foreach ($entity->items as $item) {
                $itemData = $item instanceof PurchaseItemEntity
                    ? [
                        'uuid' => $item->uuid,
                        'product_uuid' => $item->product_uuid,
                        'qty' => $item->qty,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'unit_uuid' => $item->unit_uuid,
                    ]
                    : $item;

                $purchase->items()->create([
                    'uuid' => $itemData['uuid'] ?? (string) Str::uuid(),
                    'branch_uuid' => $purchase->branch_uuid,
                    'company_uuid' => $purchase->company_uuid,
                    'product_uuid' => $itemData['product_uuid'] ?? null,
                    'qty' => $itemData['qty'] ?? 0,
                    'price' => $itemData['price'] ?? 0,
                    'subtotal' => $itemData['subtotal'] ?? 0,
                    'unit_uuid' => $itemData['unit_uuid'] ?? null,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'created_by' => $entity->actor_uuid,
                ]);
            }

            DB::commit();
            return PurchaseRecordEntity::fromModel($purchase->load('items'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findAll(PurchaseFilterEntity $filter): PurchaseListResultEntity
    {
        $query = Purchase::query();

        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        if ($filter->supplier_uuid) {
            $query->where('supplier_uuid', $filter->supplier_uuid);
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        if ($filter->search) {
            $s = "%{$filter->search}%";
            $query->where('invoice_no', 'LIKE', $s);
        }

        $paginator = $query->orderBy('purchase_date', 'DESC')
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);

        return new PurchaseListResultEntity(
            items: array_map(fn($m) => PurchaseRecordEntity::fromModel($m), $paginator->items()),
            total: $paginator->total(),
            per_page: $paginator->perPage(),
            current_page: $paginator->currentPage()
        );
    }

    public function findByUuid(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity
    {
        $model = Purchase::query()->with('items')->where('uuid', $uuid)->first();
        return $model ? PurchaseRecordEntity::fromModel($model) : null;
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::query()->where('uuid', $uuid)->first();
            if ($purchase) {
                $purchase->items()->delete();
                $purchase->delete();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

