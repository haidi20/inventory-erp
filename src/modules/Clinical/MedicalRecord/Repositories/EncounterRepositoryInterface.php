<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;

use App\Modules\Clinical\MedicalRecord\Entities\EncounterEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EncounterRepositoryInterface
{
    public function list(EncounterEntity $context): LengthAwarePaginator;
    public function save(EncounterEntity $entity): EncounterEntity;
    public function update(EncounterEntity $entity): EncounterEntity;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?EncounterEntity;
    public function delete(string $uuid, string $branchUuid, string $companyUuid): void;
}
