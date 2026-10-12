<?php

namespace App\Modules\Core\Organization\Services;

use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * BranchService
 *
 * Port dari BE: branch_service.go. Mengelola data master Cabang (Branch)
 * termasuk CRUD, validasi nama & kode, pengecekan relasi gudang, dan
 * hook import/export Excel (preview + commit).
 */
class BranchService
{
    public function __construct(protected BranchRepositoryInterface $branchRepository) {}

    /**
     * Daftar cabang dengan normalisasi search_column ke whitelist yang valid.
     */
    public function index(BranchEntity $entity): Collection
    {
        $allowedSearchColumns = ['all', 'name', 'short_name', 'description'];
        if (!in_array((string) $entity->search_column, $allowedSearchColumns, true)) {
            $entity->search_column = 'all';
        }

        $result = $this->branchRepository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    public function show(BranchEntity $entity): ?BranchEntity
    {
        if (trim((string) $entity->uuid) === '') {
            return null;
        }
        return $this->branchRepository->findByUuid($entity->uuid); // Note: findByUuid will be added or use fallback
    }

    /**
     * Simpan cabang dengan validasi:
     *  - Nama wajib diisi
     *  - Kode wajib diisi, maksimal 5 karakter
     *  - Kode harus unik (kecuali milik UUID yang sama)
     */
    public function save(BranchEntity $entity): BranchEntity
    {
        if (trim((string) $entity->name) === '') {
            throw new \InvalidArgumentException('Nama cabang wajib diisi.');
        }
        if (trim((string) $entity->short_name) === '') {
            throw new \InvalidArgumentException('Kode cabang wajib diisi.');
        }
        if (mb_strlen($entity->short_name) > 5) {
            throw new \InvalidArgumentException('Kode cabang maksimal 5 karakter.');
        }

        $existing = $this->branchRepository->getByShortName($entity);
        if ($existing !== null && $existing->uuid !== $entity->uuid) {
            throw new \InvalidArgumentException('Kode cabang sudah digunakan.');
        }

        return DB::transaction(fn () => $this->branchRepository->save($entity));
    }

    /**
     * Hapus cabang dengan pengecekan relasi gudang.
     * Return struktur {status, message} selaras BE.
     */
    public function destroy(BranchEntity $entity): array
    {
        if (trim((string) $entity->uuid) === '') {
            throw new \InvalidArgumentException('ID cabang tidak valid.');
        }

        if ($this->branchRepository->hasWarehouses($entity->uuid)) {
            return [
                'status'  => 'error',
                'message' => 'Cabang masih memiliki data gudang, tidak bisa dihapus.',
            ];
        }

        $this->branchRepository->delete($entity);

        return [
            'status'  => 'success',
            'message' => 'Cabang berhasil dihapus.',
        ];
    }

    /**
     * Export Excel (delegasi ke repo — serialisasi Excel dilakukan di layer Export class).
     */
    public function export(BranchEntity $entity): array
    {
        return $this->branchRepository->listForExport($entity);
    }

    public function findFirstBranchUuid(?string $companyUuid = null): ?string
    {
        if ($companyUuid !== null && $companyUuid !== '') {
            return $this->branchRepository->findFirstByCompany($companyUuid)?->uuid;
        }

        $branches = $this->branchRepository->getPageData(new BranchEntity())['items'] ?? [];

        return $branches[0]?->uuid;
    }

    public function findByUuidAndCompany(string $uuid, string $companyUuid): ?BranchEntity
    {
        return $this->branchRepository->findByUuidAndCompany($uuid, $companyUuid);
    }

    public function findByUuid(string $uuid): ?BranchEntity
    {
        return $this->branchRepository->findByUuid($uuid);
    }

    public function listBranches(): array
    {
        return $this->branchRepository->getPageData(new BranchEntity())['items'] ?? [];
    }

    /**
     * Template Excel untuk import master branch.
     * Layer controller/Export akan merender ke file .xlsx.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet'   => 'Data Cabang',
            'headers' => ['Kode Cabang', 'Nama Cabang', 'Deskripsi', 'Status Aktif'],
            'sample_row' => ['KDM', 'Klinik Demo', 'Cabang pusat operasional', true],
            'guide'   => [
                '1. Kode Cabang wajib unik, maksimal 5 karakter, dan disarankan huruf kapital.',
                '2. Nama Cabang wajib diisi sesuai nama usaha/cabang yang tampil ke user.',
                '3. Deskripsi boleh dikosongkan jika tidak dibutuhkan.',
                '4. Status Aktif diisi true/false atau aktif/nonaktif.',
            ],
        ];
    }

    /**
     * Preview import — parsing sudah dilakukan di controller/Import class; di sini
     * service hanya menerima array rows (index 0 dianggap header) dan memetakan
     * masing-masing row ke status new/update tanpa menulis ke DB.
     */
    public function previewImport(array $rows): array
    {
        $items = [];
        foreach ($rows as $index => $row) {
            if ($index === 0 || count($row) < 2) {
                continue;
            }
            $shortName = strtoupper(trim((string) ($row[0] ?? '')));
            $name = trim((string) ($row[1] ?? ''));
            $description = trim((string) ($row[2] ?? ''));
            $isActive = filter_var($row[3] ?? true, FILTER_VALIDATE_BOOLEAN);

            if ($shortName === '' || $name === '') {
                continue;
            }

            $existing = $this->branchRepository->getByShortName(new BranchEntity(short_name: $shortName));
            $status = $existing !== null ? 'update' : 'new';
            $message = $existing !== null
                ? 'Data akan memperbarui cabang ' . $existing->name
                : 'Data siap ditambahkan';

            $items[] = [
                'short_name'  => $shortName,
                'name'        => $name,
                'description' => $description,
                'is_active'   => $isActive,
                'status'      => $status,
                'message'     => $message,
            ];
        }

        return [
            'items' => $items,
            'total' => count($items),
        ];
    }

    /**
     * Import rows — delegasi ke save() per baris dengan best-effort, kumpulkan ringkasan.
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
            if ($index === 0 || count($row) < 2) {
                continue;
            }
            $summary['total']++;

            $shortName = strtoupper(trim((string) ($row[0] ?? '')));
            $name = trim((string) ($row[1] ?? ''));
            $description = trim((string) ($row[2] ?? ''));
            $isActive = filter_var($row[3] ?? true, FILTER_VALIDATE_BOOLEAN);

            if ($shortName === '' || $name === '') {
                $summary['failed']++;
                $summary['errors'][] = 'Baris ' . ($index + 1) . ': Kode dan Nama wajib diisi';
                continue;
            }

            try {
                $existing = $this->branchRepository->getByShortName(new BranchEntity(short_name: $shortName));
                $this->save(new BranchEntity(
                    uuid: $existing?->uuid,
                    name: $name,
                    short_name: $shortName,
                    description: $description,
                    is_active: $isActive,
                    actor_uuid: $actorUuid,
                ));
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = 'Baris ' . ($index + 1) . ': ' . $e->getMessage();
            }
        }

        return $summary;
    }
}
