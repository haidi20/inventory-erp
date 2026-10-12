<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnItemEntity;
use App\Modules\Operational\Procurement\Models\PurchaseReturn;
use App\Modules\Operational\Procurement\Models\PurchaseReturnItem;
use App\Modules\Operational\Procurement\Models\PurchaseReturnPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class PurchaseReturnRepository
 *
 * Implementasi repository retur pembelian menggunakan Eloquent.
 */
class PurchaseReturnRepository implements PurchaseReturnRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(PurchaseReturnEntity $entity): array
    {
        $query = PurchaseReturn::with(['supplier', 'paymentTerm']);

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }
        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->reference_no)) {
            $query->where('reference_no', $entity->reference_no);
        }

        if (!empty($entity->document_no)) {
            $s = '%' . Str::lower($entity->document_no) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(document_no) LIKE ?', [$s])
                    ->orWhereRaw('LOWER(reference_no) LIKE ?', [$s]);
            });
        }

        $total = $query->count();
        $page = 1;
        $perPage = 15;

        $items = $query->orderBy('date', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn (PurchaseReturn $model) => PurchaseReturnEntity::fromModel($model))
            ->all();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUUID(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseReturnEntity
    {
        $model = PurchaseReturn::with(['supplier', 'paymentTerm', 'items', 'payments'])
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $model ? PurchaseReturnEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(PurchaseReturnEntity $entity): ?PurchaseReturnEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $entity->uuid ?? (string) Str::uuid();

            $return = PurchaseReturn::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'document_no' => $entity->document_no,
                    'date' => $entity->date,
                    'supplier_uuid' => $entity->supplier_uuid,
                    'location_uuid' => $entity->location_uuid,
                    'payment_term_uuid' => $entity->term_uuid,
                    'total_amount' => $entity->total,
                    'notes' => $entity->notes,
                    'status' => $entity->status,
                ]
            );

            PurchaseReturnItem::where('purchase_return_uuid', $return->uuid)->delete();
            if (!empty($entity->items)) {
                foreach ($entity->items as $item) {
                    $item = $item instanceof PurchaseReturnItemEntity ? $item : new PurchaseReturnItemEntity(...$item);
                    PurchaseReturnItem::create([
                        'uuid' => (string) Str::uuid(),
                        'purchase_return_uuid' => $return->uuid,
                        'branch_uuid' => $return->branch_uuid,
                        'company_uuid' => $return->company_uuid,
                        'item_uuid' => $item->item_uuid,
                        'unit_uuid' => $item->unit_uuid,
                        'qty' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->subtotal,
                    ]);
                }
            }

            PurchaseReturnPayment::where('purchase_return_uuid', $return->uuid)->delete();
            if (!empty($entity->payments)) {
                foreach ($entity->payments as $payment) {
                    PurchaseReturnPayment::create([
                        'uuid' => (string) Str::uuid(),
                        'purchase_return_uuid' => $return->uuid,
                        'branch_uuid' => $return->branch_uuid,
                        'company_uuid' => $return->company_uuid,
                        'payment_date' => $payment['payment_date'],
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'notes' => $payment['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->findByUUID($return->uuid, $return->branch_uuid, $return->company_uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool
    {
        $return = PurchaseReturn::where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
        if (!$return) {
            return false;
        }

        DB::beginTransaction();
        try {
            PurchaseReturnItem::where('purchase_return_uuid', $return->uuid)->delete();
            PurchaseReturnPayment::where('purchase_return_uuid', $return->uuid)->delete();
            DB::commit();
            return (bool) $return->delete();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

