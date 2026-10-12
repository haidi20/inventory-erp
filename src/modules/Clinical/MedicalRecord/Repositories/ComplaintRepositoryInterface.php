<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;

use App\Modules\Clinical\MedicalRecord\Entities\ComplaintEntity;

interface ComplaintRepositoryInterface
{
    public function paginate(array $filters = []);
    public function find(string $uuid, ?string $companyUuid = null): ?ComplaintEntity;
    public function save(ComplaintEntity $entity): ComplaintEntity;
    public function delete(string $uuid, ?string $companyUuid = null): bool;
}

