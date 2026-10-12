<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Operational\Master\Repositories\TaxRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * TaxService
 * 
 * Port dari BE: tax_service.go. Mengelola Master Pajak (Tax Settings).
 */
class TaxService
{
    public function __construct(protected TaxRepositoryInterface $taxRepository)
    {
    }

    /**
     * Daftar Pajak dengan pagination dan filter.
     */
    public function index(TaxEntity $entity): Collection
    {
        $result = $this->taxRepository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    /**
     * Ambil detail pajak berdasarkan UUID.
     */
    public function show(TaxEntity $entity): ?TaxEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        return $this->taxRepository->findByUuid($entity);
    }

    /**
     * Simpan data pajak.
     */
    public function save(TaxEntity $entity): TaxEntity
    {
        if (empty($entity->tax_code)) {
            throw new \InvalidArgumentException('kode pajak wajib diisi');
        }

        return $this->taxRepository->save($entity);
    }

    /**
     * Hapus pajak.
     */
    public function destroy(TaxEntity $entity): array
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id pajak tidak valid');
        }

        $this->taxRepository->delete($entity);

        return ['message' => 'Pajak berhasil dihapus'];
    }

    /**
     * Export data ke array (untuk diolah layer Controller/Excel).
     */
    public function export(TaxEntity $entity): array
    {
        return $this->taxRepository->listForExport($entity);
    }

    /**
     * Template Excel untuk import pajak.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Pajak',
            'headers' => ['Kode Pajak', 'Persentase', 'Aktif', 'Utama', 'PPH'],
            'sample_row' => ['PPN11', 11, 'aktif', 'ya', 'tidak'],
            'guide' => [
                '1. Kode Pajak wajib diisi dan unik.',
                '2. Persentase angka desimal (titik).',
                '3. Kolom Aktif/Utama/PPH diisi: aktif/tidak atau ya/tidak atau true/false.',
            ],
        ];
    }

    /**
     * Import data dari array hasil parsing Excel.
     */
    public function importRows(array $rows, string $branchUuid = '', string $companyUuid = ''): array
    {
        if (count($rows) === 0) {
            throw new \InvalidArgumentException('file tidak boleh kosong');
        }

        $summary = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $i => $row) {
            if ($i === 0 || count($row) < 1) {
                continue;
            }

            $code = strtoupper(trim((string) ($row[0] ?? '')));
            if ($code === '') {
                continue;
            }

            $summary['total']++;

            try {
                $percent = (float) ($row[1] ?? 0);
                
                $active = true;
                if (isset($row[2])) {
                    $v = strtolower((string) $row[2]);
                    $active = $v === 'aktif' || $v === 'ya' || $v === 'true' || $v === '1';
                }

                $primary = false;
                if (isset($row[3])) {
                    $v = strtolower((string) $row[3]);
                    $primary = $v === 'aktif' || $v === 'ya' || $v === 'true' || $v === '1';
                }

                $pph = false;
                if (isset($row[4])) {
                    $v = strtolower((string) $row[4]);
                    $pph = $v === 'aktif' || $v === 'ya' || $v === 'true' || $v === '1';
                }

                $this->save(new TaxEntity(
                    tax_code: $code,
                    percentage: $percent,
                    is_active: $active,
                    is_primary: $primary,
                    is_income_tax: $pph,
                    branch_uuid: $branchUuid ?: null,
                    company_uuid: $companyUuid ?: null,
                ));
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return $summary;
    }

    public function getPageData(TaxEntity $entity): array
    {
        return $this->taxRepository->getPageData($entity);
    }

    public function findByUuid(TaxEntity $entity): ?TaxEntity
    {
        return $this->taxRepository->findByUuid($entity);
    }

    public function delete(TaxEntity $entity): void
    {
        $this->taxRepository->delete($entity);
    }
}
