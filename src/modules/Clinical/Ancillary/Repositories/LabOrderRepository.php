<?php

namespace App\Modules\Clinical\Ancillary\Repositories;

use App\Modules\Clinical\Ancillary\Entities\LabOrderEntity;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabOrderRepository implements LabOrderRepositoryInterface
{
    public function getPageData(LabOrderEntity $entity): array
    {
        $query = LabOrder::with(['patient', 'doctor'])
            ->where('company_uuid', $entity->company_uuid)
            ->where('deleted_at', 0)
            ->orderByDesc('ordered_at');

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        if (!empty($entity->search)) {
            $search = $entity->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('patient', fn ($p) => $p->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if (!empty($entity->status) && $entity->status !== 'all') {
            $query->where('status', $entity->status);
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items'    => array_map(fn ($m) => LabOrderEntity::fromModel($m), $paginator->items()),
            'total'    => $paginator->total(),
            'page'     => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public function findByUuid(string $uuid, string $companyUuid): ?LabOrderEntity
    {
        $model = LabOrder::with('items')
            ->where('uuid', $uuid)
            ->where('company_uuid', $companyUuid)
            ->where('deleted_at', 0)
            ->first();

        return $model ? LabOrderEntity::fromModel($model) : null;
    }

    public function save(LabOrderEntity $entity): LabOrderEntity
    {
        DB::beginTransaction();
        try {
            $model = LabOrder::updateOrCreate(
                ['uuid' => $entity->uuid ?? (string) Str::uuid()],
                [
                    'branch_uuid'    => $entity->branch_uuid,
                    'company_uuid'   => $entity->company_uuid,
                    'order_no'       => $entity->order_no,
                    'patient_uuid'   => $entity->patient_uuid,
                    'doctor_uuid'    => $entity->doctor_uuid,
                    'ordered_at'     => $entity->ordered_at ?? time(),
                    'status'         => $entity->status,
                    'clinical_notes' => $entity->clinical_notes,
                    'updated_by'     => $entity->actor_uuid,
                ]
            );

            DB::commit();
            return LabOrderEntity::fromModel($model->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $uuid, string $actorUuid): void
    {
        LabOrder::where('uuid', $uuid)->firstOrFail()->delete();
    }

    public function updateStatus(string $uuid, string $status, string $actorUuid): void
    {
        LabOrder::where('uuid', $uuid)->update([
            'status'     => $status,
            'updated_by' => $actorUuid,
            'updated_at' => time(),
        ]);
    }
}
