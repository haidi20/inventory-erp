<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepository;
use App\Modules\Operational\Master\Repositories\EmployeeRepositoryInterface;

/**
 * MasterPublicService
 *
 * Service publik Operational\Master yang boleh di-import modul lain.
 * Semua return value berbasis Entity/DTO — tidak boleh return Model Eloquent.
 */
class MasterPublicService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * Cari produk berdasarkan UUID, kembalikan sebagai Entity.
     */
    public function findProductByUuid(string $uuid, string $branchUuid = '', string $companyUuid = ''): ?ProductEntity
    {
        $entity = new ProductEntity(uuid: $uuid, branch_uuid: $branchUuid ?: null, company_uuid: $companyUuid ?: null);
        return $this->productRepository->findByUuid($entity);
    }

    /**
     * Cek stok produk (via ProductLocation).
     */
    public function getProductStock(string $productUuid): float
    {
        $product = $this->productRepository->buildProductsQuery()
            ->where('uuid', $productUuid)
            ->first();
        return $product ? (float) ($product->stock ?? 0) : 0.0;
    }

    /**
     * Kurangi stok produk langsung (emergency fallback, prefer InventoryPublicService).
     */
    public function decrementProductStock(string $productUuid, float $qty): void
    {
        $this->productRepository->buildProductsQuery()
            ->where('uuid', $productUuid)
            ->decrement('stock', $qty);
    }

    /**
     * Tambah stok produk langsung (emergency fallback).
     */
    public function incrementProductStock(string $productUuid, float $qty): void
    {
        $this->productRepository->buildProductsQuery()
            ->where('uuid', $productUuid)
            ->increment('stock', $qty);
    }

    public function updateEmployeePractitionerId(string $nik, string $practitionerId): void
    {
        $this->employeeRepository->updatePractitionerId($nik, $practitionerId);
    }
}
