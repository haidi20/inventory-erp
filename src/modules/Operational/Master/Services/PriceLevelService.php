<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\PriceLevelEntity;
use App\Modules\Operational\Master\Repositories\PriceLevelRepositoryInterface;

class PriceLevelService
{
    public function __construct(protected PriceLevelRepositoryInterface $repository) {}

    public function index(PriceLevelEntity $entity): Collection
    {
        $result = $this->repository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    public function show(string $uuid): ?PriceLevelEntity
    {
        return $this->repository->findByUuid($uuid);
    }

    public function save(PriceLevelEntity $entity): PriceLevelEntity
    {
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('Nama level harga wajib diisi.');
        }

        return $this->repository->save($entity);
    }

    public function delete(PriceLevelEntity $entity): void
    {
        $this->repository->delete($entity);
    }

    public function getActiveLevels(): array
    {
        return $this->repository->getActivePriceLevels();
    }

    public function getPageData(PriceLevelEntity $entity): array
    {
        return $this->repository->getPageData($entity);
    }

    public function findByUuid(string $uuid, ?string $companyUuid = null): ?PriceLevelEntity
    {
        return $this->repository->findByUuid($uuid, $companyUuid);
    }
}
