<?php

namespace App\Modules\Clinical\Classification\Services;

use App\Modules\Clinical\Classification\Entities\Icd10CodeEntity;
use App\Modules\Clinical\Classification\Repositories\Icd10CodeRepositoryInterface;

class Icd10CodeService
{
    public function __construct(
        private readonly Icd10CodeRepositoryInterface $repository,
    ) {}

    public function getPageData(Icd10CodeEntity $entity): array
    {
        return $this->repository->getPageData($entity);
    }

    public function findByUuid(string $uuid): ?Icd10CodeEntity
    {
        return $this->repository->findByUuid($uuid);
    }

    public function save(Icd10CodeEntity $entity): Icd10CodeEntity
    {
        return $this->repository->save($entity);
    }

    public function delete(string $uuid, string $actorUuid): void
    {
        $this->repository->delete($uuid, $actorUuid);
    }
}
