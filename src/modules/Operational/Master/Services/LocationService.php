<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Core\Identity\Entities\ActionResponseEntity;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Operational\Master\Repositories\LocationRepositoryInterface;

class LocationService
{
    public function __construct(
        protected LocationRepositoryInterface $repo,
        protected BranchService $branchService
    ) {}

    /**
     * Mendapatkan daftar gudang yang aktif secara operasional.
     */
    public function getActiveLocations(): array
    {
        return $this->repo->getActiveLocations();
    }

    /**
     * Daftar Gudang dengan pagination dan filter.
     */
    public function index(LocationEntity $entity): array
    {
        return $this->repo->getPageData($entity);
    }

    /**
     * Ambil detail gudang berdasarkan UUID.
     */
    public function getByUuid(LocationEntity $entity): ?LocationEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        return $this->repo->findByUuid($entity);
    }

    /**
     * Simpan data gudang (Create/Update) dengan validasi.
     */
    public function save(LocationEntity $entity): LocationEntity
    {
        if (empty($entity->branch_uuid)) {
            if (empty($entity->code) || empty($entity->name)) {
                throw new \InvalidArgumentException('cabang gudang wajib diisi');
            }
            $branch = $this->branchService->findByUuid($this->branchService->findFirstBranchUuid($entity->company_uuid));
            if ($branch) {
                $entity->branch_uuid = $branch->uuid;
            } else {
                throw new \InvalidArgumentException('cabang gudang wajib diisi');
            }
        }

        if (empty($entity->code)) {
            throw new \InvalidArgumentException('kode gudang wajib diisi');
        }
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('nama gudang wajib diisi');
        }

        $existing = $this->repo->getByBranchAndCode($entity);
        
        if ($existing !== null && $existing->uuid !== $entity->uuid) {
            throw new \InvalidArgumentException('kode gudang pada cabang ini sudah digunakan');
        }

        return $this->repo->save($entity);
    }

    /**
     * Hapus gudang (Soft Delete) dengan validasi shift.
     */
    public function destroy(LocationEntity $entity): ActionResponseEntity
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id gudang tidak valid');
        }

        $hasShifts = $this->repo->hasShifts($entity->uuid);
        if ($hasShifts) {
            return new ActionResponseEntity(
                success: false,
                status: 'error',
                message: 'Gudang sudah digunakan pada shift, tidak bisa dihapus.'
            );
        }

        $this->repo->delete($entity);

        return new ActionResponseEntity(
            success: true,
            status: 'success',
            message: 'Gudang berhasil dihapus.'
        );
    }

    /**
     * Export data ke Excel format.
     */
    public function export(LocationEntity $entity): array
    {
        return $this->repo->listForExport($entity);
    }

    /**
     * Template Excel untuk import gudang.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Gudang',
            'headers' => ['Kode Cabang', 'Kode Gudang', 'Nama Gudang', 'Keterangan', 'Aktif'],
            'sample_row' => ['KDM', 'G01', 'Gudang Utama', 'Penyimpanan stok utama', 'aktif'],
        ];
    }

    /**
     * Import data dari array rows hasil Excel.
     */
    public function importRows(array $rows, string $companyUuid): array
    {
        $summary = ['total' => 0, 'success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $i => $row) {
            if ($i === 0 || count($row) < 3) continue;

            $branchCode = trim((string) ($row[0] ?? ''));
            $code = strtoupper(trim((string) ($row[1] ?? '')));

            if ($branchCode === '' || $code === '') continue;
            $summary['total']++;

            try {
                $branch = $this->repo->getBranchByShortName($branchCode);
                if (!$branch) throw new \Exception("Cabang dengan kode '{$branchCode}' tidak ditemukan.");

                $branchUuid = is_array($branch) ? $branch['uuid'] : $branch->uuid;

                $existing = $this->repo->getByBranchAndCode(new LocationEntity(branch_uuid: $branchUuid, code: $code));
                
                $isActive = true;
                if (isset($row[4])) {
                    $v = strtolower((string) $row[4]);
                    $isActive = in_array($v, ['aktif', 'ya', 'true', '1']);
                }

                $this->save(new LocationEntity(
                    uuid: $existing?->uuid,
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                    code: $code,
                    name: trim((string) ($row[2] ?? '')),
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
