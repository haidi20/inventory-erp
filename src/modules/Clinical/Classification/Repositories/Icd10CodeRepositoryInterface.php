<?php

namespace App\Modules\Clinical\Classification\Repositories;

use App\Modules\Clinical\Classification\Entities\Icd10CodeEntity;

interface Icd10CodeRepositoryInterface
{
    public function getPageData(Icd10CodeEntity $entity): array;
    public function findByUuid(string $uuid): ?Icd10CodeEntity;
    public function findByCode(string $code): ?Icd10CodeEntity;
    public function save(Icd10CodeEntity $entity): Icd10CodeEntity;
    public function delete(string $uuid, string $actorUuid): void;
}
