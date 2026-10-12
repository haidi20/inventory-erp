<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Entities\SaleEntity;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Inventory\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleRepository implements SaleRepositoryInterface
{
    public function list(SaleEntity $context): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['items', 'patient', 'cashier'])
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(SaleEntity $entity): Sale
    {
        DB::beginTransaction();
        try {
            $sale = Sale::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'sale_no' => $entity->sale_no,
                'patient_uuid' => $entity->patient_uuid,
                'cashier_uuid' => $entity->cashier_uuid,
                'total_amount' => $entity->total_amount ?? 0,
                'paid' => $entity->paid ?? 0,
                'change' => $entity->change ?? 0,
                'payment_status' => $entity->payment_status,
                'payment_method' => $entity->payment_method,
                'sale_at' => $entity->sale_at ?? time(),
            ]);

            foreach ($entity->items as $item) {
                $sale->items()->create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'item_type' => $item['item_type'],
                    'item_uuid' => $item['item_uuid'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => (float) $item['qty'] * (float) $item['price'],
                ]);

                if (($item['item_type'] ?? null) === 'product') {
                    StockMovement::query()->create([
                        'branch_uuid' => $entity->branch_uuid,
                        'company_uuid' => $entity->company_uuid,
                        'product_uuid' => $item['item_uuid'],
                        'qty' => (int) $item['qty'] * -1,
                        'type' => 'out',
                        'reference_type' => 'sale',
                        'reference_uuid' => $sale->uuid,
                    ]);
                }
            }

            DB::commit();
            return $sale->load(['items', 'patient', 'cashier']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Sale
    {
        return Sale::query()
            ->with(['items', 'patient', 'cashier'])
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    public function saleNoExists(string $saleNo, string $branchUuid, string $companyUuid): bool
    {
        return Sale::query()
            ->where('sale_no', $saleNo)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->exists();
    }
}
