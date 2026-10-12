<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PosPurchaseEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Inventory\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosPurchaseRepository implements PosPurchaseRepositoryInterface
{
    public function list(PosPurchaseEntity $context): LengthAwarePaginator
    {
        return Purchase::query()
            ->with(['items', 'supplier'])
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(PosPurchaseEntity $entity): PurchaseRecordEntity
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'invoice_no' => $entity->invoice_no,
                'supplier_uuid' => $entity->supplier_uuid,
                'total' => $entity->total ?? 0,
                'paid' => $entity->paid ?? 0,
                'status' => $entity->status,
                'purchase_date' => $entity->purchase_date ?? time(),
            ]);

            foreach ($entity->items as $item) {
                $purchase->items()->create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'product_uuid' => $item['product_uuid'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => (float) $item['qty'] * (float) $item['price'],
                ]);

                StockMovement::query()->create([
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'product_uuid' => $item['product_uuid'],
                    'qty' => (int) $item['qty'],
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_uuid' => $purchase->uuid,
                ]);
            }

            DB::commit();
            return PurchaseRecordEntity::fromModel($purchase->load(['items', 'supplier']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity
    {
        $purchase = Purchase::query()
            ->with(['items', 'supplier'])
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $purchase ? PurchaseRecordEntity::fromModel($purchase) : null;
    }

    public function invoiceNoExists(string $invoiceNo, string $branchUuid, string $companyUuid): bool
    {
        return Purchase::query()
            ->where('invoice_no', $invoiceNo)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->exists();
    }
}
