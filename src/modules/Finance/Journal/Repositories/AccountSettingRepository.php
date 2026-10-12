<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Shared\Support\ExceptionLogger;
use Illuminate\Database\QueryException;
use App\Modules\Operational\Master\Models\TaxSetting;
use Throwable;

/**
 * Repository untuk pengaturan akun, mapping, dan saldo awal.
 * Menggunakan migrasi:
 * - 2026_03_15_000500_create_accounting_tables.php (accounts, account_mappings, tax_settings, closing_periods, account_balances)
 */
class AccountSettingRepository
{
    /**
     * Get all accounts.
     *
     * @return Collection
     */
    public function allAccounts(): Collection
    {
        try {
            return Account::orderBy('code')->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find an account by its code.
     *
     * @param string $code
     * @return Account|null
     */
    public function findAccountByCode(string $code): ?Account
    {
        try {
            return Account::where('code', $code)->first();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find an account by its UUID.
     *
     * @param string $uuid
     * @return Account|null
     */
    public function findAccountById(string $uuid): ?Account
    {
        try {
            return Account::find($uuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Check if any of the given account UUIDs are header accounts.
     */
    public function hasHeaderAccounts(array $accountUuids): bool
    {
        try {
            return Account::whereIn('uuid', $accountUuids)
                ->where('is_header', true)
                ->exists();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get account mappings by menu name.
     */
    public function findMappingsByMenu(string $menuName): Collection
    {
        try {
            return AccountMapping::where('menu_name', $menuName)->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get account mappings by key prefix (e.g., 'penjualan.').
     */
    public function findMappingsByPrefix(string $prefix): Collection
    {
        try {
            return AccountMapping::where('key', 'like', "{$prefix}.%")->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get all account mappings.
     */
    public function getMappings(): Collection
    {
        try {
            return AccountMapping::with('account')->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update or create an account mapping.
     */
    public function updateMapping(string $key, string $accountUuid): AccountMapping
    {
        try {
            return AccountMapping::updateOrCreate(
                ['key' => $key],
                ['account_uuid' => $accountUuid]
            );
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get all tax settings.
     *
     * @return Collection
     */
    public function getTaxSettings(): Collection
    {
        try {
            return TaxSetting::with('account')->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find tax setting by name.
     */
    public function findTaxSettingByName(string $name): ?TaxSetting
    {
        try {
            return TaxSetting::where('name', $name)->first();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update or create a tax setting.
     */
    public function updateTaxSetting(string $name, float $percentage, string $accountUuid): TaxSetting
    {
        try {
            return TaxSetting::updateOrCreate(
                ['name' => $name],
                ['percentage' => $percentage, 'account_uuid' => $accountUuid, 'is_active' => true]
            );
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get opening balances for a branch.
     *
     * @param string $branchUuid
     * @return Collection
     */
    public function getOpeningBalances(string $branchUuid): Collection
    {
        try {
            return AccountBalance::where('branch_uuid', $branchUuid)
                ->where('period', 'Opening')
                ->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update an opening balance for an account.
     *
     * @param string $branchUuid
     * @param string $accountUuid
     * @param float $amount
     * @return AccountBalance
     */
    public function updateOpeningBalance(string $branchUuid, string $accountUuid, float $amount): AccountBalance
    {
        try {
            return AccountBalance::updateOrCreate(
                ['branch_uuid' => $branchUuid, 'account_uuid' => $accountUuid, 'period' => 'Opening'],
                ['opening_balance' => $amount, 'closing_balance' => $amount]
            );
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find closing period for a specific date.
     */
    public function findPeriodByDate(?string $branchUuid, string|int $date): ?ClosingPeriod
    {
        try {
            $timestamp = is_numeric($date) ? (int)$date : strtotime($date);
            
            return ClosingPeriod::where('branch_uuid', $branchUuid)
                ->where('start_date', '<=', $timestamp)
                ->where('end_date', '>=', $timestamp)
                ->first();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Get closing periods for a branch.
     *
     * @param string $branchUuid
     * @return Collection
     */
    public function getClosingPeriods(string $branchUuid): Collection
    {
        try {
            return ClosingPeriod::where('branch_uuid', $branchUuid)
                ->orderByDesc('start_date')
                ->get();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Update or create account balance and update totals.
     */
    public function updateAccountBalance(string $branchUuid, string $accountUuid, string $period, float $debit, float $credit): AccountBalance
    {
        try {
            DB::beginTransaction();
            try {
                $attributes = [
                    'branch_uuid'  => $branchUuid,
                    'account_uuid' => $accountUuid,
                    'period'     => $period,
                ];

                $defaults = [
                    'opening_balance'  => 0,
                    'total_debit'      => 0,
                    'total_credit'     => 0,
                    'closing_balance'  => 0,
                ];

                $balance = AccountBalance::withoutGlobalScopes()
                    ->where($attributes)
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    try {
                        AccountBalance::create(array_merge($attributes, $defaults));
                    } catch (QueryException $e) {
                        if (!str_contains(strtolower($e->getMessage()), 'unique')) {
                            throw $e;
                        }
                    }

                    $balance = AccountBalance::withoutGlobalScopes()
                        ->where($attributes)
                        ->lockForUpdate()
                        ->first();
                }

                if (!$balance) {
                    throw new \RuntimeException("Failed to lock account balance row for account {$accountUuid}.");
                }

                $balance->total_debit += $debit;
                $balance->total_credit += $credit;
                $balance->closing_balance = $balance->opening_balance + $balance->total_debit - $balance->total_credit;
                $balance->save();

                DB::commit();
                return $balance;
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Create or update a closing period.
     *
     * @param array $data
     * @return ClosingPeriod
     */
    public function createOrUpdateClosingPeriod(array $data): ClosingPeriod
    {
        try {
            return ClosingPeriod::updateOrCreate(
                ['branch_uuid' => $data['branch_uuid'], 'period' => $data['period']],
                $data
            );
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find period by UUID.
     */
    public function findPeriodById(string $uuid): ?ClosingPeriod
    {
        try {
            return ClosingPeriod::find($uuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    public function findTaxSettingForRate(string $name, float $rate): ?TaxSetting
    {
        $taxSetting = TaxSetting::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();

        if (!$taxSetting) {
            $taxSetting = TaxSetting::query()
                ->where('percentage', $rate)
                ->where('is_active', true)
                ->first();
        }

        return $taxSetting;
    }

    public function findPeriodByUuidAndCompany(string $uuid, string $companyUuid): ?ClosingPeriod
    {
        return ClosingPeriod::query()
            ->where('uuid', $uuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    /**
     * Find period by period string (e.g., '2026-03').
     */
    public function findPeriodByPeriod(string $branchUuid, string $period): ?ClosingPeriod
    {
        try {
            return ClosingPeriod::where('branch_uuid', $branchUuid)
                ->where('period', $period)
                ->first();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Check if any of the given account UUIDs are inactive.
     */
    public function hasInactiveAccounts(array $accountUuids): bool
    {
        try {
            return Account::whereIn('uuid', $accountUuids)
                ->where('is_active', false)
                ->exists();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find open period for a branch.
     */
    public function findOpenPeriod(string $branchUuid): ?ClosingPeriod
    {
        try {
            return ClosingPeriod::where('branch_uuid', $branchUuid)
                ->where('status', 'open')
                ->orderBy('period', 'desc')
                ->first();
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Find period by ID (Alias).
     */
    public function findPeriod(?string $branchUuid, string $periodUuid): ?ClosingPeriod
    {
        return $this->findPeriodById($periodUuid);
    }

    /**
     * Find branch by UUID.
     */
    public function findBranch(string $branchUuid): ?Branch
    {
        try {
            return Branch::find($branchUuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }
}

