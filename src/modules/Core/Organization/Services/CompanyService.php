<?php

namespace App\Modules\Core\Organization\Services;

use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * CompanyService
 *
 * Port dari BE: company_service.go. Mengelola data Perusahaan/Profil klinik
 * — CRUD, export/import Excel, dan template Excel untuk import.
 */
class CompanyService
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository) {}

    /**
     * Daftar perusahaan dengan pagination. Default per_page & page disamakan dengan BE.
     */
    public function index(CompanyEntity $entity): Collection
    {
        $result = $this->companyRepository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    public function getByUuid(CompanyEntity $entity): ?CompanyEntity
    {
        $uuid = trim((string) $entity->uuid);
        if ($uuid === '') {
            return null;
        }
        return $this->companyRepository->findByUuid($uuid);
    }

    public function getFirstActive(): ?CompanyEntity
    {
        return $this->companyRepository->findFirstActive();
    }

    public function findByUuid(string $uuid): ?CompanyEntity
    {
        $uuid = trim($uuid);
        if ($uuid === '') {
            return null;
        }

        return $this->companyRepository->findByUuid($uuid);
    }

    /**
     * Simpan perusahaan baru dengan validasi nama wajib.
     * Jika UUID kosong, dibuat otomatis.
     */
    public function store(CompanyEntity $entity): CompanyEntity
    {
        if (trim((string) $entity->name) === '') {
            throw new \InvalidArgumentException('Nama perusahaan wajib diisi.');
        }

        return DB::transaction(fn () => $this->companyRepository->save($entity));
    }

    /**
     * Update perusahaan — UUID wajib diisi.
     */
    public function update(CompanyEntity $entity): CompanyEntity
    {
        if (trim((string) $entity->uuid) === '') {
            throw new \InvalidArgumentException('UUID perusahaan wajib diisi.');
        }
        if (trim((string) $entity->name) === '') {
            throw new \InvalidArgumentException('Nama perusahaan wajib diisi.');
        }

        return DB::transaction(fn () => $this->companyRepository->save($entity));
    }

    public function destroy(CompanyEntity $entity): void
    {
        $uuid = trim((string) $entity->uuid);
        if ($uuid === '') {
            throw new \InvalidArgumentException('UUID perusahaan wajib diisi.');
        }
        $this->companyRepository->delete($uuid);
    }

    /**
     * Export — delegasi ke repo, layer Export akan merender ke Excel.
     * Mengembalikan kontrak {data, filename} parity BE `DatabaseBackupResult`.
     */
    public function export(CompanyEntity $entity): array
    {
        $entity->per_page = 10000;
        $result = $this->companyRepository->getPageData($entity);

        return [
            'items'    => $result['items'] ?? [],
            'headers'  => [
                'Nama', 'Jenis', 'No. Registrasi', 'Telepon', 'Email',
                'Alamat', 'Provinsi', 'Kota', 'Kecamatan', 'Kode Pos', 'Website',
            ],
            'filename' => 'master-perusahaan.xlsx',
        ];
    }

    /**
     * Template Excel untuk import master perusahaan.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet'   => 'Data Perusahaan',
            'headers' => ['Nama', 'Jenis', 'No. Registrasi', 'Telepon', 'Email', 'Alamat'],
            'sample_row' => ['Klinik Contoh', 'Klinik Pratama', 'REG-001', '02100000000', 'info@klinik.test', 'Alamat contoh'],
            'guide'   => [
                '1. Kolom Nama: Nama resmi klinik/perusahaan (Wajib).',
                '2. Kolom No. Registrasi: Digunakan sebagai kunci unik untuk UPSERT.',
                '3. Kolom Email: Gunakan format email yang valid.',
                '4. Pastikan data alamat terisi lengkap untuk keperluan pelaporan.',
            ],
        ];
    }

    /**
     * Import rows — baris pertama header, upsert berdasarkan No. Registrasi (fallback by name).
     */
    public function importRows(array $rows, string $actorUuid): array
    {
        $summary = [
            'total'   => 0,
            'success' => 0,
            'failed'  => 0,
            'errors'  => [],
        ];

        foreach ($rows as $index => $row) {
            if ($index === 0 || count($row) < 1) {
                continue;
            }
            $summary['total']++;

            $name = trim((string) ($row[0] ?? ''));
            if ($name === '') {
                $summary['failed']++;
                $summary['errors'][] = 'Baris ' . ($index + 1) . ': Nama wajib diisi';
                continue;
            }

            try {
                $regNo = trim((string) ($row[2] ?? ''));
                $existing = null;
                if ($regNo !== '') {
                    $pageData = $this->companyRepository->getPageData(
                        new CompanyEntity(search: $regNo, per_page: 1, page: 1)
                    );
                    $items = $pageData['items'] ?? [];
                    if (!empty($items)) {
                        $first = $items[0];
                        $existing = is_array($first) ? ($first['uuid'] ?? null) : ($first->uuid ?? null);
                    }
                }

                $entity = new CompanyEntity(
                    uuid: $existing,
                    name: $name,
                    address: trim((string) ($row[5] ?? '')),
                    phone: trim((string) ($row[3] ?? '')),
                    email: trim((string) ($row[4] ?? '')),
                    actor_uuid: $actorUuid,
                );

                if ($existing !== null) {
                    $this->update($entity);
                } else {
                    $this->store($entity);
                }
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = 'Baris ' . ($index + 1) . ': ' . $e->getMessage();
            }
        }

        return $summary;
    }

    private function buildEntity(array $input, string $name): CompanyEntity
    {
        return new CompanyEntity(
            uuid: trim((string) ($input['uuid'] ?? '')) ?: null,
            name: $name,
            address: isset($input['address']) ? trim((string) $input['address']) : null,
            phone: isset($input['phone']) ? trim((string) $input['phone']) : null,
            email: isset($input['email']) ? trim((string) $input['email']) : null,
            website: isset($input['website']) ? trim((string) $input['website']) : null,
            logo: $input['logo'] ?? null,
            is_active: filter_var($input['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            actor_uuid: $input['actor_uuid'] ?? null,
        );
    }
}
