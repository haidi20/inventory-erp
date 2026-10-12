<?php

namespace App\Modules\Operational\Procurement\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseReturn;
use App\Modules\Operational\Procurement\Models\PurchaseReturnItem;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnItemEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProcurementRepository
{
    /**
     * Build purchase query.
     * 
     * Membangun query untuk data pembelian (PO).
     */
    public function buildPurchaseQuery(array $filters = []): Builder
    {
        $query = Purchase::with(['supplier', 'items.product'])->latest('purchase_date');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%$search%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'LIKE', "%$search%"));
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'pending')  $query->whereNull('received_at');
            if ($filters['status'] === 'received') $query->whereNotNull('received_at');
        }

        return $query;
    }

    /**
     * Get paginated purchases.
     * 
     * Mendapatkan daftar pembelian dengan paginasi.
     */
    public function getPaginatedPurchases(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildPurchaseQuery($filters)->paginate($perPage);
    }

    /**
     * Build purchase return query.
     * 
     * Membangun query untuk data retur pembelian.
     */
    public function buildReturnQuery(array $filters = []): Builder
    {
        $query = PurchaseReturn::with(['supplier', 'purchase', 'items.product'])->latest('returned_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'LIKE', "%$search%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'LIKE', "%$search%"));
            });
        }

        return $query;
    }

    /**
     * Get paginated returns.
     * 
     * Mendapatkan daftar retur dengan paginasi.
     */
    public function getPaginatedReturns(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildReturnQuery($filters)->paginate($perPage);
    }

    /**
     * Create purchase return record from Entity.
     * 
     * Membuat record retur pembelian dari Entity.
     */
    public function createReturn(PurchaseReturnEntity $entity): PurchaseReturn
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return PurchaseReturn::create($entity->toArray());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create purchase return item from Entity.
     * 
     * Membuat item detail retur pembelian dari Entity.
     */
    public function createReturnItem(PurchaseReturnItemEntity $entity): PurchaseReturnItem
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return PurchaseReturnItem::create($entity->toArray());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Find purchase by UUID.
     * 
     * Mencari data pembelian berdasarkan UUID.
     */
    public function findPurchaseByUuid(string $uuid): Purchase
    {
        return Purchase::with('items.product')->findOrFail($uuid);
    }

    public function markPurchaseReceived(Purchase $purchase): void
    {
        $purchase->update([
            'received_at' => now(),
            'received_by_uuid' => auth()->user()->uuid,
        ]);
    }

    public function countReturnsToday(): int
    {
        return PurchaseReturn::query()
            ->whereBetween('created_at', [today()->startOfDay()->timestamp, today()->endOfDay()->timestamp])
            ->count();
    }

    public function deleteReturn(PurchaseReturn $return): void
    {
        $return->items()->delete();
        $return->delete();
    }

    /**
     * Find return by UUID.
     * 
     * Mencari data retur berdasarkan UUID.
     */
    public function findReturnByUuid(string $uuid): PurchaseReturn
    {
        return PurchaseReturn::with('items.product')->findOrFail($uuid);
    }
}



