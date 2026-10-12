<?php

namespace App\Modules\Operational\Manufacturing\Services;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Operational\Manufacturing\Repositories\AssemblyRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;

/**
 * AssemblyService
 *
 * Port dari BE: assembly_service.go. Mengelola perakitan (Assembly / Bill of
 * Materials produksi) — CRUD, posting ke stok, dan hook import/export Excel.
 */
class AssemblyService
{
    public function __construct(
        protected AssemblyRepositoryInterface $assemblyRepository,
        protected ?StockRepositoryInterface $stockRepository = null,
    ) {}

    public function index(AssemblyEntity $entity): array
    {
        return $this->assemblyRepository->index($entity);
    }

    public function getByUuid(string $uuid): ?AssemblyEntity
    {
        return $this->assemblyRepository->findByID($uuid);
    }

    public function save(AssemblyEntity $entity): ?AssemblyEntity
    {
        return $this->assemblyRepository->save($entity);
    }

    public function delete(string $uuid): bool
    {
        return $this->assemblyRepository->delete($uuid);
    }

    /**
     * Post assembly — menandai status "posted" sebagai trigger stock movement.
     */
    public function post(string $uuid): ?AssemblyEntity
    {
        $assembly = $this->assemblyRepository->findByID($uuid);
        if (!$assembly) {
            return null;
        }

        $assembly->status = 'posted';
        return $this->assemblyRepository->save($assembly);
    }

    /** Export seluruh assembly sesuai filter. */
    public function export(AssemblyEntity $entity): array
    {
        $data = $this->assemblyRepository->index($entity);
        return [
            'sheet' => 'Data Perakitan',
            'headers' => ['No Dokumen', 'Tanggal', 'Gudang', 'Status', 'Total Item'],
            'items' => $data['items']
        ];
    }

    /** Import assembly dari array rows. */
    public function importRows(array $rows, string $branchUuid, string $companyUuid): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $entities = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (empty($row[0])) {
                continue;
            }

            $entities[] = AssemblyEntity::fromArray([
                'assembly_no' => $row[0],
                'date' => $row[1] ?? date('Y-m-d'),
                'location_uuid' => $row[2],
                'item_uuid' => $row[3],
                'unit_uuid' => $row[4],
                'qty' => $row[5],
                'description' => $row[6] ?? '',
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
            ]);
        }

        return $this->assemblyRepository->saveMany($entities);
    }
}
