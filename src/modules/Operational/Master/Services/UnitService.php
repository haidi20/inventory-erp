<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Operational\Master\Repositories\UnitRepositoryInterface;

/**
 * UnitService
 * 
 * Port dari BE: unit_service.go. Mengelola data Master Satuan (UOM).
 */
class UnitService
{
    public function __construct(protected UnitRepositoryInterface $unitRepository)
    {
    }

    /**
     * Daftar Satuan dengan pagination dan filter.
     */
    public function index(UnitEntity $entity): array
    {
        return $this->unitRepository->getPageData($entity);
    }

    /**
     * Ambil detail satuan berdasarkan UUID.
     */
    public function getByUuid(UnitEntity $entity): ?UnitEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        return $this->unitRepository->findByUuid($entity);
    }

    /**
     * Simpan data satuan (Create/Update) dengan validasi kode unik.
     */
    public function save(UnitEntity $entity): UnitEntity
    {
        if (empty($entity->code)) {
            throw new \InvalidArgumentException('kode satuan wajib diisi');
        }
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('nama satuan wajib diisi');
        }

        // Check for duplicates
        $existing = $this->unitRepository->getByCode($entity);
        if ($existing !== null && $existing->uuid !== $entity->uuid) {
            throw new \InvalidArgumentException("kode satuan '{$entity->code}' sudah digunakan oleh satuan lain");
        }

        return $this->unitRepository->save($entity);
    }

    /**
     * Hapus satuan (Soft Delete).
     */
    public function destroy(UnitEntity $entity): void
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id satuan tidak valid');
        }
        $this->unitRepository->delete($entity);
    }

    /**
     * Template Excel untuk import satuan.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Satuan',
            'headers' => ['Kode Satuan', 'Nama Satuan', 'Simbol', 'Keterangan', 'Aktif'],
            'sample_row' => ['PCS', 'Pieces', 'pcs', 'Satuan terkecil', true],
            'guide' => [
                '1. Unique key import adalah Kode Satuan. Jika kode sudah ada, sistem akan melakukan upsert.',
                '2. Nama Satuan wajib diisi.',
                '3. Aktif diisi true/false atau aktif/nonaktif.',
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
                $isActive = true;
                if (isset($row[4])) {
                    $val = strtolower((string) $row[4]);
                    $isActive = $val === 'true' || $val === '1' || $val === 'aktif' || $val === 'ya';
                }

                $existing = $this->unitRepository->getByCode(new UnitEntity(code: $code, branch_uuid: $branchUuid, company_uuid: $companyUuid));
                
                $this->save(new UnitEntity(
                    uuid: $existing?->uuid,
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                    code: $code,
                    name: trim((string) ($row[1] ?? '')),
                    symbol: trim((string) ($row[2] ?? '')),
                    description: trim((string) ($row[3] ?? '')),
                    is_active: $isActive,
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
