<?php

namespace App\Modules\Clinical\MedicalRecord\Services;

use App\Modules\Clinical\MedicalRecord\Repositories\ComplaintRepositoryInterface;
use App\Modules\Clinical\MedicalRecord\Entities\ComplaintEntity;

class ComplaintService
{
    public function __construct(
        protected ComplaintRepositoryInterface $repository
    ) {}

    public function getList(array $filters = [])
    {
        return $this->repository->paginate($filters);
    }

    public function find(string $uuid, ?string $companyUuid = null): ?ComplaintEntity
    {
        return $this->repository->find($uuid, $companyUuid);
    }

    public function submitComplaint(ComplaintEntity $entity): ComplaintEntity
    {
        return $this->repository->save($entity);
    }

    public function updateStatus(string $uuid, string $status, ?string $companyUuid = null, ?string $updatedBy = null): ?ComplaintEntity
    {
        $complaint = $this->repository->find($uuid, $companyUuid);
        if ($complaint) {
            $entity = ComplaintEntity::fromArray(array_merge($complaint->toArray(), [
                'status' => $status,
                'uuid' => $uuid,
                'updated_by' => $updatedBy,
            ]));
            return $this->repository->save($entity);
        }
        return null;
    }

    public function deleteComplaint(string $uuid, ?string $companyUuid = null): bool
    {
        return $this->repository->delete($uuid, $companyUuid);
    }
}

