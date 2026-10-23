<?php

namespace App\Modules\Finance\Ledger\Repositories;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * GeneralLedgerRepository
 *
 * Implementasi repository untuk manajemen data Buku Besar (General Ledger).
 * Menangani kalkulasi saldo awal dan pengambilan histori transaksi jurnal.
 */
class GeneralLedgerRepository implements GeneralLedgerRepositoryInterface
{
    /**
     * Param:
     * - GeneralLedgerFilterEntity $entity: data entity.
     *
     * Proses: Membuat pagination.
     *
     * Output: Paginator hasil data.
     */
    public function paginate(GeneralLedgerFilterEntity $entity): LengthAwarePaginator
    {
        return GeneralLedger::query()
            ->with('account')
            ->where('company_uuid', $entity->company_uuid)
            ->when($entity->branch_uuid, fn ($query) => $query->where('branch_uuid', $entity->branch_uuid))
            ->when($entity->account_uuid, fn ($query) => $query->where('account_uuid', $entity->account_uuid))
            ->when($entity->date_from, fn ($query) => $query->where('journal_date', '>=', Carbon::parse($entity->date_from)->startOfDay()->timestamp))
            ->when($entity->date_to, fn ($query) => $query->where('journal_date', '<=', Carbon::parse($entity->date_to)->endOfDay()->timestamp))
            ->orderBy('journal_date')
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);
    }

    /**
     * {@inheritDoc}
     *
     * Param:
     * - string $accountUuid: data akun.
     * - string|int $date: tanggal.
     * - ?string $branchUuid: cabang.
     *
     * Proses: Mengeksekusi pembacaan data opening balance pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service. Akses data tetap memperhatikan konteks company/branch untuk menjaga isolasi tenant.
     *
     * Output: Nilai numerik hasil proses.
     */
    public function getOpeningBalance(string $accountUuid, string|int $date, ?string $branchUuid = null): float
    {
        // Get account details to determine balance direction and type
        $account = Account::query()->where('uuid', $accountUuid)->firstOrFail();

        $isDebitNormal = $this->isDebitNormal($account->type);
        $isPL = $this->isPLAccount($account->type)
            || ($account->type === null
                && (str_starts_with((string) $account->code, '4')
                    || str_starts_with((string) $account->code, '5')));

        $targetDate = is_numeric($date)
            ? Carbon::createFromTimestamp((int) $date)->startOfDay()
            : Carbon::parse($date)->startOfDay();
        $targetUnix = $targetDate->timestamp;

        $openingBalance = 0;

        if ($isPL && $targetDate->dayOfYear === 1) {
            return 0.0;
        }

        // Balance Sheet accounts have an initial balance from Opening period
        if (! $isPL) {
            $openingBalance = DB::table('account_balances')
                ->where('account_uuid', $accountUuid)
                ->where('company_uuid', $account->company_uuid)
                ->where('period', 'Opening')
                ->where('deleted_at', 0)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->value('opening_balance');

            if ($openingBalance === null) {
                $fallbackOpeningBalances = DB::table('account_balances')
                    ->where('account_uuid', $accountUuid)
                    ->where('company_uuid', $account->company_uuid)
                    ->where('period', 'Opening')
                    ->where('deleted_at', 0)
                    ->pluck('opening_balance');

                if ($fallbackOpeningBalances->count() === 1) {
                    $openingBalance = $fallbackOpeningBalances->first();
                }
            }

            $openingBalance = $openingBalance ?? 0;
        }

        // Add mutations before target date
        $query = DB::table('journal_lines')
            ->join('journals', 'journals.uuid', '=', 'journal_lines.journal_uuid')
            ->where('journal_lines.account_uuid', $accountUuid)
            ->where('journals.company_uuid', $account->company_uuid)
            ->whereIn('journals.status', ['posted', 'reversed', 'voided'])
            ->where('journals.journal_date', '<', $targetUnix)
            ->where('journal_lines.deleted_at', 0)
            ->where('journals.deleted_at', 0);

        if ($isPL) {
            // PL accounts start fresh at the beginning of the year
            $startOfYear = $targetDate->copy()->startOfYear()->timestamp;
            $query->where('journals.journal_date', '>=', $startOfYear);
        }

        if ($branchUuid) {
            $query->where('journals.branch_uuid', $branchUuid);
        }

        $totals = $query->select(
            DB::raw('COALESCE(SUM(journal_lines.debit), 0) as debit'),
            DB::raw('COALESCE(SUM(journal_lines.credit), 0) as credit')
        )->first();

        $mutation = $isDebitNormal
            ? ($totals->debit - $totals->credit)
            : ($totals->credit - $totals->debit);

        return (float) $openingBalance + (float) $mutation;
    }

    /**
     * {@inheritDoc}
     *
     * Param:
     * - string $accountUuid: data akun.
     * - string|int $startDate: batas awal.
     * - string|int $endDate: batas akhir.
     * - ?string $branchUuid: cabang.
     * - array $options: konfigurasi tambahan.
     *
     * Proses: Mengeksekusi pembacaan data journal lines pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service. Akses data tetap memperhatikan konteks company/branch untuk menjaga isolasi tenant.
     *
     * Output: Array hasil proses.
     */
    public function getJournalLines(string $accountUuid, string|int $startDate, string|int $endDate, ?string $branchUuid = null, array $options = []): array
    {
        $startUnix = is_numeric($startDate)
            ? Carbon::createFromTimestamp((int) $startDate)->startOfDay()->timestamp
            : Carbon::parse($startDate)->startOfDay()->timestamp;
        $endUnix = is_numeric($endDate)
            ? Carbon::createFromTimestamp((int) $endDate)->endOfDay()->timestamp
            : Carbon::parse($endDate)->endOfDay()->timestamp;

        $query = DB::table('journal_lines')
            ->join('journals', 'journals.uuid', '=', 'journal_lines.journal_uuid')
            ->where('journal_lines.account_uuid', $accountUuid)
            ->whereIn('journals.status', ['posted', 'reversed', 'voided'])
            ->whereBetween('journals.journal_date', [$startUnix, $endUnix])
            ->where('journal_lines.deleted_at', 0)
            ->where('journals.deleted_at', 0)
            ->orderBy('journals.journal_date', 'ASC')
            ->orderBy('journals.created_at', 'ASC');

        if ($branchUuid) {
            $query->where('journals.branch_uuid', $branchUuid);
        }

        if (! empty($options['keyword'])) {
            $keyword = strtolower($options['keyword']);
            $query->whereRaw('LOWER(journals.description) LIKE ?', ['%'.$keyword.'%']);
        }

        if (! empty($options['min_amount'])) {
            // Use CAST(? AS REAL) because PHP floats are bound as TEXT by PDO/SQLite,
            // and SQLite considers INTEGER < TEXT regardless of value.
            $query->whereRaw('(journal_lines.debit + journal_lines.credit) >= CAST(? AS REAL)', [(float) $options['min_amount']]);
        }

        if (! empty($options['max_amount'])) {
            $query->whereRaw('(journal_lines.debit + journal_lines.credit) <= CAST(? AS REAL)', [(float) $options['max_amount']]);
        }

        if (isset($options['is_reconciled']) && $options['is_reconciled'] !== null) {
            // is_reconciled is always 0 in SQLite (no reconciliation table yet); if filter=true → 0 results
            $query->whereRaw('0 = 1'); // placeholder: mark as unreconciled when true is requested
        }

        return $query->select([
            'journals.uuid as journal_uuid',
            'journal_lines.uuid',
            'journals.journal_date',
            'journals.reference_no',
            'journals.description',
            'journal_lines.debit',
            'journal_lines.credit',
            'journals.reference_type',
            DB::raw('0 as is_reconciled'),
        ])->get()->toArray();
    }

    /**
     * Membangun ulang buku besar satu periode dari jurnal final.
     *
     * Param:
     * - string $closingPeriodUuid: UUID periode yang disinkronkan.
     *
     * Proses: Transaksi eksplisit; periode dan jurnal dibaca tanpa global scope karena sync adalah operasi sistem lintas sesi (bukan scope user login); kumpulkan journal posted/reversed/voided pada rentang periode dan branch periode, ambil lines join journals terurut tanggal/referensi, lalu upsert ke general_ledgers.
     *
     * Output: Tidak mengembalikan nilai.
     */
    public function syncByPeriod(string $closingPeriodUuid): void
    {
        DB::beginTransaction();
        try {
            $period = ClosingPeriod::withoutGlobalScopes()->where('uuid', $closingPeriodUuid)->firstOrFail();

            // withoutGlobalScopes: sync GL adalah operasi sistem berbasis periode,
            // tidak boleh ter-filter konteks company/branch user yang sedang login
            // (misal sync periode cabang A saat session user cabang B aktif).
            $journalUuids = Journal::query()->withoutGlobalScopes()
                ->where('branch_uuid', $period->branch_uuid)
                ->whereBetween('journal_date', [(int) $period->start_date, (int) $period->end_date])
                ->whereIn('status', ['posted', 'reversed', 'voided'])
                ->pluck('uuid');

            $lines = JournalLine::query()->withoutGlobalScopes()
                ->join('journals', 'journals.uuid', '=', 'journal_lines.journal_uuid')
                ->whereIn('journals.uuid', $journalUuids)
                ->where('journal_lines.deleted_at', 0)
                ->where('journals.deleted_at', 0)
                ->orderBy('journals.journal_date')
                ->orderBy('journals.reference_no')
                ->orderBy('journals.created_at')
                ->select([
                    'journal_lines.uuid as journal_line_uuid',
                    'journal_lines.account_uuid',
                    'journal_lines.branch_uuid',
                    'journal_lines.debit',
                    'journal_lines.credit',
                    'journals.uuid as journal_uuid',
                    'journals.company_uuid',
                    'journals.journal_date',
                    'journals.reference_no',
                    'journals.description',
                    'journals.is_system',
                ])
                ->get();

            // Remove stale GL entries: lines that were deleted or journals that no longer have active lines
            if ($journalUuids->isNotEmpty()) {
                $activeLineUuids = $lines->pluck('journal_line_uuid');
                $staleQuery = GeneralLedger::query()->whereIn('journal_uuid', $journalUuids);
                if ($activeLineUuids->isNotEmpty()) {
                    $staleQuery->whereNotIn('journal_line_uuid', $activeLineUuids);
                }
                $staleQuery->delete();
            }

            $balances = [];
            foreach ($lines as $line) {
                $roundedDebit = round((float) $line->debit, 2);
                $roundedCredit = round((float) $line->credit, 2);
                $key = $line->branch_uuid.':'.$line->account_uuid;
                $balances[$key] = round(($balances[$key] ?? 0) + ($roundedDebit - $roundedCredit), 2);

                // Use updateOrCreate keyed by journal_line_uuid to preserve the GL UUID on re-sync (true upsert).
                // withoutGlobalScopes() because the match criteria already includes deleted_at = 0.
                GeneralLedger::withoutGlobalScopes()->updateOrCreate(
                    ['journal_line_uuid' => $line->journal_line_uuid, 'deleted_at' => 0],
                    [
                        'branch_uuid' => $line->branch_uuid,
                        'company_uuid' => $line->company_uuid,
                        'account_uuid' => $line->account_uuid,
                        'journal_uuid' => $line->journal_uuid,
                        'journal_date' => $line->journal_date,
                        'reference_no' => $line->reference_no,
                        'description' => $line->description,
                        'debit' => $roundedDebit,
                        'credit' => $roundedCredit,
                        'balance' => $balances[$key],
                        'is_system' => (bool) $line->is_system,
                    ]
                );
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Param:
     * - string $closingPeriodUuid: closing period uuid.
     *
     * Proses: Mengeksekusi pembacaan data period pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service.
     *
     * Output: ClosingPeriod hasil proses atau null.
     */
    public function findPeriod(string $closingPeriodUuid): ?ClosingPeriod
    {
        return ClosingPeriod::withoutGlobalScopes()->where('uuid', $closingPeriodUuid)->first();
    }

    /**
     * Param:
     * - string $branchUuid: cabang.
     *
     * Proses: Mengeksekusi pembacaan data branch pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service.
     *
     * Output: Branch hasil proses atau null.
     */
    public function findBranch(string $branchUuid): ?Branch
    {
        return Branch::query()->where('uuid', $branchUuid)->first();
    }

    /**
     * Param:
     * - string $period: periode.
     * - ?string $branchUuid: cabang.
     *
     * Proses: Mengeksekusi pembacaan data periods for cash flow pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service. Akses data tetap memperhatikan konteks company/branch untuk menjaga isolasi tenant.
     *
     * Output: Collection data hasil proses.
     */
    public function findPeriodsForCashFlow(string $period, ?string $branchUuid = null): Collection
    {
        return ClosingPeriod::withoutGlobalScopes()
            ->where('period', $period)
            ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
            ->get();
    }

    /**
     * Menghitung total debit dan credit dari general_ledger dalam satu periode.
     *
     * Param:
     * - string $branchUuid: cabang.
     * - string $periodUuid: periode.
     *
     * Proses: Mengeksekusi pembacaan data trial balance sums pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service. Akses data tetap memperhatikan konteks company/branch untuk menjaga isolasi tenant.
     *
     * Output: Array hasil proses.
     */
    public function getTrialBalanceSums(string $branchUuid, string $periodUuid): array
    {
        $period = ClosingPeriod::withoutGlobalScopes()->where('uuid', $periodUuid)->firstOrFail();

        $sums = DB::table('general_ledgers')
            ->join('journals', 'journals.uuid', '=', 'general_ledgers.journal_uuid')
            ->where('general_ledgers.branch_uuid', $branchUuid)
            ->whereBetween('journals.journal_date', [(int) $period->start_date, (int) $period->end_date])
            ->where('general_ledgers.deleted_at', 0)
            ->selectRaw('COALESCE(SUM(general_ledgers.debit), 0) as total_debit, COALESCE(SUM(general_ledgers.credit), 0) as total_credit')
            ->first();

        return [
            'debit' => (float) ($sums->total_debit ?? 0),
            'credit' => (float) ($sums->total_credit ?? 0),
        ];
    }

    /**
     * Determine if account type is Debit Normal.
     * Logic based on standard accounting rules commonly used in the system.
     *
     * Param:
     * - ?string $type: tipe data.
     *
     * Proses: Mengeksekusi pemrosesan data is debit normal pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service.
     *
     * Output: Boolean hasil proses.
     */
    private function isDebitNormal(?string $type): bool
    {
        if (! $type) {
            return true;
        }
        $type = strtolower(trim((string) $type));

        return in_array($type, ['asset', 'expense', 'cogs', 'other_expense'], true);
    }

    /**
     * Determine if account is a Profit & Loss account.
     *
     * Param:
     * - ?string $type: tipe data.
     *
     * Proses: Mengeksekusi pemrosesan data is pl account pada domain Finance/Ledger; repository menjadi pemilik query/transaksi dan mengubah hasil menjadi Entity/DTO bila dikembalikan ke service.
     *
     * Output: Boolean hasil proses.
     */
    private function isPLAccount(?string $type): bool
    {
        if (! $type) {
            return false;
        }
        $type = strtolower(trim((string) $type));

        return in_array($type, ['revenue', 'expense', 'cogs', 'other_revenue', 'other_expense'], true);
    }
}
