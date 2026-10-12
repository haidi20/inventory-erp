<?php

namespace App\Modules\Clinical\Registration\Repositories;

use App\Modules\Clinical\Registration\Entities\RegistrationEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RegistrationRepositoryInterface
{
    public function list(RegistrationEntity $context): LengthAwarePaginator;
    public function save(RegistrationEntity $entity): RegistrationEntity;
    public function update(RegistrationEntity $entity): RegistrationEntity;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?RegistrationEntity;
    public function delete(string $uuid, string $branchUuid, string $companyUuid): void;
}
