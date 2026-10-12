<?php

namespace App\Modules\Clinical\Pharmacy\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Clinical\Pharmacy\Models\Dispensing;
use App\Modules\Clinical\Pharmacy\Models\DispensingItem;
use App\Modules\Clinical\Pharmacy\Entities\DispensingEntity;
use App\Modules\Clinical\Pharmacy\Entities\DispensingItemEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class DispensingRepository
{
    /**
     * Build standard dispensing query.
     *
     * Membangun query standar untuk data dispensing.
     */
    public function buildQuery(array $filters = []): Builder
    {
        $query = Dispensing::with(['patient', 'dispenser', 'items.product'])
            ->latest('dispensed_at');

        if (!empty($filters['company_uuid'])) {
            $query->where('company_uuid', $filters['company_uuid']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('dispensing_no', 'LIKE', "%$search%")
                  ->orWhereHas('patient', fn($p) => $p->where('name', 'LIKE', "%$search%"));
            });
        }

        return $query;
    }

    /**
     * Get paginated dispensing list.
     * 
     * Mendapatkan daftar dispensing dengan paginasi.
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    /**
     * Create fresh dispensing record from Entity.
     * 
     * Membuat record dispensing baru dari Entity.
     */
    public function create(DispensingEntity $entity): Dispensing
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return Dispensing::create($entity->toArray());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create dispensing item from Entity.
     * 
     * Membuat item detail dispensing dari Entity.
     */
    public function createItem(DispensingItemEntity $entity): DispensingItem
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return DispensingItem::create($entity->toArray());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get dispensing by UUID.
     * 
     * Mencari data dispensing berdasarkan UUID.
     */
    public function findByUuid(string $uuid): Dispensing
    {
        return Dispensing::with('items.product')->findOrFail($uuid);
    }

    public function countToday(): int
    {
        return Dispensing::query()
            ->whereBetween('created_at', [today()->startOfDay()->timestamp, today()->endOfDay()->timestamp])
            ->count();
    }

    /**
     * Delete dispensing record.
     * 
     * Menghapus record dispensing.
     */
    public function delete(string $uuid): bool
    {
        DB::beginTransaction();
        try {
            $dispensing = Dispensing::findOrFail($uuid);
            DB::commit();
            return $dispensing->delete();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}


