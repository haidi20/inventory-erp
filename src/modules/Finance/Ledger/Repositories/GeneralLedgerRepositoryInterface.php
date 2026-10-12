<?php

namespace App\Modules\Finance\Ledger\Repositories;

/**
 * GeneralLedgerRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Buku Besar (General Ledger).
 */
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GeneralLedgerRepositoryInterface
{
    public function paginate(GeneralLedgerFilterEntity $entity): LengthAwarePaginator;

    /**
     * Mendapatkan saldo awal akun pada tanggal tertentu.
     * 
     * @param string $accountUuid
     * @param string $date
     * @param string|null $branchUuid
     * @return float
     */
    public function getOpeningBalance(string $accountUuid, string|int $date, ?string $branchUuid = null): float;

    /**
     * Mendapatkan daftar transaksi buku besar dalam rentang tanggal.
     * 
     * @param string $accountUuid
     * @param string $startDate
     * @param string $endDate
     * @param string|null $branchUuid
     * @param array $options
     * @return array
     */
    public function getJournalLines(string $accountUuid, string|int $startDate, string|int $endDate, ?string $branchUuid = null, array $options = []): array;

    public function syncByPeriod(string $closingPeriodUuid): void;

    /**
     * Mendapatkan total debit dan credit dari general_ledger dalam satu periode.
     *
     * @param string $branchUuid
     * @param string $periodUuid
     * @return array{debit: float, credit: float}
     */
    public function getTrialBalanceSums(string $branchUuid, string $periodUuid): array;
}
