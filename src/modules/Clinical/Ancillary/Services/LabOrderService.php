<?php

namespace App\Modules\Clinical\Ancillary\Services;

use App\Modules\Clinical\Ancillary\Entities\LabOrderEntity;
use App\Modules\Clinical\Ancillary\Repositories\LabOrderRepositoryInterface;
use Illuminate\Support\Str;

class LabOrderService
{
    public function __construct(
        private readonly LabOrderRepositoryInterface $repository,
    ) {}

    public function getPageData(LabOrderEntity $entity): array
    {
        return $this->repository->getPageData($entity);
    }

    public function findByUuid(string $uuid, string $companyUuid): ?LabOrderEntity
    {
        return $this->repository->findByUuid($uuid, $companyUuid);
    }

    public function save(LabOrderEntity $entity): LabOrderEntity
    {
        if (empty($entity->order_no)) {
            $entity = new LabOrderEntity(
                uuid:          $entity->uuid,
                branch_uuid:   $entity->branch_uuid,
                company_uuid:  $entity->company_uuid,
                order_no:      'LAB-' . strtoupper(Str::random(8)),
                patient_uuid:  $entity->patient_uuid,
                doctor_uuid:   $entity->doctor_uuid,
                ordered_at:    $entity->ordered_at,
                status:        $entity->status,
                clinical_notes: $entity->clinical_notes,
                resulted_by:   $entity->resulted_by,
                resulted_at:   $entity->resulted_at,
                actor_uuid:    $entity->actor_uuid,
                items:         $entity->items,
            );
        }

        return $this->repository->save($entity);
    }

    public function delete(string $uuid, string $actorUuid): void
    {
        $this->repository->delete($uuid, $actorUuid);
    }

    public function updateStatus(string $uuid, string $status, string $actorUuid): void
    {
        $this->repository->updateStatus($uuid, $status, $actorUuid);
    }
}
