<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Repositories\ItemConversionRepositoryInterface;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Modules\Operational\Master\Entities\ItemUomEntity;

/**
 * ItemConversionService
 *
 * Port dari BE: item_conversion_service.go. Mengelola konversi satuan (UOM)
 * per barang, termasuk menyimpan pasangan from→to unit dan daftar UOM per item.
 */
class ItemConversionService
{
    public function __construct(protected ItemConversionRepositoryInterface $itemConversionRepository) {}

    public function save(ItemConversionEntity $entity): ?ItemConversionEntity
    {
        if (empty($entity->company_uuid)) {
            throw new \InvalidArgumentException('Company UUID wajib diisi.');
        }
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('Branch UUID wajib diisi.');
        }
        if (empty($entity->from_unit_uuid) || empty($entity->to_unit_uuid)) {
            throw new \InvalidArgumentException('Satuan asal dan tujuan wajib diisi.');
        }
        if ($entity->from_unit_uuid === $entity->to_unit_uuid) {
            throw new \InvalidArgumentException('Satuan asal dan tujuan tidak boleh sama.');
        }
        if ($entity->conv_ratio <= 0) {
            throw new \InvalidArgumentException('Rasio konversi harus lebih besar dari nol.');
        }

        return $this->itemConversionRepository->save($entity);
    }

    public function delete(ItemConversionEntity $entity): void
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('UUID konversi wajib diisi.');
        }
        $this->itemConversionRepository->delete($entity);
    }

    public function getByUnits(string $fromUnit, string $toUnit, string $branchUuid, string $companyUuid, ?string $itemUuid = null): ?ItemConversionEntity
    {
        return $this->itemConversionRepository->getByUnits($fromUnit, $toUnit, $branchUuid, $companyUuid, $itemUuid);
    }

    public function index(ItemConversionEntity $entity): array
    {
        return $this->itemConversionRepository->getPageData($entity);
    }

    public function saveUom(ItemConversionEntity $entity): ?ItemUomEntity
    {
        if (empty($entity->branch_uuid) || empty($entity->company_uuid)) {
            throw new \InvalidArgumentException('Branch dan Company UUID wajib diisi.');
        }
        if (empty($entity->item_uuid) || empty($entity->uom_code)) {
            throw new \InvalidArgumentException('Item dan Kode Satuan wajib diisi.');
        }

        return $this->itemConversionRepository->saveUom($entity->toArray());
    }

    public function getUomsByItem(string $itemUuid, string $branchUuid, string $companyUuid): array
    {
        return $this->itemConversionRepository->getUomsByItem($itemUuid, $branchUuid, $companyUuid);
    }
}
