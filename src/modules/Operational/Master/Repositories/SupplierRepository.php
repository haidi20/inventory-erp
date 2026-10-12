<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Support\Str;

/**
 * SupplierRepository
 * 
 * Implementasi repository untuk manajemen data Pemasok (Supplier).
 * Mengatur logika akses data, pencarian, dan persistence untuk model Supplier.
 */
class SupplierRepository implements SupplierRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(SupplierEntity $entity): array
    {
        $sortableColumns = ['name', 'email', 'phone'];
        $sortBy = in_array($entity->sort_by, $sortableColumns, true) ? $entity->sort_by : 'name';
        $sortDirection = strtolower((string) $entity->sort_direction) === 'desc' ? 'desc' : 'asc';

        $query = Supplier::query()
            ->where('deleted_at', 0)
            ->orderBy($sortBy, $sortDirection);

        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }
        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (($entity->search ?? '') !== '') {
            $search = strtolower($entity->search);
            $query->where(function ($builder) use ($search) {
                $builder->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(phone) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(email) LIKE ?", ["%{$search}%"]);
            });
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        $activeSuppliersQuery = Supplier::query()->where('deleted_at', 0)->where('is_active', true);
        $inactiveSuppliersQuery = Supplier::query()->where('deleted_at', 0)->where('is_active', false);

        if (!empty($entity->branch_uuid)) {
            $activeSuppliersQuery->where('branch_uuid', $entity->branch_uuid);
            $inactiveSuppliersQuery->where('branch_uuid', $entity->branch_uuid);
        }
        if (!empty($entity->company_uuid)) {
            $activeSuppliersQuery->where('company_uuid', $entity->company_uuid);
            $inactiveSuppliersQuery->where('company_uuid', $entity->company_uuid);
        }

        return [
            'items' => array_map(fn($m) => SupplierEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'activeSuppliers' => $activeSuppliersQuery->count(),
            'inactiveSuppliers' => $inactiveSuppliersQuery->count(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(SupplierEntity $entity): ?SupplierEntity
    {
        $model = Supplier::query()
            ->where('uuid', trim((string) $entity->uuid))
            ->where('deleted_at', 0);

        if (!empty($entity->branch_uuid)) {
            $model->where('branch_uuid', $entity->branch_uuid);
        }
        if (!empty($entity->company_uuid)) {
            $model->where('company_uuid', $entity->company_uuid);
        }

        $model = $model->first();

        return $model ? SupplierEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(SupplierEntity $entity): SupplierEntity
    {
        DB::beginTransaction();
        try {
            $supplier = Supplier::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'name' => $entity->name,
                    'phone' => $entity->phone,
                    'email' => $entity->email,
                    'address' => $entity->address,
                    'term_days' => $entity->term_days,
                    'is_active' => $entity->is_active ?? true,
                ]
            );

            DB::commit();
            return SupplierEntity::fromModel($supplier->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(SupplierEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Supplier::query()
                ->where('uuid', $entity->uuid)
                ->when($entity->branch_uuid, fn ($query) => $query->where('branch_uuid', $entity->branch_uuid))
                ->when($entity->company_uuid, fn ($query) => $query->where('company_uuid', $entity->company_uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cek apakah supplier memiliki transaksi pembelian (legacy check).
     * 
     * @param SupplierEntity $entity
     * @return bool
     */
    public function hasPurchases(SupplierEntity $entity): bool
    {
        $supplier = Supplier::query()
            ->where('uuid', $entity->uuid)
            ->when($entity->branch_uuid, fn ($query) => $query->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->company_uuid, fn ($query) => $query->where('company_uuid', $entity->company_uuid))
            ->firstOrFail();
        return $supplier->purchases()->exists();
    }
}
