<?php

namespace App\Modules\Operational\Inventory\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Inventory\Models\ItemOpeningBalance;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryActionRepository
{
    /**
     * Create stock movement record from Entity.
     * 
     * Mencatat mutasi/pergerakan stok barang menggunakan Entity.
     */
    public function createMovement(StockMovementEntity $entity): StockMovement
    {
        DB::beginTransaction();
        try {
        $movement = StockMovement::create([
            'uuid' => $entity->uuid,
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid,
            'product_uuid' => $entity->product_uuid,
            'location_uuid' => $entity->location_uuid,
            'qty' => $entity->qty,
            'type' => $entity->type,
            'reference_type' => $entity->ref_type?->value,
            'reference_uuid' => $entity->ref_uuid,
            'price' => $entity->cost_price,
            'user_uuid' => $entity->created_by,
            'created_by' => $entity->created_by,
            'updated_by' => $entity->created_by,
        ]);
        DB::commit();
        return $movement;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Build adjustment query.
     * 
     * Membangun query untuk data penyesuaian stok.
     */
    public function buildAdjustmentQuery(array $filters = []): Builder
    {
        $query = StockMovement::where('type', 'adjust')
            ->with(['product', 'user'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', fn($q) => $q->where('name', 'LIKE', "%$search%"));
        }

        return $query;
    }

    /**
     * Get paginated adjustments.
     * 
     * Mendapatkan daftar penyesuaian stok dengan paginasi.
     */
    public function getPaginatedAdjustments(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->buildAdjustmentQuery($filters)->paginate($perPage);
    }

    /**
     * Delete movements by reference.
     * 
     * Menghapus catatan mutasi stok berdasarkan referensi dokumen.
     */
    public function deleteByReference(string $type, string $uuid): void
    {
        DB::beginTransaction();
        try {
            StockMovement::where('reference_type', $type)
                ->where('reference_uuid', $uuid)
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteItemOpeningBalance(string $uuid): void
    {
        ItemOpeningBalance::query()->where('uuid', $uuid)->delete();
    }
}



