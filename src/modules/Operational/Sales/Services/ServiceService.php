<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\ServiceEntity;
use App\Modules\Operational\Sales\Repositories\ServiceRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ServiceService
{
    public function __construct(
        protected ServiceRepositoryInterface $repository
    ) {}

    public function listServices(ServiceEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createService(ServiceEntity $entity): mixed
    {
        $this->validateBeforeSave($entity);
        return $this->repository->save($entity);
    }

    public function getService(string $uuid, string $branchUuid, string $companyUuid): mixed
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    public function updateService(ServiceEntity $entity): mixed
    {
        $this->validateBeforeSave($entity, $entity->uuid);
        return $this->repository->update($entity);
    }

    public function validateBeforeSave(ServiceEntity $entity, ?string $ignoreUuid = null): void
    {
        if ($entity->code && $this->repository->codeExists($entity->code, $entity->branch_uuid, $entity->company_uuid, $ignoreUuid)) {
            throw ValidationException::withMessages([
                'code' => ['The code has already been taken.'],
            ]);
        }
    }

    public function deleteService(string $uuid, string $branchUuid, string $companyUuid): void
    {
        $this->repository->delete($uuid, $branchUuid, $companyUuid);
    }
}
