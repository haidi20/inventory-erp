<?php

namespace App\Modules\Clinical\Ancillary\Repositories;

use App\Modules\Clinical\Ancillary\Entities\RadiologyOrderEntity;

interface RadiologyOrderRepositoryInterface
{
    public function getPageData(RadiologyOrderEntity $entity): array;
    public function findByUuid(string $uuid, string $companyUuid): ?RadiologyOrderEntity;
    public function save(RadiologyOrderEntity $entity): RadiologyOrderEntity;
    public function delete(string $uuid, string $actorUuid): void;
    public function updateStatus(string $uuid, string $status, string $actorUuid): void;
}
