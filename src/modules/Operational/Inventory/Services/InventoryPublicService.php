<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\InventoryActionRepository;
use Illuminate\Support\Str;

/**
 * InventoryPublicService
 *
 * Service publik Operational\Inventory yang boleh di-import modul lain.
 * Semua parameter dan return value berbasis Entity/DTO — tidak boleh return Model Eloquent.
 */
class InventoryPublicService
{
    public function __construct(
        private StockRepositoryInterface $stockRepo,
        private InventoryActionRepository $inventoryActionRepo
    ) {}

    /**
     * Simpan stock movement (untuk saldo awal, adjustment, dispensing, dll).
     */
    public function saveMovement(StockMovementEntity $entity): StockMovementEntity
    {
        return $this->stockRepo->saveMovement($entity);
    }

    /**
     * Hapus item opening balance berdasarkan UUID.
     */
    public function deleteItemOpeningBalance(string $uuid): void
    {
        $this->inventoryActionRepo->deleteItemOpeningBalance($uuid);
    }

    /**
     * Buat StockMovementEntity baru (factory helper).
     */
    public function makeMovementEntity(
        string $branchUuid,
        string $productUuid,
        ?string $warehouseUuid,
        string $date,
        string $type,
        StockMovementReferenceType $refType,
        string $refUuid,
        float $qty,
        float $costPrice,
        string $createdBy,
        ?string $companyUuid = null
    ): StockMovementEntity {
        return new StockMovementEntity(
            uuid: (string) Str::uuid(),
            branch_uuid: $branchUuid,
            company_uuid: $companyUuid,
            product_uuid: $productUuid,
            location_uuid: $warehouseUuid,
            date: strtotime($date),
            type: $type,
            ref_type: $refType,
            ref_uuid: $refUuid,
            qty: $qty,
            cost_price: $costPrice,
            created_by: $createdBy
        );
    }
}
