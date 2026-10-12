<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Entities\SalesOrderEntity;
use App\Modules\Operational\Sales\Entities\DeliveryOrderEntity;
use App\Modules\Operational\Sales\Entities\SalesInvoiceEntity;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Sales\Models\SalesOrderDetail;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrderDetail;
use App\Modules\Finance\Ledger\Models\SalesInvoice;
use App\Modules\Finance\Ledger\Models\SalesInvoiceDetail;
use App\Modules\Finance\Ledger\Models\SalesInvoiceAging;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * SalesTransactionRepository
 * 
 * Implementasi repository untuk manajemen siklus transaksi Penjualan (SO, DO, Invoice).
 * Menangani sinkronisasi dokumen operasional secara transaksional dengan validasi scope organisasi.
 */
class SalesTransactionRepository implements SalesTransactionRepositoryInterface
{
    // --- Sales Order ---

    /** @inheritDoc */
    public function storeSO(SalesOrderEntity $entity): SalesOrderEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'person_uuid' => $entity->person_uuid,
                'location_uuid' => $entity->location_uuid,
                'payment_term_uuid' => $entity->payment_term_uuid,
                'sales_order_no' => $entity->sales_order_no,
                'sales_order_date' => $entity->sales_order_date,
                'status' => $entity->status,
                'description' => $entity->description,
                'updated_by' => $entity->actor_uuid,
            ];

            if (!empty($entity->uuid)) {
                $so = SalesOrder::query()
                    ->where('uuid', $entity->uuid)
                    ->where('branch_uuid', $entity->branch_uuid)
                    ->where('company_uuid', $entity->company_uuid)
                    ->firstOrFail();
                $so->update($data);
                $so->details()->delete();
            } else {
                $data['uuid'] = (string) Str::uuid();
                $data['created_by'] = $entity->actor_uuid;
                $so = SalesOrder::query()->create($data);
            }

            foreach ($entity->details as $detail) {
                $so->details()->create(array_merge($detail, [
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $so->branch_uuid,
                    'company_uuid' => $so->company_uuid,
                    'created_by' => $entity->actor_uuid,
                ]));
            }

            DB::commit();
            return SalesOrderEntity::fromModel($so->load('details'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** @inheritDoc */
    public function getPageDataSO(SalesOrderEntity $entity): array
    {
        $query = SalesOrder::query();

        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        if ($entity->status) $query->where('status', $entity->status);
        if ($entity->start_date) $query->where('sales_order_date', '>=', strtotime($entity->start_date));
        if ($entity->end_date) $query->where('sales_order_date', '<=', strtotime($entity->end_date));

        if ($entity->search) {
            $s = "%{$entity->search}%";
            if ($entity->search_column && $entity->search_column !== 'all') {
                $query->where($entity->search_column, 'LIKE', $s);
            } else {
                $query->where(function($q) use ($s) {
                    $q->where('sales_order_no', 'LIKE', $s)->orWhere('description', 'LIKE', $s);
                });
            }
        }

        $paginator = $query->orderBy('sales_order_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => SalesOrderEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
        ];
    }

    /** @inheritDoc */
    public function getSOByUuid(string $uuid, string $branchUuid, string $companyUuid): ?SalesOrderEntity
    {
        $model = SalesOrder::query()
            ->with('details')
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
        return $model ? SalesOrderEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getSOByOrderNo(string $orderNo, string $branchUuid, string $companyUuid): ?SalesOrderEntity
    {
        $model = SalesOrder::query()
            ->with('details')
            ->where('sales_order_no', $orderNo)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
        return $model ? SalesOrderEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function deleteSO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            $so = SalesOrder::query()->where('uuid', $uuid)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->firstOrFail();
            $so->details()->delete();
            $so->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // --- Delivery Order ---

    /** @inheritDoc */
    public function storeDO(DeliveryOrderEntity $entity): DeliveryOrderEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'person_uuid' => $entity->person_uuid,
                'location_uuid' => $entity->location_uuid,
                'delivery_no' => $entity->delivery_no,
                'delivery_date' => $entity->delivery_date,
                'description' => $entity->description,
                'updated_by' => $entity->actor_uuid,
            ];

            if (!empty($entity->uuid)) {
                $do = DeliveryOrder::query()->where('uuid', $entity->uuid)->where('branch_uuid', $entity->branch_uuid)->where('company_uuid', $entity->company_uuid)->firstOrFail();
                $do->update($data);
                $do->details()->delete();
            } else {
                $data['uuid'] = (string) Str::uuid();
                $data['created_by'] = $entity->actor_uuid;
                $do = DeliveryOrder::query()->create($data);
            }

            foreach ($entity->details as $detail) {
                $do->details()->create(array_merge($detail, [
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $do->branch_uuid,
                    'company_uuid' => $do->company_uuid,
                    'created_by' => $entity->actor_uuid,
                ]));
            }

            DB::commit();
            return DeliveryOrderEntity::fromModel($do->load('details'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** @inheritDoc */
    public function getPageDataDO(DeliveryOrderEntity $entity): array
    {
        $query = DeliveryOrder::query();
        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        if ($entity->start_date) $query->where('delivery_date', '>=', strtotime($entity->start_date));
        if ($entity->end_date) $query->where('delivery_date', '<=', strtotime($entity->end_date));

        if ($entity->search) {
            $s = "%{$entity->search}%";
            $query->where(function($q) use ($s) {
                $q->where('delivery_no', 'LIKE', $s)->orWhere('description', 'LIKE', $s);
            });
        }

        $paginator = $query->orderBy('delivery_date', 'DESC')->paginate($entity->per_page, ['*'], 'page', $entity->page);
        return [
            'items' => array_map(fn($m) => DeliveryOrderEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage()
        ];
    }

    /** @inheritDoc */
    public function getDOByUuid(string $uuid, string $branchUuid, string $companyUuid): ?DeliveryOrderEntity
    {
        $model = DeliveryOrder::query()->with('details')->where('uuid', $uuid)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->first();
        return $model ? DeliveryOrderEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getDOByDeliveryNo(string $deliveryNo, string $branchUuid, string $companyUuid): ?DeliveryOrderEntity
    {
        $model = DeliveryOrder::query()->with('details')->where('delivery_no', $deliveryNo)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->first();
        return $model ? DeliveryOrderEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function deleteDO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            $do = DeliveryOrder::query()->where('uuid', $uuid)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->firstOrFail();
            $do->details()->delete();
            $do->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // --- Sales Invoice ---

    /** @inheritDoc */
    public function storeInvoice(SalesInvoiceEntity $entity): SalesInvoiceEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'person_uuid' => $entity->person_uuid,
                'invoice_no' => $entity->invoice_no,
                'invoice_date' => $entity->invoice_date,
                'payment_term_uuid' => $entity->payment_term_uuid,
                'location_uuid' => $entity->location_uuid,
                'status' => $entity->status,
                'updated_by' => $entity->actor_uuid,
            ];

            if (!empty($entity->uuid)) {
                $inv = SalesInvoice::query()->where('uuid', $entity->uuid)->where('branch_uuid', $entity->branch_uuid)->where('company_uuid', $entity->company_uuid)->firstOrFail();
                $inv->update($data);
                $inv->details()->delete();
                $inv->agings()->delete();
            } else {
                $data['uuid'] = (string) Str::uuid();
                $data['created_by'] = $entity->actor_uuid;
                $inv = SalesInvoice::query()->create($data);
            }

            foreach ($entity->details as $detail) {
                $inv->details()->create(array_merge($detail, [
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $inv->branch_uuid,
                    'company_uuid' => $inv->company_uuid,
                    'created_by' => $entity->actor_uuid,
                ]));
            }

            DB::commit();
            return SalesInvoiceEntity::fromModel($inv->load(['details', 'agings']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** @inheritDoc */
    public function getPageDataInvoice(SalesInvoiceEntity $entity): array
    {
        $query = SalesInvoice::query();
        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        if ($entity->status) $query->where('status', $entity->status);

        if ($entity->search) {
            $s = "%{$entity->search}%";
            $query->where('invoice_no', 'LIKE', $s);
        }

        $paginator = $query->orderBy('invoice_date', 'DESC')->paginate($entity->per_page, ['*'], 'page', $entity->page);
        return [
            'items' => array_map(fn($m) => SalesInvoiceEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage()
        ];
    }

    /** @inheritDoc */
    public function getInvoiceByUuid(string $uuid, string $branchUuid, string $companyUuid): ?SalesInvoiceEntity
    {
        $model = SalesInvoice::query()->with(['details', 'agings'])->where('uuid', $uuid)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->first();
        return $model ? SalesInvoiceEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getInvoiceByInvoiceNo(string $invoiceNo, string $branchUuid, string $companyUuid): ?SalesInvoiceEntity
    {
        $model = SalesInvoice::query()->with(['details', 'agings'])->where('invoice_no', $invoiceNo)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->first();
        return $model ? SalesInvoiceEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function deleteInvoice(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            $inv = SalesInvoice::query()->where('uuid', $uuid)->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->firstOrFail();
            $inv->details()->delete();
            $inv->agings()->delete();
            $inv->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // --- Aging & Helpers ---

    /** @inheritDoc */
    public function storeAging(array $agingData): void
    {
        $uuid = $agingData['uuid'] ?? (string) Str::uuid();
        SalesInvoiceAging::query()->updateOrCreate(
            ['uuid' => $uuid],
            $agingData
        );
    }

    /** @inheritDoc */
    public function getAgingByInvoiceUuid(string $invoiceUuid, string $branchUuid, string $companyUuid): array
    {
        return SalesInvoiceAging::query()
            ->where('sales_invoice_uuid', $invoiceUuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->orderBy('due_date', 'ASC')
            ->get()
            ->toArray();
    }

    /** @inheritDoc */
    public function findSOForExport(SalesOrderEntity $entity): array
    {
        $query = SalesOrder::query()->with('details');
        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        $items = $query->orderBy('sales_order_date', 'DESC')->get();
        return $items->map(fn($m) => SalesOrderEntity::fromModel($m))->toArray();
    }

    /** @inheritDoc */
    public function findDOForExport(DeliveryOrderEntity $entity): array
    {
        $query = DeliveryOrder::query()->with('details');
        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        $items = $query->orderBy('delivery_date', 'DESC')->get();
        return $items->map(fn($m) => DeliveryOrderEntity::fromModel($m))->toArray();
    }

    /** @inheritDoc */
    public function findInvoiceForExport(SalesInvoiceEntity $entity): array
    {
        $query = SalesInvoice::query()->with('details');
        if ($entity->company_uuid) $query->where('company_uuid', $entity->company_uuid);
        if ($entity->branch_uuid) $query->where('branch_uuid', $entity->branch_uuid);
        $items = $query->orderBy('invoice_date', 'DESC')->get();
        return $items->map(fn($m) => SalesInvoiceEntity::fromModel($m))->toArray();
    }
}

