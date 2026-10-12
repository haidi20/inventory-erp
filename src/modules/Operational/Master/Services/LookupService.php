<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\LookupDocumentEntity;
use App\Modules\Operational\Master\Entities\LookupImportEntity;
use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupTypeListResultEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use App\Modules\Operational\Master\Entities\LookupValueListResultEntity;
use App\Modules\Operational\Master\Repositories\LookupRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * LookupService
 *
 * Port dari BE: lookup_service.go. Mengelola data referensi (LookupType &
 * LookupValue) — CRUD, halaman data, serta template/import/export Excel.
 */
class LookupService
{
    public function __construct(protected LookupRepositoryInterface $lookupRepository) {}

    public function getTypes(): LookupTypeListResultEntity
    {
        return Cache::remember('lookup_types', 3600, fn () => $this->lookupRepository->getTypes());
    }

    public function getTypeByCode(LookupTypeEntity $entity): ?LookupTypeEntity
    {
        $typeCode = trim((string) $entity->code);
        if ($typeCode === '') {
            throw new \InvalidArgumentException('Kode tipe lookup wajib diisi.');
        }
        return $this->lookupRepository->getTypeByCode(
            LookupTypeEntity::fromArray([...get_object_vars($entity), 'code' => $typeCode, 'company_uuid' => $entity->company_uuid])
        );
    }

    public function getValues(LookupTypeEntity $entity): LookupValueListResultEntity
    {
        $typeCode = trim((string) $entity->code);
        if ($typeCode === '') {
            throw new \InvalidArgumentException('Kode tipe lookup wajib diisi.');
        }
        $cacheKey = 'lookup_values_' . $typeCode . '_' . ($entity->company_uuid ?? 'global');

        return Cache::remember($cacheKey, 3600, fn () => $this->lookupRepository->getValuesByType(
            LookupTypeEntity::fromArray([...get_object_vars($entity), 'code' => $typeCode, 'company_uuid' => $entity->company_uuid])
        ));
    }

    public function storeType(LookupTypeEntity $input): LookupTypeEntity
    {
        $code = strtoupper(trim((string) ($input->code ?? '')));
        $name = trim((string) ($input->name ?? ''));
        $description = trim((string) ($input->description ?? ''));

        if ($code === '') {
            throw new \InvalidArgumentException('Kode tipe lookup wajib diisi.');
        }
        if ($name === '') {
            throw new \InvalidArgumentException('Nama tipe lookup wajib diisi.');
        }
        if ($this->lookupRepository->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => $code,
            'company_uuid' => $input->company_uuid,
        ])) !== null) {
            throw new \InvalidArgumentException('Kode tipe lookup sudah digunakan.');
        }

        $entity = new LookupTypeEntity(
            uuid: trim((string) ($input->uuid ?? '')) ?: (string) Str::uuid(),
            code: $code,
            name: $name,
            description: $description,
            company_uuid: $input->company_uuid,
            actor_uuid: $input->actor_uuid,
        );

        return $this->lookupRepository->saveType($entity);
    }

    public function storeValue(LookupValueEntity $input): LookupValueEntity
    {
        $typeId = trim((string) ($input->lookup_type_uuid ?? ''));
        $code = strtoupper(trim((string) ($input->code ?? '')));
        $value = trim((string) ($input->value ?? ''));
        $description = trim((string) ($input->description ?? ''));

        if ($typeId === '') {
            throw new \InvalidArgumentException('Tipe lookup wajib diisi.');
        }
        if ($code === '') {
            throw new \InvalidArgumentException('Kode nilai lookup wajib diisi.');
        }
        if ($value === '') {
            throw new \InvalidArgumentException('Label nilai lookup wajib diisi.');
        }
        if ($this->lookupRepository->getValueByCode(LookupValueEntity::fromArray([
            'lookup_type_uuid' => $typeId,
            'code' => $code,
            'company_uuid' => $input->company_uuid,
        ])) !== null) {
            throw new \InvalidArgumentException('Kode nilai untuk tipe ini sudah digunakan.');
        }

        $entity = new LookupValueEntity(
            uuid: trim((string) ($input->uuid ?? '')) ?: (string) Str::uuid(),
            lookup_type_uuid: $typeId,
            code: $code,
            value: $value,
            description: $description,
            order_priority: $input->order_priority,
            company_uuid: $input->company_uuid,
            actor_uuid: $input->actor_uuid,
        );

        return $this->lookupRepository->saveValue($entity);
    }

    public function updateValue(LookupValueEntity $input): LookupValueEntity
    {
        $uuid = trim((string) ($input->uuid ?? ''));
        $value = trim((string) ($input->value ?? ''));
        if ($uuid === '') {
            throw new \InvalidArgumentException('ID nilai lookup wajib diisi.');
        }
        if ($value === '') {
            throw new \InvalidArgumentException('Label nilai lookup wajib diisi.');
        }

        $existing = $this->lookupRepository->getValueByUuid(LookupValueEntity::fromArray(['uuid' => $uuid]));
        if ($existing === null) {
            throw new \RuntimeException('Data nilai lookup tidak ditemukan.');
        }

        $entity = new LookupValueEntity(
            uuid: $uuid,
            lookup_type_uuid: (string) ($existing->lookup_type_uuid ?? $input->lookup_type_uuid ?? ''),
            code: (string) ($existing->code ?? $input->code ?? ''),
            value: $value,
            description: trim((string) ($input->description ?? '')),
            order_priority: (int) ($input->order_priority ?: ($existing->order_priority ?? 0)),
            company_uuid: $input->company_uuid ?: $existing->company_uuid,
            actor_uuid: $input->actor_uuid,
        );

        return $this->lookupRepository->saveValue($entity);
    }

    public function deleteType(string $uuid): void
    {
        $this->lookupRepository->deleteType(trim($uuid));
    }

    public function deleteValue(LookupValueEntity $entity): void
    {
        $uuid = trim((string) $entity->uuid);
        if ($uuid === '') {
            throw new \InvalidArgumentException('ID nilai lookup tidak valid.');
        }
        $this->lookupRepository->deleteValue($uuid);
    }

    public function getTypesPage(LookupTypeEntity $filter): LookupTypeListResultEntity
    {
        $column = strtolower(trim((string) ($filter->search_column ?? '')));
        if (!in_array($column, ['code', 'name', 'description'], true)) {
            $column = 'all';
        }
        $entity = new LookupTypeEntity(
            search: trim((string) ($filter->search ?? '')),
            search_column: $column,
            page: $filter->page,
            per_page: $filter->per_page,
            company_uuid: $filter->company_uuid,
        );
        return $this->lookupRepository->getTypesPageData($entity);
    }

    public function getValuesPage(LookupValueEntity $filter): LookupValueListResultEntity
    {
        $typeId = trim((string) ($filter->lookup_type_uuid ?? ''));
        if ($typeId === '') {
            throw new \InvalidArgumentException('Tipe lookup wajib ditentukan.');
        }
        $column = strtolower(trim((string) ($filter->search_column ?? '')));
        if (!in_array($column, ['code', 'value', 'description'], true)) {
            $column = 'all';
        }
        $entity = new LookupValueEntity(
            lookup_type_uuid: $typeId,
            search: trim((string) ($filter->search ?? '')),
            search_column: $column,
            page: $filter->page,
            per_page: $filter->per_page,
            company_uuid: $filter->company_uuid,
        );
        return $this->lookupRepository->getValuesPageData($entity);
    }

    public function exportValues(LookupTypeEntity $entity): LookupDocumentEntity
    {
        $typeCode = trim((string) $entity->code);
        if ($typeCode === '') {
            throw new \InvalidArgumentException('Kode tipe lookup wajib diisi.');
        }
        $values = $this->lookupRepository->getValuesByType(LookupTypeEntity::fromArray([
            'code' => $typeCode,
            'company_uuid' => $entity->company_uuid,
        ]));
        return new LookupDocumentEntity(
            sheet: 'Data Lookup',
            headers: ['Kode', 'Nilai/Label', 'Urutan', 'Deskripsi', 'Status Aktif'],
            items: $values->items,
            guide: [
                '1. Kolom Kode: Harus unik dan tidak boleh kosong.',
                '2. Kolom Nilai/Label: Teks yang akan muncul di pilihan (misal: Laki-laki).',
                '3. Kolom Urutan: Angka untuk menentukan posisi tampilan (1, 2, 3...).',
                '4. Kolom Deskripsi: Informasi tambahan (opsional).',
                '5. Kolom Status Aktif: Diisi true untuk aktif, false untuk non-aktif.',
            ],
        );
    }

    /**
     * Import values — controller/Import class wajib parse Excel menjadi array rows.
     * Baris pertama header; kolom A-E = Kode, Value, OrderPriority, Description, IsActive.
     */
    public function importValues(LookupImportEntity $entity): void
    {
        $typeCode = trim((string) $entity->type_code);
        $rows = $entity->rows;
        if ($typeCode === '') {
            throw new \InvalidArgumentException('Kode tipe lookup wajib diisi.');
        }
        if (empty($rows)) {
            throw new \InvalidArgumentException('File import tidak boleh kosong.');
        }

        $type = $this->lookupRepository->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => $typeCode,
            'company_uuid' => $entity->company_uuid,
        ]));
        if ($type === null) {
            throw new \RuntimeException('Tipe lookup tidak ditemukan.');
        }

            foreach ($rows as $i => $row) {
                if ($i === 0 || count($row) < 2) {
                    continue;
                }
                $code = strtoupper(trim((string) ($row[0] ?? '')));
                $value = trim((string) ($row[1] ?? ''));
                if ($code === '' || $value === '') {
                    continue;
                }

                $existing = $this->lookupRepository->getValueByCode(LookupValueEntity::fromArray([
                    'lookup_type_uuid' => $type->uuid,
                    'code' => $code,
                    'company_uuid' => $entity->company_uuid,
                ]));
                if ($existing !== null) {
                    $entity = new LookupValueEntity(
                        uuid: $existing->uuid,
                        lookup_type_uuid: $type->uuid,
                        lookup_type_uuid: $type->uuid,
                        code: $code,
                        value: $value,
                        description: trim((string) ($row[3] ?? '')),
                        order_priority: (int) ($row[2] ?? 0),
                        company_uuid: $entity->company_uuid,
                    );
                    $this->lookupRepository->saveValue($entity);
                } else {
                    $entity = new LookupValueEntity(
                        uuid: (string) Str::uuid(),
                        lookup_type_uuid: $type->uuid,
                        code: $code,
                        value: $value,
                        description: trim((string) ($row[3] ?? '')),
                        order_priority: (int) ($row[2] ?? 0),
                        company_uuid: $entity->company_uuid,
                    );
                    $this->lookupRepository->saveValue($entity);
                }
            }
    }

    public function getImportTemplate(): LookupDocumentEntity
    {
        return new LookupDocumentEntity(
            sheet: 'Data Lookup',
            headers: ['Kode', 'Nilai/Label', 'Urutan', 'Deskripsi', 'Status Aktif'],
            sample_row: ['EXAMPLE_01', 'Contoh Nilai 1', 1, 'Deskripsi contoh', true],
            guide: [
                '1. Kolom Kode: Harus unik dan tidak boleh kosong.',
                '2. Kolom Nilai/Label: Teks yang akan muncul di pilihan (misal: Laki-laki).',
                '3. Kolom Urutan: Angka untuk menentukan posisi tampilan (1, 2, 3...).',
                '4. Kolom Deskripsi: Informasi tambahan (opsional).',
                '5. Kolom Status Aktif: Diisi true untuk aktif, false untuk non-aktif.',
            ],
        );
    }
}
