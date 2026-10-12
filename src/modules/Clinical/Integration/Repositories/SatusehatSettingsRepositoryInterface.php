<?php

namespace App\Modules\Clinical\Integration\Repositories;

use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;

interface SatusehatSettingsRepositoryInterface
{
    public function get(): ?SatusehatSettingsEntity;
    public function save(SatusehatSettingsEntity $entity): SatusehatSettingsEntity;
}