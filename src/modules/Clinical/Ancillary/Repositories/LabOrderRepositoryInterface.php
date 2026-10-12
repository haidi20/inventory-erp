<?php

namespace App\Modules\Clinical\Ancillary\Repositories;

use App\Modules\Clinical\Ancillary\Entities\LabOrderEntity;

interface LabOrderRepositoryInterface
{
    public function getPageData(LabOrderEntity $entity): array;
    public function findByUuid(string $uuid, string $companyUuid): ?LabOrderEntity;
    public function save(LabOrderEntity $entity): LabOrderEntity;
    public function delete(string $uuid, string $actorUuid): void;
    public function updateStatus(string $uuid, string $status, string $actorUuid): void;
}
