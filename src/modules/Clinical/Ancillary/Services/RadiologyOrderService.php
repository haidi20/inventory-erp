<?php

namespace App\Modules\Clinical\Ancillary\Services;

use App\Modules\Clinical\Ancillary\Entities\RadiologyOrderEntity;
use App\Modules\Clinical\Ancillary\Repositories\RadiologyOrderRepositoryInterface;
use Illuminate\Support\Str;

class RadiologyOrderService
{
    public function __construct(
        private readonly RadiologyOrderRepositoryInterface $repository,
    ) {}

    public function getPageData(RadiologyOrderEntity $entity): array
    {
        return $this->repository->getPageData($entity);
    }

    public function findByUuid(string $uuid, string $companyUuid): ?RadiologyOrderEntity
    {
        return $this->repository->findByUuid($uuid, $companyUuid);
    }

    public function save(RadiologyOrderEntity $entity): RadiologyOrderEntity
    {
        if (empty($entity->order_no)) {
            $entity = new RadiologyOrderEntity(
                uuid:             $entity->uuid,
                branch_uuid:      $entity->branch_uuid,
                company_uuid:     $entity->company_uuid,
                order_no:         'RAD-' . strtoupper(Str::random(8)),
                patient_uuid:     $entity->patient_uuid,
                doctor_uuid:      $entity->doctor_uuid,
                modality:         $entity->modality,
                body_part:        $entity->body_part,
                clinical_notes:   $entity->clinical_notes,
                status:           $entity->status,
                findings:         $entity->findings,
                impression:       $entity->impression,
                radiologist_uuid: $entity->radiologist_uuid,
                ordered_at:       $entity->ordered_at,
                resulted_at:      $entity->resulted_at,
                actor_uuid:       $entity->actor_uuid,
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
