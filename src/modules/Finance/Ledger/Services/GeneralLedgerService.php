<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Core\Organization\Services\BranchService;
use Exception;
use Throwable;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GeneralLedgerService
{
    protected GeneralLedgerRepository $repository;
    protected AccountSettingRepository $accountSettingRepository;
    protected BranchService $branchRepository;

    public function __construct(
        GeneralLedgerRepository $repository,
        AccountSettingRepository $accountSettingRepository,
        BranchService $branchRepository
    ) {
        $this->repository = $repository;
        $this->accountSettingRepository = $accountSettingRepository;
        $this->branchRepository = $branchRepository;
    }

    public function listGeneralLedgers(GeneralLedgerFilterEntity $entity): LengthAwarePaginator
    {
        if ($entity->period_uuid) {
            $period = $this->accountSettingRepository->findPeriodById($entity->period_uuid);
            if ($period) {
                $entity->date_from = $period->start_date->toDateString();
                $entity->date_to = $period->end_date->toDateString();
            }
        }

        return $this->repository->paginate($entity);
    }

    /**
     * Melakukan sinkronisasi data Buku Besar (General Ledger) untuk seluruh cabang dan akun.
     */
    public function syncAllBranches(): void
    {
        try {
            $branches = $this->branchRepository->listBranches();
            foreach ($branches as $branch) {
                $period = $this->accountSettingRepository->findOpenPeriod($branch->uuid);
                if ($period) {
                    $this->syncByPeriod($period->uuid);
                }
            }
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Menghasilkan laporan detail transaksi Buku Besar beserta Saldo Berjalan (Running Balance).
     */
    public function getLedgerReport(GeneralLedgerFilterEntity $entity): array
    {
        try {
            if (empty($entity->account_uuid)) throw new Exception("Account UUID is required");

            $account = $this->accountSettingRepository->findAccountById($entity->account_uuid);
            if (!$account) throw new Exception("Account not found.");

            $startTs = is_numeric($entity->date_from) ? (int)$entity->date_from : strtotime($entity->date_from);
            $endTs = is_numeric($entity->date_to) ? (int)$entity->date_to : strtotime($entity->date_to);

            if ($entity->branch_uuid !== null) {
                $currentPeriod = $this->accountSettingRepository->findPeriodByDate($entity->branch_uuid, $startTs);
                if ($currentPeriod && $currentPeriod->status === 'open') {
                    $this->syncByPeriod($currentPeriod->uuid);
                }
            }

            // 1. Opening Balance
            $openingBalance = $this->repository->getOpeningBalance($entity->account_uuid, $startTs, $entity->branch_uuid);
            if (
                (str_starts_with((string) $account->code, '4') || str_starts_with((string) $account->code, '5'))
                && (int) date('z', $startTs) === 0
            ) {
                $openingBalance = 0.0;
            }

            // 2. Journal Lines
            $options = [
                'keyword' => $entity->keyword,
                'min_amount' => $entity->min_amount,
                'max_amount' => $entity->max_amount,
                'is_reconciled' => $entity->is_reconciled,
                'reference_type' => $entity->reference_type,
            ];
            $lines = $this->repository->getJournalLines($entity->account_uuid, $startTs, $endTs, $entity->branch_uuid, $options);

            $reportLines = [];
            $runningBalance = $openingBalance;

            foreach ($lines as $line) {
                $mutation = (float) $line->debit - (float) $line->credit;
                $runningBalance += $mutation;

                $reportLines[] = [
                    'uuid' => $line->uuid,
                    'journal_uuid' => $line->journal_uuid,
                    'date' => date('Y-m-d', (int)$line->journal_date),
                    'reference' => $line->reference_no,
                    'description' => $line->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'balance' => $runningBalance,
                    'is_reconciled' => (bool) $line->is_reconciled,
                ];
            }

            return [
                'account_info' => [
                    'uuid' => $account->uuid,
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'filters' => $entity,
                'opening_balance' => $openingBalance,
                'transactions' => $reportLines,
                'closing_balance' => $runningBalance,
                'total_debit' => collect($reportLines)->sum('debit'),
                'total_credit' => collect($reportLines)->sum('credit'),
            ];
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    public function syncByPeriod(string $closingPeriodUuid): void
    {
        try {
            $this->repository->syncByPeriod($closingPeriodUuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    public function checkLedgerBalance(string $periodUuid): array
    {
        try {
            $period = $this->accountSettingRepository->findPeriodById($periodUuid);
            if (!$period) throw new Exception("Periode tidak ditemukan.");

            $sums = $this->repository->getTrialBalanceSums($period->branch_uuid, $periodUuid);
            $diff = abs(round($sums['debit'], 2) - round($sums['credit'], 2));

            return [
                'period' => $period->name,
                'total_debit' => $sums['debit'],
                'total_credit' => $sums['credit'],
                'difference' => $diff,
                'is_balanced' => $diff < 0.01,
            ];
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }
}

