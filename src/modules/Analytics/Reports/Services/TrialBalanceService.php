<?php

namespace App\Modules\Analytics\Reports\Services;

use App\Modules\Finance\Journal\Entities\PeriodInfoEntity;
use App\Modules\Finance\Journal\Entities\TotalsEntity;
use App\Modules\Finance\Ledger\Entities\TrialBalanceEntity;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Ledger\Services\TrialBalanceQueryService;
use Illuminate\Support\Carbon;
use Throwable;

class TrialBalanceService
{
    public function __construct(
        protected TrialBalanceQueryService $queryService
    ) {}

    /**
     * Generate Neraca Saldo (Trial Balance)
     */
    public function generate(string $periodUuid, ?string $branchUuid = null, bool $useStoredProcedure = true): TrialBalanceEntity
    {
        try {
            $period = $this->queryService->findPeriodById($periodUuid);
            if (!$period) {
                throw new \Exception("Periode tidak ditemukan.");
            }
            
            $accounts = $useStoredProcedure 
                ? $this->queryService->getBalancesViaStoredProcedure($period->start_date, $period->end_date, $branchUuid)
                : $this->queryService->getBalancesByAccount($periodUuid, $branchUuid);

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($accounts as $acc) {
                $totalDebit += $acc->total_debit;
                $totalCredit += $acc->total_credit;
            }

            $difference = abs($totalDebit - $totalCredit);
            $isBalanced = $difference < 0.01;

            return new TrialBalanceEntity(
                new PeriodInfoEntity(
                    $period->uuid,
                    $period->name,
                    $period->start_date,
                    $period->end_date,
                    is_object($period->status) ? $period->status->value : (string)$period->status
                ),
                $branchUuid,
                $accounts,
                new TotalsEntity($totalDebit, $totalCredit, $difference),
                $isBalanced,
                time()
            );
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Validasi Keseimbangan Neraca Saldo
     */
    public function validateBalance(string $periodUuid, ?string $branchUuid = null): array
    {
        try {
            $period = $this->queryService->findPeriodById($periodUuid);
            if (!$period) {
                throw new \Exception("Periode tidak ditemukan.");
            }
            $tb = $this->generate($periodUuid, $branchUuid);
            
            return [
                'is_balanced' => $tb->is_balanced,
                'totals' => $tb->totals->toArray(),
                'period' => $period->name,
                'branch_uuid' => $branchUuid
            ];
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }
}
