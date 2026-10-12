<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Operational\Master\Models\Service;
use Illuminate\Support\Str;

/**
 * TreatmentRepository
 * 
 * Implementasi repository untuk manajemen data Layanan/Tindakan Medis (Treatment/Service).
 * Mengatur logika akses data, pencarian, dan persistence untuk model Service.
 */
class TreatmentRepository implements TreatmentRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(TreatmentEntity $entity): array
    {
        $query = Service::query()
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
            'items' => array_map(fn($m) => TreatmentEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'activeServices' => Service::query()
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
    public function findByUuid(TreatmentEntity $entity): ?TreatmentEntity
    {
        $model = Service::query()
            ->where('uuid', trim((string) $entity->uuid))
            ->where('deleted_at', 0)
            ->when($entity->branch_uuid, fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? TreatmentEntity::fromModel($model) : null;
    }

    public function getByCode(TreatmentEntity $entity): ?TreatmentEntity
    {
        $model = Service::query()
            ->where('code', $entity->code)
            ->where('deleted_at', 0)
            ->when($entity->branch_uuid, fn ($query) => $query->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->company_uuid, fn ($query) => $query->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? TreatmentEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(TreatmentEntity $entity): TreatmentEntity
    {
        DB::beginTransaction();
        try {
            $service = Service::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                ],
                [
                    'code' => $entity->code,
                    'name' => $entity->name,
                    'duration_minutes' => $entity->duration_minutes ?? 0,
                    'price' => $entity->price ?? 0,
                    'doctor_fee' => $entity->doctor_fee ?? 0,
                    'is_active' => $entity->is_active ?? true,
                    'company_uuid' => $entity->company_uuid,
                ]
            );

            DB::commit();
            return TreatmentEntity::fromModel($service->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TreatmentEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Service::query()
                ->where('uuid', $entity->uuid)
                ->when($entity->branch_uuid, fn ($builder) => $builder->where('branch_uuid', $entity->branch_uuid))
                ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
