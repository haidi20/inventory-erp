<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\LookupDocumentEntity;
use App\Modules\Operational\Master\Entities\LookupImportEntity;
use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Entities\LookupSetListResultEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueListResultEntity;
use App\Modules\Operational\Master\Repositories\LookupSetRepositoryInterface;

/**
 * LookupSetService
 *
 * Mengelola master kategori dan nilai lookup (LookupSet + LookupValue)
 * beserta staging (TempLookup) untuk proses import.
 */
class LookupSetService
{
    public function __construct(protected LookupSetRepositoryInterface $lookupSetRepository) {}

    // ---------------------------------------------------------------
    // LookupSet (kategori)
    // ---------------------------------------------------------------

    public function indexSet(LookupSetEntity $filter): LookupSetListResultEntity
    {
        $entity = $this->buildSetEntity($filter);
        $data = $this->lookupSetRepository->getSetIndexData($entity);

        return new LookupSetListResultEntity(
            items: $data->items,
            total: $data->total,
            filtered: $data->filtered,
            draw: (int) $filter->start
        );
    }

    public function storeSet(LookupSetEntity $input): LookupSetEntity
    {
        if (trim((string) ($input->name ?? '')) === '') {
            throw new \InvalidArgumentException('Nama kategori wajib diisi.');
        }
        $entity = $this->buildSetEntity($input);
        return $this->lookupSetRepository->storeSet($entity);
    }

    public function showSet(LookupSetEntity $entity): ?LookupSetEntity
    {
        return $this->lookupSetRepository->getSetByUuid($entity);
    }

    public function updateSet(LookupSetEntity $input): LookupSetEntity
    {
        if (trim((string) ($input->name ?? '')) === '') {
            throw new \InvalidArgumentException('Nama kategori wajib diisi.');
        }
        $entity = $this->buildSetEntity($input);
        return $this->lookupSetRepository->updateSet($entity);
    }

    public function destroySet(string $uuid, ?string $companyUuid = null): void
    {
        $this->lookupSetRepository->destroySet(trim($uuid), $companyUuid);
    }

    // ---------------------------------------------------------------
    // LookupValue (nilai)
    // ---------------------------------------------------------------

    public function indexValue(LookupSetValueEntity $filter): LookupSetValueListResultEntity
    {
        $entity = $this->buildValueEntity($filter);
        $data = $this->lookupSetRepository->getValueIndexData($entity);
        return new LookupSetValueListResultEntity(
            items: $data->items,
            total: $data->total,
            filtered: $data->filtered,
            draw: (int) $filter->start
        );
    }

    public function storeValue(LookupSetValueEntity $input): LookupSetValueEntity
    {
        if (trim((string) ($input->display_name ?? '')) === '') {
            throw new \InvalidArgumentException('Nama tampilan wajib diisi.');
        }
        $rawSetId = trim((string) ($input->lookup_set_uuid ?? ''));
        $hasSetId = $rawSetId !== '' && $rawSetId !== '0';
        $hasSetUuid = $rawSetId !== '' && $rawSetId !== '0';
        if (!$hasSetId && !$hasSetUuid) {
            throw new \InvalidArgumentException('Kategori lookup tidak valid.');
        }
        $entity = $this->buildValueEntity($input);
        return $this->lookupSetRepository->storeValue($entity);
    }

    public function showValue(LookupSetValueEntity $entity): ?LookupSetValueEntity
    {
        return $this->lookupSetRepository->getValueByUuid($entity);
    }

    public function updateValue(LookupSetValueEntity $input): LookupSetValueEntity
    {
        if (trim((string) ($input->display_name ?? '')) === '') {
            throw new \InvalidArgumentException('Nama tampilan wajib diisi.');
        }
        $entity = $this->buildValueEntity($input);
        return $this->lookupSetRepository->updateValue($entity);
    }

    public function destroyValue(string $uuid, ?string $companyUuid = null): void
    {
        $this->lookupSetRepository->destroyValue(trim($uuid), $companyUuid);
    }

    public function getValuesBySetName(LookupSetEntity $entity): LookupSetValueListResultEntity
    {
        return $this->lookupSetRepository->getValuesBySetName($entity);
    }

    // ---------------------------------------------------------------
    // Excel — template / export / import / sync staging
    // ---------------------------------------------------------------

    public function getImportTemplate(): LookupDocumentEntity
    {
        return new LookupDocumentEntity(
            sheet: 'Daftar Nilai',
            headers: [
                'PROGRAM_CODE', 'DISPLAY_NAME', 'SEQ', 'DESCRIPTION', 'ACTIVE_FLAG',
                'PRICE_UP_BELI', 'DISKON_PILIH', 'PERSEN1', 'PERSEN2',
                'D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7', 'D8', 'D9', 'D10',
            ],
            sample_row: [
                'PC-001', 'CONTOH ITEM A', '1', 'Deskripsi contoh item', 'Y',
                '0', '0', '0', '0',
                'Detail 1', 'Detail 2', '', '', '', '', '', '', '', '',
            ],
            guide: [
                ['PANDUAN PENGISIAN DATA', '', ''],
                ['', '', ''],
                ['KOLOM', 'DESKRIPSI', 'KETERANGAN & CONTOH'],
                ['PROGRAM_CODE', 'Kode program / Kode unik', 'Opsional. Contoh: PRG001'],
                ['DISPLAY_NAME', 'Nama Tampilan', 'WAJIB. Nama yang muncul di pilihan. Contoh: AKTIF'],
                ['SEQ', 'Urutan', 'Angka untuk mengurutkan tampilan. Contoh: 1, 2, 3'],
                ['DESCRIPTION', 'Keterangan', 'Opsional. Penjelasan tambahan mengenai nilai ini.'],
                ['ACTIVE_FLAG', 'Status Aktif', 'Y (Aktif) atau N (Non-Aktif). Default: Y'],
                ['PRICE_UP_BELI', 'Markup Harga', 'Angka desimal. Contoh: 10.50'],
                ['DISKON_PILIH', 'Diskon Default', 'Angka desimal. Contoh: 5.00'],
                ['PERSEN1', 'Persen Parameter 1', 'Angka desimal. Contoh: 0.15'],
                ['PERSEN2', 'Persen Parameter 2', 'Angka desimal. Contoh: 0.20'],
                ['D1 s/d D10', 'Detail Tambahan', 'Digunakan untuk informasi spesifik kategori. Bisa diisi teks bebas.'],
                ['', '', ''],
                ['CATATAN PENTING:', '', ''],
                ['1. Jangan mengubah header (baris pertama) pada sheet "Daftar Nilai".', '', ''],
                ['2. Pastikan kolom DISPLAY_NAME tidak kosong.', '', ''],
                ['3. Sistem akan mengupdate data jika PROGRAM_CODE atau DISPLAY_NAME sudah ada (Upsert).', '', ''],
            ],
        );
    }

    public function exportSet(LookupSetEntity $filter): LookupDocumentEntity
    {
        $entity = $this->buildSetEntity($filter);
        $entity->length = 10000;
        $data = $this->lookupSetRepository->getSetIndexData($entity);
        return new LookupDocumentEntity(
            sheet: 'Kategori Lookup',
            headers: ['ID', 'KODE', 'NAMA', 'DESKRIPSI', 'EDITABLE'],
            items: $data->items,
            filename: 'kategori-lookup-' . now()->format('Ymd') . '.xlsx',
        );
    }

    public function getSetImportTemplate(): LookupDocumentEntity
    {
        return new LookupDocumentEntity(
            sheet: 'Lookup Set',
            headers: ['PROGRAM_CODE', 'NAME'],
            guide: [
                ['KOLOM', 'STATUS', 'KETERANGAN'],
                ['PROGRAM_CODE', 'Opsional', 'Kode internal unik. Jika sama dengan data existing, sistem akan update data tersebut.'],
                ['NAME', 'Wajib', 'Nama kategori lookup yang tampil di aplikasi.'],
            ],
            sample_row: ['MASTER-BARANG', 'Master Barang']
        );
    }

    public function importSet(LookupImportEntity $entity): void
    {
        $rows = $entity->rows;

        if (empty($rows)) {
            throw new \InvalidArgumentException('File import tidak boleh kosong.');
        }

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $programCode = strtoupper(trim((string) ($row[0] ?? '')));
                $name = trim((string) ($row[1] ?? ''));

                if ($name === '') {
                    continue;
                }

                $existing = $programCode !== ''
                    ? $this->lookupSetRepository->getSetByProgramCode(LookupSetEntity::fromArray([
                        'program_code' => $programCode,
                        'company_uuid' => $entity->company_uuid,
                    ]))
                    : $this->lookupSetRepository->getSetByName(LookupSetEntity::fromArray([
                        'name' => $name,
                        'company_uuid' => $entity->company_uuid,
                    ]));

                if ($existing !== null) {
                    $this->lookupSetRepository->updateSet(LookupSetEntity::fromArray([
                        'uuid' => $existing->uuid,
                        'name' => $name,
                        'program_code' => $programCode !== '' ? $programCode : $existing->program_code,
                        'company_uuid' => $entity->company_uuid,
                    ]));
                    continue;
                }

                $this->lookupSetRepository->storeSet(LookupSetEntity::fromArray([
                    'name' => $name,
                    'program_code' => $programCode,
                    'company_uuid' => $entity->company_uuid,
                ]));
            }
    }

    public function exportValue(LookupSetValueEntity $filter): LookupDocumentEntity
    {
        $entity = $this->buildValueEntity($filter);
        $entity->length = 10000;
        $data = $this->lookupSetRepository->getValueIndexData($entity);
        return new LookupDocumentEntity(
            sheet: 'Data Nilai',
            headers: ['NAMA', 'D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7', 'D8', 'D9', 'D10'],
            items: $data->items,
            filename: 'data-nilai-' . now()->format('Ymd') . '.xlsx',
        );
    }

    /**
     * Import nilai ke staging (TempLookup) lalu sync ke master.
     * Controller/Import class wajib parsing Excel menjadi array rows terlebih dulu.
     */
    public function importValue(LookupImportEntity $entity): void
    {
        $rawSetId = trim((string) $entity->lookup_set_uuid);
        $rows = $entity->rows;
        if (empty($rows)) {
            throw new \InvalidArgumentException('Tidak ada data untuk diimport.');
        }
        if ($rawSetId === '' || $rawSetId === '0') {
            throw new \InvalidArgumentException('lookup_set_uuid tidak valid.');
        }

        $this->lookupSetRepository->clearTempValues($rawSetId);

            foreach ($rows as $i => $row) {
                if ($i === 0 || count($row) < 2) {
                    continue;
                }
                $displayName = trim((string) ($row[1] ?? ''));
                if ($displayName === '') {
                    continue;
                }

                $details = [];
                // Kolom 9..18 dipetakan ke detail index 1..10
                for ($j = 1; $j <= 10; $j++) {
                    $colIdx = $j + 8;
                    $val = trim((string) ($row[$colIdx] ?? ''));
                    if ($val !== '') {
                        $details[] = ['index' => $j, 'value' => $val];
                    }
                }

                $active = trim((string) ($row[4] ?? ''));
                if ($active === '') {
                    $active = 'Y';
                }
                $seq = 0;
                if (($row[2] ?? '') !== '' && is_numeric($row[2])) {
                    $seq = (int) $row[2];
                }

                $entity = new LookupSetValueEntity(
                    lookup_set_uuid: $rawSetId,
                    program_code1: trim((string) ($row[0] ?? '')),
                    display_name: $displayName,
                    description: trim((string) ($row[3] ?? '')),
                    seq: $seq,
                    active_flag: $active,
                    price_up_beli: (float) ($row[5] ?? 0),
                    diskon_pilih: (float) ($row[6] ?? 0),
                    persen1: (float) ($row[7] ?? 0),
                    persen2: (float) ($row[8] ?? 0),
                    details: $details,
                    company_uuid: $entity->company_uuid,
                );

                // Staging — delegasi ke repository (bila tersedia) atau upsertValue sebagai fallback.
                if (method_exists($this->lookupSetRepository, 'storeTempValue')) {
                    $this->lookupSetRepository->storeTempValue($entity);
                } else {
                    $this->lookupSetRepository->upsertValue($entity);
                }
            }

        $this->syncValue(LookupSetEntity::fromArray(['id' => (int) $rawSetId]));
    }

    public function syncValue(LookupSetEntity $entity): void
    {
        $this->lookupSetRepository->syncTempToMaster($entity->uuid ?: $entity->id);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function buildSetEntity(LookupSetEntity $filter): LookupSetEntity
    {
        $page = max(1, (int) ($filter->page ?: 1));
        $perPage = max(1, (int) ($filter->per_page ?: ($filter->length ?: 10)));
        $start = $filter->start > 0 ? $filter->start : (($page - 1) * $perPage);
        $length = $filter->start > 0 && $filter->length > 0 ? $filter->length : $perPage;

        return new LookupSetEntity(
            uuid: $filter->uuid,
            id: $filter->id,
            name: $filter->name,
            program_code: $filter->program_code,
            company_uuid: $filter->company_uuid,
            search: trim((string) ($filter->search ?? '')),
            search_column: trim((string) ($filter->search_column ?? 'all')),
            sort_by: trim((string) ($filter->sort_by ?? 'name')),
            sort_direction: trim((string) ($filter->sort_direction ?? 'asc')),
            page: $page,
            per_page: $perPage,
            start: $start,
            length: $length,
            actor_uuid: $filter->actor_uuid,
            filter_active: $filter->filter_active,
        );
    }

    private function buildValueEntity(LookupSetValueEntity $filter): LookupSetValueEntity
    {
        return new LookupSetValueEntity(
            uuid: $filter->uuid,
            lookup_set_uuid: $filter->lookup_set_uuid,
            program_code1: $filter->program_code1,
            display_name: $filter->display_name,
            description: $filter->description,
            seq: $filter->seq,
            active_flag: $filter->active_flag,
            price_up_beli: $filter->price_up_beli,
            diskon_pilih: $filter->diskon_pilih,
            persen1: $filter->persen1,
            persen2: $filter->persen2,
            details: $filter->details,
            company_uuid: $filter->company_uuid,
            search: trim((string) ($filter->search ?? '')),
            start: $filter->start,
            length: $filter->length,
            actor_uuid: $filter->actor_uuid,
        );
    }
}
