<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Entities\LookupSetListResultEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueListResultEntity;

/**
 * LookupSetRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data referensi dan mekanisme staging.
 */
interface LookupSetRepositoryInterface
{
    // LookupSet CRUD
    public function storeSet(LookupSetEntity $entity): LookupSetEntity;
    public function getSetByUuid(LookupSetEntity $entity): ?LookupSetEntity;
    public function getSetById(LookupSetEntity $entity): ?LookupSetEntity;
    public function getSetByProgramCode(LookupSetEntity $entity): ?LookupSetEntity;
    public function getSetByName(LookupSetEntity $entity): ?LookupSetEntity;
    public function updateSet(LookupSetEntity $entity): LookupSetEntity;
    public function destroySet(string $uuid, ?string $companyUuid = null): void;
    public function getSetIndexData(LookupSetEntity $entity): LookupSetListResultEntity;

    // LookupValue CRUD
    public function storeValue(LookupSetValueEntity $entity): LookupSetValueEntity;
    public function getValueByUuid(LookupSetValueEntity $entity): ?LookupSetValueEntity;
    public function updateValue(LookupSetValueEntity $entity): LookupSetValueEntity;
    public function destroyValue(string $uuid, ?string $companyUuid = null): void;
    public function getValueIndexData(LookupSetValueEntity $entity): LookupSetValueListResultEntity;
    
    public function getValuesBySetId(LookupSetEntity $entity): LookupSetValueListResultEntity;
    public function getValuesBySetName(LookupSetEntity $entity): LookupSetValueListResultEntity;

    // Utils
    public function upsertValue(LookupSetValueEntity $entity): LookupSetValueEntity;
    
    // Staging
    public function syncTempToMaster(int|string $setId): void;
    public function clearTempValues(int|string $setId): void;
}
