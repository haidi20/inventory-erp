<?php

namespace App\Modules\Operational\Manufacturing\Services;

use App\Modules\Operational\Manufacturing\Repositories\BomRepositoryInterface;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;

/**
 * BomService
 *
 * Port dari BE: bom_service.go. Mengelola Formula / Bill of Materials (BOM):
 * header (item hasil + kuantitas), detail (bahan baku), dan biaya tambahan.
 */
class BomService
{
    public function __construct(protected BomRepositoryInterface $bomRepository) {}

    public function index(BomEntity $entity): array
    {
        return $this->bomRepository->getPageData($entity);
    }

    public function getByUuid(string $uuid): ?BomEntity
    {
        return $this->bomRepository->findByUUID($uuid);
    }

    /**
     * Simpan Formula (create atau update) dengan seluruh validasi BE
     */
    public function save(BomEntity $entity): ?BomEntity
    {
        if (empty($entity->code)) {
            throw new \InvalidArgumentException('Nomor formula (document no) wajib diisi.');
        }
        if (empty($entity->item_uuid)) {
            throw new \InvalidArgumentException('Item hasil produksi (finished good) wajib dipilih.');
        }
        if ($entity->qty <= 0) {
            throw new \InvalidArgumentException('Kuantitas produksi harus lebih besar dari nol.');
        }

        if (empty($entity->items)) {
            throw new \InvalidArgumentException('Rincian bahan baku minimal harus ada satu.');
        }

        $seen = [];
        foreach ($entity->items as $d) {
            $detailItem = trim((string) ($d['item_uuid'] ?? ''));
            if ($detailItem === '') {
                throw new \InvalidArgumentException('Item bahan baku harus dipilih.');
            }
            if ($detailItem === $entity->item_uuid) {
                throw new \InvalidArgumentException('Item bahan baku tidak boleh sama dengan item hasil produksi.');
            }
            if (isset($seen[$detailItem])) {
                throw new \InvalidArgumentException('Item bahan baku tidak boleh ganda dalam satu formula.');
            }
            $seen[$detailItem] = true;

            if (((float) ($d['qty'] ?? 0)) < 0) {
                throw new \InvalidArgumentException('Kuantitas bahan tidak boleh negatif.');
            }
        }

        return $this->bomRepository->save($entity);
    }

    public function destroy(string $uuid, string $actorUuid): array
    {
        $this->bomRepository->delete($uuid, $actorUuid);
        return [
            'status'  => 'success',
            'message' => 'Formula berhasil dihapus.',
        ];
    }

    /** Export Excel */
    public function export(BomEntity $entity): array
    {
        return $this->bomRepository->listForExport($entity);
    }

    /** Template Excel import */
    public function getImportTemplate(): array
    {
        return [
            'headers' => [
                'No Formula', 'Item Hasil Code', 'Satuan Hasil Code', 'Qty Hasil',
                'No Ref', 'Keterangan', 'Primer', 'Aktif',
            ],
            'sample_row' => ['F-001', 'I-00012', 'PCS', 1, 'REF-001', 'Contoh formula', true, true],
        ];
    }

    /**
     * Import Excel rows
     */
    public function importRows(array $rows, string $actorUuid, string $branchUuid, string $companyUuid = ''): array
    {
        $success = 0;
        $total = 0;

        $entities = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (count($row) < 2) {
                continue;
            }

            $docNo = trim((string) ($row[0] ?? ''));
            $itemCode = trim((string) ($row[1] ?? ''));
            if ($docNo === '' || $itemCode === '') {
                continue;
            }

            $existing = $this->bomRepository->getByDocumentNo($docNo, $companyUuid);
            $entities[] = BomEntity::fromArray([
                'uuid'         => $existing?->uuid,
                'code'         => $docNo,
                'item_uuid'    => $itemCode,
                'unit_uuid'    => trim((string) ($row[2] ?? '')),
                'qty'          => (float) ($row[3] ?? 1),
                'description'  => (string) ($row[5] ?? ''),
                'is_active'    => filter_var($row[7] ?? true, FILTER_VALIDATE_BOOLEAN),
                'actor_uuid'   => $actorUuid,
                'branch_uuid'  => $branchUuid,
                'company_uuid' => $companyUuid,
                'items'        => [],
            ]);
        }

        $result = $this->bomRepository->saveMany($entities);

        return [
            'total'   => $result['total'],
            'success' => $result['success'],
        ];
    }

    /**
     * Simpan BOM tanpa validasi ketat (untuk flow Controller simple store).
     * Controller tidak selalu mengirim code atau items.
     */
    public function saveSimple(BomEntity $entity): ?BomEntity
    {
        return $this->bomRepository->save($entity);
    }
}
