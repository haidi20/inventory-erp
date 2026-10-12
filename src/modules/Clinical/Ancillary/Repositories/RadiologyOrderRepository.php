<?php

namespace App\Modules\Clinical\Ancillary\Repositories;

use App\Modules\Clinical\Ancillary\Entities\RadiologyOrderEntity;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RadiologyOrderRepository implements RadiologyOrderRepositoryInterface
{
    public function getPageData(RadiologyOrderEntity $entity): array
    {
        $query = RadiologyOrder::with(['patient', 'doctor'])
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
            'items'    => array_map(fn ($m) => RadiologyOrderEntity::fromModel($m), $paginator->items()),
            'total'    => $paginator->total(),
            'page'     => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public function findByUuid(string $uuid, string $companyUuid): ?RadiologyOrderEntity
    {
        $model = RadiologyOrder::where('uuid', $uuid)
            ->where('company_uuid', $companyUuid)
            ->where('deleted_at', 0)
            ->first();

        return $model ? RadiologyOrderEntity::fromModel($model) : null;
    }

    public function save(RadiologyOrderEntity $entity): RadiologyOrderEntity
    {
        DB::beginTransaction();
        try {
            $model = RadiologyOrder::updateOrCreate(
                ['uuid' => $entity->uuid ?? (string) Str::uuid()],
                [
                    'branch_uuid'      => $entity->branch_uuid,
                    'company_uuid'     => $entity->company_uuid,
                    'order_no'         => $entity->order_no,
                    'patient_uuid'     => $entity->patient_uuid,
                    'doctor_uuid'      => $entity->doctor_uuid,
                    'modality'         => $entity->modality,
                    'body_part'        => $entity->body_part,
                    'clinical_notes'   => $entity->clinical_notes,
                    'status'           => $entity->status,
                    'findings'         => $entity->findings,
                    'impression'       => $entity->impression,
                    'radiologist_uuid' => $entity->radiologist_uuid,
                    'ordered_at'       => $entity->ordered_at ?? time(),
                    'resulted_at'      => $entity->resulted_at,
                    'updated_by'       => $entity->actor_uuid,
                ]
            );

            DB::commit();
            return RadiologyOrderEntity::fromModel($model->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $uuid, string $actorUuid): void
    {
        RadiologyOrder::where('uuid', $uuid)->firstOrFail()->delete();
    }

    public function updateStatus(string $uuid, string $status, string $actorUuid): void
    {
        RadiologyOrder::where('uuid', $uuid)->update([
            'status'     => $status,
            'updated_by' => $actorUuid,
            'updated_at' => time(),
        ]);
    }
}
