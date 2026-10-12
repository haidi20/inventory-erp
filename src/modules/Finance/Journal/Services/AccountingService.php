<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Entities\AssetEntity;
use App\Modules\Finance\Journal\Entities\CoaEntity;
use App\Modules\Finance\Journal\Entities\CoaSetupEntity;
use App\Modules\Finance\Journal\Exports\AssetExport;
use App\Modules\Finance\Journal\Exports\AssetTemplateExport;
use App\Modules\Finance\Journal\Exports\CoaExport;
use App\Modules\Finance\Journal\Exports\CoaTemplateExport;
use App\Modules\Finance\Journal\Repositories\AccountingRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * AccountingService
 *
 * Service utama akuntansi (port dari accounting_service.go BE).
 * Mencakup:
 *  - Manajemen Aset Tetap + perhitungan penyusutan
 *  - Manajemen Account / COA (CRUD)
 *  - Pengaturan CoaSetup
 *  - Generate Auto Journal hook (stub untuk integrasi transaksi)
 *  - Template / Import / Export Excel untuk Aset & COA
 */
class AccountingService
{
    /** Default metode depresiasi: Straight Line (garis lurus). */
    public const DEPRECIATION_STRAIGHT_LINE = 1;

    public function __construct(
        protected AccountingRepositoryInterface $accountingRepository
    ) {}

    // =====================================================================
    // Asset / Depresiasi
    // =====================================================================

    /**
     * Hitung penyusutan tahunan aset.
     * Saat ini mendukung Straight Line (AssetValue / LifeYear).
     */
    public function calculateDepreciation(AssetEntity $asset): float
    {
        $method = (int) ($asset->depreciation_method_uuid ?? 1); // 1 = Straight Line
        $lifeYear = (float) ($asset->life_year ?? 0);
        $value = (float) ($asset->asset_value ?? 0);

        if ($method === 1 && $lifeYear > 0) {
            return $value / $lifeYear;
        }

        return 0.0;
    }

    public function getAsset(string $uuid): ?AssetEntity
    {
        return $this->accountingRepository->getAssetByUuid($uuid);
    }

    public function createAsset(AssetEntity $entity): AssetEntity
    {
        return DB::transaction(function () use ($entity) {
            $entity->yearly_depreciation = $this->calculateDepreciation($entity);

            return $this->accountingRepository->createAsset($entity);
        });
    }

    public function updateAsset(AssetEntity $entity): AssetEntity
    {
        $entity->yearly_depreciation = $this->calculateDepreciation($entity);

        return $this->accountingRepository->updateAsset($entity);
    }

    public function deleteAsset(string $uuid): void
    {
        $this->accountingRepository->deleteAsset($uuid);
    }

    public function getAllAssets(AssetEntity $entity): array
    {
        return $this->accountingRepository->getAllAssets($entity);
    }

    // =====================================================================
    // Account / Coa CRUD
    // =====================================================================

    public function createAccount(AccountEntity $entity): AccountEntity
    {
        return $this->accountingRepository->createAccount($entity);
    }

    public function updateAccount(AccountEntity $entity): AccountEntity
    {
        return $this->accountingRepository->updateAccount($entity);
    }

    public function deleteAccount(string $uuid): void
    {
        $this->accountingRepository->deleteAccount($uuid);
    }

    public function createCoa(CoaEntity $entity): CoaEntity
    {
        return $this->accountingRepository->createCoa($entity);
    }

    // =====================================================================
    // Coa Setup
    // =====================================================================

    public function updateCoaSetup(CoaSetupEntity $entity)
    {
        Validator::make($entity->toArray(), [
            'program_name' => ['required', 'string', 'max:100'],
            'account_uuid' => ['required', 'string', 'exists:accounts,uuid'],
        ])->validate();

        return $this->accountingRepository->updateCoaSetup($entity->toArray());
    }

    // =====================================================================
    // Auto Journal hook
    // =====================================================================

    /**
     * Hook untuk men-generate jurnal otomatis dari transaksi lain (sales, purchase, dll).
     */
    public function generateAutoJournal(string $refType, string $refUuid): void
    {
        // Placeholder intentionally kept as no-op (mengikuti BE yang simplified).
    }

    // =====================================================================
    // Excel — Assets
    // =====================================================================

    /** Unduh template Excel untuk import data aset. */
    public function getAssetTemplate(): string
    {
        return Excel::raw(new AssetTemplateExport, \Maatwebsite\Excel\Excel::XLSX);
    }

    /** Export seluruh aset (berdasarkan filter) sebagai Excel. */
    public function exportAssets(AssetEntity $filter): string
    {
        $filter->page = 1;
        $filter->per_page = 1000000;
        $result = $this->accountingRepository->getAllAssets($filter);

        return Excel::raw(new AssetExport($result['items'] ?? []), \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Import Aset dari file Excel.
     */
    public function importAssets(string $branchUuid, ?string $userUuid, string $fileContent): array
    {
        if (empty($fileContent)) {
            throw new \InvalidArgumentException('File import tidak boleh kosong.');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'asset_import_').'.xlsx';
        file_put_contents($tmpPath, $fileContent);

        try {
            $rows = Excel::toArray(null, $tmpPath)[0] ?? [];
        } finally {
            @unlink($tmpPath);
        }

        $success = 0;
        $total = 0;

            foreach ($rows as $i => $row) {
                if ($i === 0 || count($row) < 2) {
                    continue;
                }
                $code = trim((string) ($row[0] ?? ''));
                if ($code === '') {
                    continue;
                }

                $total++;
                $entity = new AssetEntity(
                    code: $code,
                    name: trim((string) ($row[1] ?? '')),
                    branch_uuid: $branchUuid,
                    actor_uuid: $userUuid,
                );

                $existing = $this->accountingRepository->getAssetByCode($code);
                if ($existing) {
                    $entity->uuid = $existing->uuid;
                    $this->updateAsset($entity);
                } else {
                    $this->createAsset($entity);
                }
                $success++;
            }

        return [
            'total' => $total,
            'success' => $success,
        ];
    }

    // =====================================================================
    // Excel — COA
    // =====================================================================

    public function getCoaTemplate(): string
    {
        return Excel::raw(new CoaTemplateExport, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportCoa(): string
    {
        $accounts = $this->accountingRepository->getAllAccounts();

        return Excel::raw(new CoaExport($accounts), \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Import COA dari Excel.
     */
    public function importCoa(string $branchUuid, ?string $userUuid, string $fileContent): array
    {
        if (empty($fileContent)) {
            throw new \InvalidArgumentException('File import tidak boleh kosong.');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'coa_import_').'.xlsx';
        file_put_contents($tmpPath, $fileContent);

        try {
            $rows = Excel::toArray(null, $tmpPath)[0] ?? [];
        } finally {
            @unlink($tmpPath);
        }

        $allAccounts = $this->accountingRepository->getAllAccounts();
        $codeToUuid = [];
        foreach ($allAccounts as $acc) {
            $codeToUuid[$acc['code']] = $acc['uuid'];
        }

        $success = 0;
        $total = 0;

            foreach ($rows as $i => $row) {
                if ($i === 0 || count($row) < 3) {
                    continue;
                }
                $code = trim((string) ($row[0] ?? ''));
                if ($code === '') {
                    continue;
                }
                $total++;

                $entity = AccountEntity::fromArray([
                    'code' => $code,
                    'name' => trim((string) ($row[1] ?? '')),
                    'type' => strtolower(trim((string) ($row[2] ?? 'asset'))),
                    'is_header' => $row[4] ?? false,
                    'is_active' => $row[7] ?? true,
                ]);

                if (isset($codeToUuid[$code])) {
                    $entity->uuid = $codeToUuid[$code];
                    $this->updateAccount($entity);
                } else {
                    $this->createAccount($entity);
                }
                $success++;
            }

        return [
            'total' => $total,
            'success' => $success,
        ];
    }
}
