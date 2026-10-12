<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderListResultEntity;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function save(PurchaseOrderEntity $entity): PurchaseOrder
    {
        DB::beginTransaction();
        try {
            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'supplier_uuid' => $entity->supplier_uuid,
                'location_uuid' => $entity->location_uuid,
                'document_no' => $entity->document_no,
                'reference_no' => $entity->reference_no,
                'date' => $entity->date,
                'status' => $entity->status->value,
                'notes' => $entity->notes,
                'discount_percent' => $entity->discount_percent,
                'discount_amount' => $entity->discount_amount,
                'tax_percent' => $entity->tax_percent,
                'tax_amount' => $entity->tax_amount,
                'updated_at' => time(),
            ];

            if (empty($entity->uuid)) {
                $entity->uuid = (string) Str::uuid();
                $data['created_at'] = time();
                $data['created_by'] = $entity->actor_uuid;
            }

            $po = PurchaseOrder::updateOrCreate(['uuid' => $entity->uuid], $data);
            
            $po->items()->delete();
            foreach ($entity->items as $item) {
                $po->items()->create([
                    'uuid' => $item->uuid ?? (string) Str::uuid(),
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'product_uuid' => $item->product_uuid,
                    'unit_uuid' => $item->unit_uuid,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'subtotal' => $item->subtotal,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }

            DB::commit();
            return $po->load('items');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findAll(PurchaseOrderFilterEntity $filter): PurchaseOrderListResultEntity
    {
        $query = PurchaseOrder::query();

        if ($filter->company_uuid) {
            $query->where('company_uuid', $filter->company_uuid);
        }

        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        if ($filter->search) {
            $s = "%{$filter->search}%";
            $query->where('document_no', 'LIKE', $s);
        }

        $paginator = $query->orderBy('date', 'DESC')
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);

        return new PurchaseOrderListResultEntity(
            items: $paginator->items(),
            total: $paginator->total(),
            page: $paginator->currentPage()
        );
    }

    public function findByUuid(string $uuid, ?string $company_uuid = null): ?PurchaseOrder
    {
        $query = PurchaseOrder::query()->with('items')->where('uuid', $uuid);

        if ($company_uuid) {
            $query->where('company_uuid', $company_uuid);
        }

        return $query->first();
    }

    public function delete(string $uuid, string $actorUuid): bool
    {
        $po = PurchaseOrder::query()->where('uuid', $uuid)->first();
        if ($po) {
            $po->items()->delete();
            return $po->delete();
        }
        return false;
    }
}

