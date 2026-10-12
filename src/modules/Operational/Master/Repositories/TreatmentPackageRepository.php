<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TreatmentPackageRepository
 * 
 * Implementasi repository untuk manajemen data Paket Layanan Medis (Treatment/Service Package).
 * Mengatur logika akses data, pencarian, dan persistence (header & items) untuk model ServicePackage.
 */
class TreatmentPackageRepository implements TreatmentPackageRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(TreatmentPackageEntity $entity): array
    {
        $query = ServicePackage::query()
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

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
                    ->orWhereRaw("LOWER(code) LIKE ?", ["%{$search}%"]);
            });
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => TreatmentPackageEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'activePackages' => ServicePackage::query()
                ->where('deleted_at', 0)
                ->when($entity->branch_uuid, fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
                ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
                ->where('is_active', true)
                ->count(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(TreatmentPackageEntity $entity): ?TreatmentPackageEntity
    {
        $model = ServicePackage::query()
            ->with(['items'])
            ->where('uuid', trim((string) $entity->uuid))
            ->where('deleted_at', 0)
            ->when($entity->branch_uuid, fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? TreatmentPackageEntity::fromModel($model) : null;
    }

    public function getByCode(TreatmentPackageEntity $entity): ?TreatmentPackageEntity
    {
        $model = ServicePackage::query()
            ->where('code', $entity->code)
            ->when($entity->branch_uuid, fn ($query) => $query->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->company_uuid, fn ($query) => $query->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? TreatmentPackageEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(TreatmentPackageEntity $entity): TreatmentPackageEntity
    {
        DB::beginTransaction();
        try {
            $package = ServicePackage::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                ],
                [
                    'code' => $entity->code,
                    'name' => $entity->name,
                    'price' => $entity->price ?? 0,
                    'is_active' => $entity->is_active ?? true,
                    'company_uuid' => $entity->company_uuid,
                ]
            );

            // Sync items (Delete old ones, create new ones)
            // Note: In simplified medical system, we usually replace all items on update
            $package->items()->get()->each->delete();

            foreach ($entity->items as $itemEntity) {
                ServicePackageItem::create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $package->branch_uuid,
                    'company_uuid' => $package->company_uuid,
                    'service_package_uuid' => $package->uuid,
                    'item_type' => $itemEntity->item_type,
                    'item_uuid' => $itemEntity->item_uuid,
                    'qty' => $itemEntity->qty ?? 1,
                ]);
            }

            DB::commit();
            return TreatmentPackageEntity::fromModel($package->fresh(['items']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TreatmentPackageEntity $entity): void
    {
        DB::beginTransaction();
        try {
            $package = ServicePackage::query()
                ->where('uuid', $entity->uuid)
                ->when($entity->branch_uuid, fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
                ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
                ->firstOrFail();

            // Soft delete items as well
            $package->items()->get()->each->delete();

            $package->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
