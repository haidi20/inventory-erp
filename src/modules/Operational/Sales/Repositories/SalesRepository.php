<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class SalesRepository
 *
 * Implementasi repository penjualan (Sales) menggunakan Eloquent.
 */
class SalesRepository implements SalesRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function save(array $data): ?Sale
    {
        DB::beginTransaction();
        try {
            $uuid = $data['uuid'] ?? (string) Str::uuid();

            $sale = Sale::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'branch_uuid' => $data['branch_uuid'],
                    'company_uuid' => $data['company_uuid'],
                    'document_no' => $data['document_no'],
                    'date' => $data['date'],
                    'customer_uuid' => $data['customer_uuid'] ?? null,
                    'total_amount' => $data['total_amount'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                    'status' => $data['status'] ?? 'completed',
                ]
            );

            // Sync Items
            SaleItem::where('sale_uuid', $sale->uuid)->delete();
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    SaleItem::create([
                        'uuid' => (string) Str::uuid(),
                        'sale_uuid' => $sale->uuid,
                        'branch_uuid' => $sale->branch_uuid,
                        'company_uuid' => $sale->company_uuid,
                        'item_uuid' => $item['item_uuid'],
                        'unit_uuid' => $item['unit_uuid'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'total' => $item['total'],
                    ]);
                }
            }

            DB::commit();
            return $this->findByUUID($sale->uuid, $sale->branch_uuid, $sale->company_uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function findAll(array $filter): array
    {
        $query = Sale::with(['customer', 'items']);

        if (!empty($filter['branch_uuid'])) {
            $query->where('branch_uuid', $filter['branch_uuid']);
        }
        if (!empty($filter['company_uuid'])) {
            $query->where('company_uuid', $filter['company_uuid']);
        }
        if (!empty($filter['date_start'])) {
            $query->where('date', '>=', $filter['date_start']);
        }
        if (!empty($filter['date_end'])) {
            $query->where('date', '<=', $filter['date_end']);
        }
        if (!empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        return $query->orderBy('date', 'desc')->get()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function findByUUID(string $uuid, string $branchUuid, string $companyUuid): ?Sale
    {
        return Sale::with(['customer', 'items'])
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool
    {
        $sale = Sale::where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        if (!$sale) {
            return false;
        }

        DB::beginTransaction();
        try {
            SaleItem::where('sale_uuid', $sale->uuid)->delete();
            DB::commit();
            return (bool) $sale->delete();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

