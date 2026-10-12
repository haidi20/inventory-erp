<?php

namespace App\Modules\Analytics\Reports\Services;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Ledger\Services\FinancialReportQueryService;
use App\Exceptions\UnauthorizedException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;
use Exception;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;

/**
 * FinancialReportService
 * 
 * Melayani pembuatan laporan keuangan utama seperti Neraca, Laba Rugi, dan Arus Kas.
 * Menggunakan FinancialReportEntity sebagai kontrak data filter.
 */
class FinancialReportService
{
    public function __construct(
        protected FinancialReportQueryService $queryService
    ) {}

    /**
     * Laporan Laba Rugi
     */
    public function getIncomeStatement(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        
        $period = $this->queryService->findPeriod($entity->branch_uuid, $periodUuid);
        $results = $this->queryService->getIncomeStatement($periodUuid, $entity->branch_uuid);
        
        $revenueTotal = 0;
        $expenseTotal = 0;
        foreach ($results as $row) {
            $revenueTotal += $row->total_revenue ?? 0;
            $expenseTotal += abs((float) ($row->total_expense ?? 0));
        }

        return [
            'period_info' => [
                'uuid' => $periodUuid,
                'period_name' => $period?->name ?? 'Unknown',
                'start_date' => $period?->start_date,
                'end_date' => $period?->end_date
            ],
            'branch_info' => [
                'uuid' => $entity->branch_uuid,
                'name' => $entity->branch_uuid ? ($this->queryService->findBranch($entity->branch_uuid)?->name ?? 'Branch') : 'Konsolidasi'
            ],
            'revenue' => ['total' => $revenueTotal],
            'expense' => ['total' => $expenseTotal],
            'net_income' => $revenueTotal - $expenseTotal,
            'details' => $results
        ];
    }

    /**
     * Laporan Neraca (Balance Sheet)
     */
    public function getBalanceSheet(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        
        $period = $this->queryService->findPeriod($entity->branch_uuid, $periodUuid);
        if (!$period) throw new Exception("Periode akuntansi tidak ditemukan.");
        
        $asOfDate = $period->end_date;
        $results = $this->queryService->getBalanceSheet($asOfDate, $entity->branch_uuid);
        
        $assetTotal = collect($results)->sum('total_assets');
        $liabilityTotal = collect($results)->sum('total_liabilities');
        $equityTotal = collect($results)->sum('total_equity');

        return [
            'period_info' => [
                'uuid' => $periodUuid,
                'period_name' => $period->name, 
                'as_of_date' => $asOfDate
            ],
            'branch_info' => [
                'uuid' => $entity->branch_uuid,
                'name' => $entity->branch_uuid ? ($this->queryService->findBranch($entity->branch_uuid)?->name ?? 'Branch') : 'Konsolidasi'
            ],
            'assets' => ['total' => $assetTotal],
            'liabilities' => ['total' => $liabilityTotal],
            'equity' => ['total' => $equityTotal],
            'total_liabilities_and_equity' => $liabilityTotal + $equityTotal,
            'is_balanced' => abs($assetTotal - ($liabilityTotal + $equityTotal)) < 0.01,
            'details' => $results
        ];
    }

    /**
     * Laporan Arus Kas (Indirect)
     */
    public function getCashFlow(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        return $this->queryService->getCashFlowIndirect($periodUuid, $entity->branch_uuid);
    }

    /**
     * Laporan Perubahan Ekuitas
     */
    public function getEquityStatement(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        return $this->queryService->getEquityStatement($periodUuid, $entity->branch_uuid);
    }

    /**
     * Neraca Saldo Setelah Penyesuaian
     */
    public function getAdjustedTrialBalance(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        return [
            'period_info' => ['uuid' => $periodUuid],
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getAdjustedTrialBalance($periodUuid, $entity->branch_uuid)
        ];
    }

    /**
     * Umur Piutang (AR Aging)
     */
    public function getAccountsReceivableAging(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        return [
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getARAging($entity->branch_uuid)
        ];
    }

    /**
     * Umur Hutang (AP Aging)
     */
    public function getAccountsPayableAging(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        return [
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getAPAging($entity->branch_uuid)
        ];
    }

    /**
     * Laporan Persediaan
     */
    public function getInventoryReport(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        return [
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getInventoryReport($entity->branch_uuid)
        ];
    }

    /**
     * Laporan Aset Tetap
     */
    public function getFixedAssetReport(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        return [
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getFixedAssetReport($entity->branch_uuid)
        ];
    }

    /**
     * Laporan Pajak
     */
    public function getTaxReport(FinancialReportEntity|string|null $entity, ?string $branchUuid = null)
    {
        $entity = $this->normalizeEntity($entity, $branchUuid);
        $this->validateBranchAccess($entity->branch_uuid);
        $periodUuid = $this->resolvePeriodId($entity->period_uuid, $entity->branch_uuid);
        return [
            'period_info' => ['uuid' => $periodUuid],
            'branch_info' => ['uuid' => $entity->branch_uuid],
            'details' => $this->queryService->getTaxReport($periodUuid, $entity->branch_uuid)
        ];
    }

    public function exportIncomeStatement(FinancialReportEntity|string|null $entity, ?string $branchUuid = null, string $format = 'excel'): array
    {
        $normalized = $this->normalizeEntity($entity, $branchUuid);
        $data = $this->getIncomeStatement($normalized);

        return $this->buildExportPayload('income_statement', $data, $normalized, $format);
    }

    public function exportBalanceSheet(FinancialReportEntity|string|null $entity, ?string $branchUuid = null, string $format = 'excel'): array
    {
        $normalized = $this->normalizeEntity($entity, $branchUuid);
        $data = $this->getBalanceSheet($normalized);

        return $this->buildExportPayload('balance_sheet', $data, $normalized, $format);
    }

    /**
     * Validasi akses cabang untuk pengguna yang login
     */
    protected function validateBranchAccess(?string $branchUuid): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (current_branch_uuid() && $branchUuid && current_branch_uuid() !== $branchUuid) {
                throw new UnauthorizedException("Akses Ditolak: Anda tidak memiliki izin untuk melihat laporan cabang lain.");
            }
        }
    }

    /**
     * Resolusi ID periode berdasarkan format UUID atau YYYY-MM
     */
    protected function resolvePeriodId(mixed $periodUuid, ?string $branchUuid = null): string
    {
        if (is_string($periodUuid)) {
            // Jika format YYYY-MM
            if (preg_match('/^\d{4}-\d{2}$/', $periodUuid)) {
                if ($branchUuid !== null) {
                    $period = $this->queryService->findPeriodByPeriod($branchUuid, $periodUuid);
                    if ($period) {
                        return (string) ($period->uuid ?? $period->id);
                    }
                }
            }
            return (string) $periodUuid;
        }
        return '';
    }

    protected function buildExportPayload(string $reportKey, array $data, FinancialReportEntity $entity, string $format): array
    {
        $normalizedFormat = strtolower(trim($format)) ?: 'excel';
        $periodUuid = $data['period_info']['uuid'] ?? $entity->period_uuid ?? 'unknown-period';
        $branchSegment = $entity->branch_uuid ?: 'consolidated';

        return [
            'format' => $normalizedFormat,
            'file_path' => "storage/app/reports/{$reportKey}_{$periodUuid}_{$branchSegment}.{$normalizedFormat}",
            'generated_at' => now()->timestamp,
            'data' => $data,
        ];
    }

    protected function normalizeEntity(FinancialReportEntity|string|null $entity, ?string $branchUuid = null): FinancialReportEntity
    {
        if ($entity instanceof FinancialReportEntity) {
            if ($entity->branch_uuid !== null || $branchUuid === null) {
                return $entity;
            }

            return FinancialReportEntity::fromArray([
                'period_uuid' => $entity->period_uuid,
                'branch_uuid' => $branchUuid,
                'company_uuid' => $entity->company_uuid,
                'format' => $entity->format,
                'as_of_date' => $entity->as_of_date,
                'actor_uuid' => $entity->actor_uuid,
            ]);
        }

        return FinancialReportEntity::fromArray([
            'period_uuid' => is_string($entity) ? $entity : null,
            'branch_uuid' => $branchUuid,
            'actor_uuid' => Auth::id(),
        ]);
    }
}

