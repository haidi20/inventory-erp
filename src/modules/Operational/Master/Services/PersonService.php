<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\PersonEntity;
use App\Modules\Operational\Master\Repositories\PersonRepositoryInterface;

/**
 * PersonService
 * 
 * Port dari BE: person_service.go. Mengelola data Master Person (Customer/Supplier background).
 */
class PersonService
{
    public function __construct(protected PersonRepositoryInterface $personRepository)
    {
    }

    /**
     * Daftar Person dengan pagination dan filter.
     */
    public function index(PersonEntity $entity): array
    {
        return $this->personRepository->getPageData($entity);
    }

    /**
     * Ambil detail person berdasarkan UUID.
     */
    public function getByUuid(PersonEntity $entity): ?PersonEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        $person = $this->personRepository->findByUuid($entity->uuid, $entity->branch_uuid ?? '', $entity->company_uuid ?? '');
        return $person ? PersonEntity::fromModel($person) : null;
    }

    /**
     * Simpan data person (Create/Update) dengan validasi kode unik.
     */
    public function save(PersonEntity $entity, array $customFields = []): PersonEntity
    {
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('nama person wajib diisi');
        }
        if (empty($entity->code)) {
            throw new \InvalidArgumentException('kode person wajib diisi');
        }

        $existing = $this->personRepository->getByCode($entity->code);

        if ($existing !== null && $existing->uuid !== $entity->uuid) {
            throw new \InvalidArgumentException('kode person sudah digunakan');
        }

        if ($customFields !== []) {
            $entity->custom_fields = $customFields;
        }

        $person = $this->personRepository->save($entity);
        return $person instanceof PersonEntity ? $person : PersonEntity::fromModel($person);
    }

    /**
     * Hapus person (Soft Delete).
     */
    public function destroy(PersonEntity $entity): void
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id person tidak valid');
        }
        
        $this->personRepository->delete($entity);
    }

    /**
     * Export data ke array (untuk diolah layer Controller/Excel).
     */
    public function export(PersonEntity $entity): array
    {
        return $this->personRepository->listForExport($entity);
    }

    /**
     * Template Excel untuk import person.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Person',
            'headers' => ['Kode', 'Nama', 'Tipe', 'Alamat (Custom1)', 'NPWP', 'Aktif'],
            'sample_row' => ['C001', 'Pelanggan Umum', 'Customer', 'Jl. Raya No. 1', '00.000.000.0-000.000', 'aktif'],
            'guide' => [
                '1. Kode wajib unik dan diisi.',
                '2. Nama wajib diisi.',
                '3. Tipe diisi: Customer atau Supplier.',
                '4. Alamat akan disimpan di field kustom 1 (Custom1).',
            ],
        ];
    }

    /**
     * Import data dari array hasil parsing Excel.
     */
    public function importRows(array $rows, string $branchUuid, string $companyUuid): array
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
            if ($i === 0 || count($row) < 3) {
                continue;
            }

            $code = strtoupper(trim((string) ($row[0] ?? '')));
            $name = trim((string) ($row[1] ?? ''));
            $typeStr = strtolower(trim((string) ($row[2] ?? '')));
            
            if ($code === '' || $name === '') {
                continue;
            }

            $summary['total']++;

            try {
                $addr = trim((string) ($row[3] ?? ''));
                $npwp = trim((string) ($row[4] ?? ''));
                
                $active = true;
                if (isset($row[5])) {
                    $v = strtolower((string) $row[5]);
                    $active = $v === 'aktif' || $v === '1' || $v === 'true';
                }

                $existing = $this->personRepository->getByCode($code);
                
                $entity = new PersonEntity(
                    uuid: $existing?->uuid,
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                    code: $code,
                    name: $name,
                    is_supplier: str_contains($typeStr, 'supp'),
                    npwp_no: $npwp,
                    is_active: $active,
                );

                $this->save($entity, [
                    [
                        'field_key' => 'Custom1',
                        'field_value' => $addr,
                        'field_type' => 'string',
                    ],
                ]);
                
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return $summary;
    }
}
