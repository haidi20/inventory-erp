<?php

namespace App\Modules\Clinical\Integration\Services;

use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Clinical\Integration\Repositories\SatusehatSettingsRepositoryInterface;

class SatusehatSettingsService
{
    public function __construct(
        protected SatusehatSettingsRepositoryInterface $repo
    ) {}

    public function getSettings(): ?SatusehatSettingsEntity
    {
        return $this->repo->get();
    }

    public function saveSettings(SatusehatSettingsEntity $entity): SatusehatSettingsEntity
    {
        return $this->repo->save($entity);
    }
}