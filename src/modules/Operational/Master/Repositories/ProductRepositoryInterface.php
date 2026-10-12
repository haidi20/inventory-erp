<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Models\Product;

/**
 * ProductRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Master Produk.
 * Menangani logika kompleks terkait variasi satuan, harga level, dan manajemen stok.
 */
interface ProductRepositoryInterface
{
    /**
     * Mendapatkan query builder produk yang dapat dipakai ulang oleh export.
     */
    public function query(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Mendapatkan data halaman produk dengan filter pencarian, kategori, dan statistik.
     * 
     * @param ProductEntity $entity
     * @return array
     */
    public function getPageData(ProductEntity $entity): array;

    /**
     * Mendapatkan opsi-opsi untuk modal filter master produk.
     *
     * @param ProductEntity $entity
     * @return array
     */
    public function getFilterOptions(ProductEntity $entity): array;

    /**
     * Mencari produk berdasarkan UUID dalam konteks cabang dan perusahaan.
     * 
     * @param ProductEntity $entity
     * @return ProductEntity|null
     */
    public function findByUuid(ProductEntity $entity): ?ProductEntity;

    /**
     * Menyimpan data produk baru atau memperbarui data lama beserta relasi (Unit, Harga, Akun).
     * 
     * @param ProductEntity $entity
     * @param array $extraData Data tambahan seperti units, prices, suppliers, dll.
     * @return ProductEntity
     */
    public function save(ProductEntity $entity, array $extraData = []): ProductEntity;

    /**
     * Memeriksa apakah produk sudah memiliki riwayat transaksi.
     * 
     * @param string $uuid
     * @return bool
     */
    public function hasTransactions(string $uuid): bool;

    /**
     * Menghapus produk (Soft Delete).
     * 
     * @param ProductEntity $entity
     * @return void
     */
    public function delete(ProductEntity $entity): void;

    /**
     * Mencari produk berdasarkan kode unik.
     * 
     * @param ProductEntity $entity
     * @return ProductEntity|null
     */
    public function getByCode(ProductEntity $entity): ?ProductEntity;

    /**
     * Mendapatkan daftar produk untuk kebutuhan ekspor data.
     * 
     * @param ProductEntity $entity
     * @return array<ProductEntity>
     */
    public function listForExport(ProductEntity $entity): array;

    /**
     * Mengurangi stok produk di gudang tertentu.
     * 
     * @param string $warehouseUuid
     * @param string $productUuid
     * @param float $qty
     * @param string $refType
     * @param string $refUuid
     * @param string $actorUuid
     * @return void
     */
    public function reduceStock(string $warehouseUuid, string $productUuid, float $qty, string $refType, string $refUuid, string $actorUuid): void;

    public function getFormOptions(): array;

    public function getCompanyOptions(): array;

    public function getDropdownOptions(string $type, string $search, string $selected, int $limit): array;

    public function getDefaultCompanyUuid(): ?string;

    public function findWithRelations(string $uuid, string $branchUuid, string $companyUuid, bool $isSuperadmin = false): ?Product;
}
