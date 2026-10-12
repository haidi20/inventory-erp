<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * MasterProductService
 * 
 * Port dari BE: master_product_service.go. Mengelola data Master Barang dan Jasa.
 */
class MasterProductService
{
    public function __construct(protected ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * Daftar Barang Jasa dengan pagination dan filter standar.
     */
    public function index(ProductEntity $entity): Collection
    {
        $result = $this->productRepository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    /**
     * Ambil detail produk berdasarkan UUID.
     */
    public function show(ProductEntity $entity): ?ProductEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        return $this->productRepository->findByUuid($entity);
    }

    /**
     * Simpan data produk dengan validasi ketat.
     */
    public function save(ProductEntity $entity, array $extraData = []): ProductEntity
    {
        if (empty($entity->code)) {
            throw new \InvalidArgumentException('kode produk wajib diisi');
        }
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('nama produk wajib diisi');
        }
        if ($entity->price_buy < 0) {
            throw new \InvalidArgumentException('harga beli dasar tidak boleh negatif');
        }
        if ($entity->min_stock < 0) {
            throw new \InvalidArgumentException('stok minimum tidak boleh negatif');
        }

        return $this->productRepository->save($entity, $extraData);
    }

    /**
     * Hapus produk dengan pengecekan riwayat aplikasi.
     */
    public function destroy(ProductEntity $entity): array
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id produk tidak valid');
        }

        if ($this->productRepository->hasTransactions($entity->uuid)) {
            return [
                'status' => 'error',
                'message' => 'Produk sudah digunakan dalam transaksi, tidak bisa dihapus.',
            ];
        }

        $this->productRepository->delete($entity);

        return [
            'status' => 'success',
            'message' => 'Produk dihapus.',
        ];
    }

    /**
     * Export data ke array untuk Controller.
     */
    public function export(ProductEntity $entity): array
    {
        return $this->productRepository->listForExport($entity);
    }

    /**
     * Template Excel untuk import produk.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Barang Jasa',
            'headers' => ['Kode Item', 'Nama Item', 'Barcode', 'Stok Minimum', 'Harga Beli Dasar', 'Harga Jual Dasar', 'Aktif'],
            'sample_row' => ['BRG-001', 'Paracetamol 500 mg', '899100001', 10, 500, 1000, true],
            'guide' => [
                '1. Unique key import adalah Kode Item. Jika kode sudah ada, sistem akan melakukan upsert.',
                '2. Nama Item wajib diisi sesuai nama barang/jasa yang tampil ke user.',
                '3. Barcode boleh dikosongkan jika item belum memiliki barcode.',
                '4. Stok Minimum, Harga Beli Dasar, dan Harga Jual Dasar diisi angka tanpa pemisah ribuan.',
                '5. Aktif diisi true/false atau aktif/nonaktif.',
            ],
        ];
    }

    /**
     * Import data dari array hasil parsing Excel.
     */
    public function importRows(array $rows, string $branchUuid, string $companyUuid): array
    {
        if (count($rows) === 0) {
            throw new \InvalidArgumentException('file import tidak boleh kosong');
        }

        $summary = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $i => $row) {
            if ($i === 0 || count($row) < 2) {
                continue;
            }

            $code = trim((string) ($row[0] ?? ''));
            if ($code === '') {
                continue;
            }

            $summary['total']++;

            try {
                $existing = $this->productRepository->getByCode(new ProductEntity(
                    code: $code,
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                ));
                
                $this->save(new ProductEntity(
                    uuid: $existing?->uuid,
                    code: $code,
                    name: trim((string) ($row[1] ?? '')),
                    barcode: $row[2] ?? null,
                    min_stock: (float) ($row[3] ?? 0),
                    price_buy: (float) ($row[4] ?? 0),
                    price_sell: (float) ($row[5] ?? 0),
                    is_active: filter_var($row[6] ?? true, FILTER_VALIDATE_BOOLEAN),
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                ));
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return $summary;
    }
}
