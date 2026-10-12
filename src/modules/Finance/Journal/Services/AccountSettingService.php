<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Journal\Repositories\JournalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

// akuntasi pengaturan:
// Daftar Akun (Chart of Accounts)
// Pemetaan Akun dan menu (Account Mapping)
// Master Pajak (Tax Settings)
// Saldo Awal (Opening Balance)
// Periode Akuntansi (Closing Period)

class AccountSettingService
{
    protected AccountSettingRepository $repository;
    protected JournalService $journalService;
    protected JournalRepository $journalRepository;

    public function __construct(
        AccountSettingRepository $repository,
        JournalService $journalService,
        JournalRepository $journalRepository
    )
    {
        $this->repository = $repository;
        $this->journalService = $journalService;
        $this->journalRepository = $journalRepository;
    }

    /**
     * Get all accounts for chart of accounts.
     *
     * @return Collection
     */
    public function getChartOfAccounts(): Collection
    {
        try {
            return $this->repository->allAccounts();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get all defined account mappings.
     *
     * @return Collection
     */
    public function getAccountMappings(): Collection
    {
        try {
            return $this->repository->getMappings();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update account mapping by key and account code.
     *
     * @param string $key
     * @param string $accountCode
     * @return Model|null
     */
    public function updateMappingByCode(string $key, string $accountCode): ?Model
    {
        try {
            $account = $this->repository->findAccountByCode($accountCode);

            if (!$account) {
                return null;
            }

            return $this->repository->updateMapping($key, $account->uuid ?? $account->id);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    // ==========================================
    // Master Pajak (Tax Settings)
    // ==========================================

    /**
     * Get all tax settings.
     *
     * @return Collection
     */
    public function getTaxSettings(): Collection
    {
        try {
            return $this->repository->getTaxSettings();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update or create a tax setting and link to an account code.
     *
     * @param string $name
     * @param float $percentage
     * @param string $accountCode
     * @return Model|null
     */
    public function updateTaxSetting(string $name, float $percentage, string $accountCode): ?Model
    {
        try {
            $account = $this->repository->findAccountByCode($accountCode);
            if (!$account) return null;

            return $this->repository->updateTaxSetting($name, $percentage, $account->uuid ?? $account->id);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    // ==========================================
    // Saldo Awal (Opening Balance)
    // ==========================================

    /**
     * Get opening balances for a specific branch.
     *
     * @param string $branchUuid
     * @return Collection
     */
    public function getOpeningBalances(string $branchUuid): Collection
    {
        try {
            return $this->repository->getOpeningBalances($branchUuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update an opening balance for an account code.
     * Otomatis membuat jurnal saldo awal (Debit: Akun Terkait, Kredit: Modal).
     *
     * @param string $branchUuid
     * @param string $accountCode
     * @param float $amount
     * @return Model|null
     */
    public function updateOpeningBalance(string $branchUuid, string $accountCode, float $amount): ?Model
    {
        try {
            $account = $this->repository->findAccountByCode($accountCode);
            if (!$account) {
                return null;
            }

            $balance = $this->repository->updateOpeningBalance($branchUuid, $account->uuid ?? $account->id, $amount);

            // Akun Modal (Equity) sebagai contra account
            $equityAccount = $this->repository->findAccountByCode('3100');
            if (!$equityAccount || $amount <= 0) {
                return $balance;
            }

            // Tentukan posisi debit/kredit berdasarkan tipe akun
            $accountType = is_object($account->type) ? $account->type->value : $account->type;
            $isDebitNormal = in_array($accountType, ['asset', 'expense'], true);

            $lines = [
                [
                    'account_uuid' => $account->uuid ?? $account->id, // map to account_uuid in db since we changed the id
                    'debit'  => $isDebitNormal ? $amount : 0,
                    'credit' => $isDebitNormal ? 0 : $amount,
                ],
                [
                    'account_uuid' => $equityAccount->uuid ?? $equityAccount->id, // map to account_uuid
                    'debit'  => $isDebitNormal ? 0 : $amount,
                    'credit' => $isDebitNormal ? $amount : 0,
                ],
            ];

            $uuidBalance = $balance->uuid ?? (string)$balance->id;

            $dto = JournalEntity::fromArray([
                'branch_uuid'      => $branchUuid,
                'reference_no'   => $this->resolveOpeningBalanceReferenceNo($branchUuid, $uuidBalance),
                'reference_uuid'   => $uuidBalance,
                'journal_date'   => time(),
                'reference_type' => JournalReferenceType::OPENING_BALANCE,
                'description'    => "Saldo Awal: {$account->name} ({$accountCode})",
                'lines'          => $lines,
            ]);

            $this->journalService->upsertJournal($dto);

            return $balance;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    protected function resolveOpeningBalanceReferenceNo(string $branchUuid, string $balanceUuid): string
    {
            $existingJournal = $this->journalRepository->findByReference(
            $branchUuid,
            JournalReferenceType::OPENING_BALANCE->value,
            $balanceUuid
        );

        if ($existingJournal) {
            return $existingJournal->reference_no;
        }

        return $this->journalService->generateReferenceNo(
            JournalReferenceType::OPENING_BALANCE,
            null,
            ['branch_uuid' => $branchUuid]
        );
    }

    // ==========================================
    // Periode Akuntansi (Closing Period)
    // ==========================================

    /**
     * Get all closing periods for a branch.
     *
     * @param string $branchUuid
     * @return Collection
     */
    public function getClosingPeriods(string $branchUuid): Collection
    {
        try {
            return $this->repository->getClosingPeriods($branchUuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Close a specific accounting period.
     *
     * @param string $branchUuid
     * @param string $period (e.g. '2023-12')
     * @return Model
     */
    public function closePeriod(string $branchUuid, string $period): Model
    {
        try {
            return $this->repository->createOrUpdateClosingPeriod([
                'branch_uuid' => $branchUuid,
                'period' => $period,
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }
}

