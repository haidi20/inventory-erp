<?php

namespace App\Modules\Finance\Ledger\Repositories;

use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Facades\DB;

class FinancialReportRepository
{
    private function resolveCompanyUuid(?string $branchUuid = null): ?string
    {
        if ($branchUuid) {
            return Branch::query()->withoutGlobalScopes()
                ->whereKey($branchUuid)
                ->value('company_uuid') ?: null;
        }

        return current_company_uuid() ?: null;
    }

    public function getIncomeStatement(string $periodUuid, ?string $branchUuid = null)
    {
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $companyUuidParam = $companyUuid ?: '0';
        $period = DB::table('closing_periods')
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('uuid', $periodUuid)
            ->where(function ($query) {
                $query->where('deleted_at', 0)->orWhereNull('deleted_at');
            })
            ->first();

        if (!$period) {
            $period = DB::table('closing_periods')
                ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
                ->where('period', $periodUuid)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->where(function ($query) {
                    $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                })
                ->first();
        }
        $branchUuidParam = $branchUuid ?: '0';

        return DB::table('accounts as a')
            ->leftJoin(DB::raw("(
                SELECT account_uuid, SUM(credit - debit) as amount
                FROM general_ledgers
                WHERE journal_date BETWEEN ? AND ?
                  AND (? = '0' OR branch_uuid = ?)
                  AND (? = '0' OR company_uuid = ?)
                  AND deleted_at = 0
                GROUP BY account_uuid
            ) as gl"), 'a.uuid', '=', 'gl.account_uuid')
            ->setBindings([
                $period->start_date,
                $period->end_date,
                $branchUuidParam,
                $branchUuidParam,
                $companyUuidParam,
                $companyUuidParam,
            ], 'join')
            ->where('a.is_header', 0)
            ->where('a.deleted_at', 0)
            ->when($companyUuid, fn ($query) => $query->where('a.company_uuid', $companyUuid))
            ->select([
                DB::raw("SUM(CASE WHEN a.type = 'revenue' THEN (COALESCE(gl.amount, 0)) ELSE 0 END) as total_revenue"),
                DB::raw("SUM(CASE WHEN a.type = 'expense' THEN (COALESCE(gl.amount, 0)) ELSE 0 END) as total_expense")
            ])->get();
    }

    public function getBalanceSheet(string|int $asOfDate, ?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        // Normalise: accept both Unix timestamp and date string
        $asOfTs = is_numeric($asOfDate) ? (int) $asOfDate : (strtotime((string) $asOfDate) ?: time());
        $formattedDate = date('Y-m-d', $asOfTs);
        $periodYm = date('Y-m', $asOfTs);

        return DB::table('accounts as a')
            ->leftJoin('account_balances as ab', function($join) use ($periodYm, $branchUuidParam, $companyUuid) {
                $join->on('a.uuid', '=', 'ab.account_uuid')
                     ->where('ab.period', '<', $periodYm)
                     ->where('ab.deleted_at', 0)   // HasSoftDeleteUnique: 0 = active
                     ->where(function($q) use ($companyUuid) {
                         if ($companyUuid) $q->where('ab.company_uuid', $companyUuid);
                     })
                     ->where(function($q) use ($branchUuidParam) {
                         if ($branchUuidParam !== '0') $q->where('ab.branch_uuid', $branchUuidParam);
                     });
            })
            ->leftJoin('general_ledgers as gl', function($join) use ($asOfTs, $branchUuidParam, $companyUuid) {
                $join->on('a.uuid', '=', 'gl.account_uuid')
                     ->where('gl.journal_date', '<=', $asOfTs)  // Unix timestamp comparison
                     ->where('gl.deleted_at', 0)                // HasSoftDeleteUnique: 0 = active
                     ->where(function($q) use ($companyUuid) {
                         if ($companyUuid) $q->where('gl.company_uuid', $companyUuid);
                     })
                     ->where(function($q) use ($branchUuidParam) {
                         if ($branchUuidParam !== '0') $q->where('gl.branch_uuid', $branchUuidParam);
                     });
            })
            ->where('a.is_header', 0)
            ->where('a.deleted_at', 0)   // HasSoftDeleteUnique: 0 = active
            ->when($companyUuid, fn ($query) => $query->where('a.company_uuid', $companyUuid))
            ->select([
                DB::raw("SUM(CASE WHEN a.type = 'asset' THEN (COALESCE(ab.closing_balance, 0) + COALESCE(gl.debit - gl.credit, 0)) ELSE 0 END) as total_assets"),
                DB::raw("SUM(CASE WHEN a.type = 'liability' THEN (COALESCE(ab.closing_balance, 0) + COALESCE(gl.credit - gl.debit, 0)) ELSE 0 END) as total_liabilities"),
                DB::raw("SUM(CASE WHEN a.type = 'equity' THEN (COALESCE(ab.closing_balance, 0) + COALESCE(gl.credit - gl.debit, 0)) ELSE 0 END) as total_equity")
            ])->get();
    }

    public function getCashFlowIndirect(string $periodUuid, ?string $branchUuid = null)
    {
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $period = DB::table('closing_periods')
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('uuid', $periodUuid)
            ->whereNull('deleted_at')
            ->first();
        $branchUuidParam = $branchUuid ?: '0';

        // Simplified implementation of the indirect method
        $equityInfo = $this->getEquityStatement($periodUuid, $branchUuid);
        $netIncome = $equityInfo['net_income'];

        $operatingChanges = DB::table('general_ledgers as gl')
            ->join('accounts as a', 'gl.account_uuid', '=', 'a.uuid')
            ->whereBetween('gl.journal_date', [$period->start_date, $period->end_date])
            ->whereNull('gl.deleted_at')
            ->whereNull('a.deleted_at')
            ->when($companyUuid, function ($query) use ($companyUuid) {
                $query->where('gl.company_uuid', $companyUuid);
            })
            ->where(function($q) use ($branchUuidParam) {
                 if ($branchUuidParam !== '0') $q->where('gl.branch_uuid', $branchUuidParam);
            })
            ->select([
                DB::raw("SUM(CASE WHEN a.code = '1201' THEN (debit - credit) ELSE 0 END) as increase_in_receivables"),
                DB::raw("SUM(CASE WHEN a.code IN ('2101', '2102') THEN (credit - debit) ELSE 0 END) as increase_in_payables")
            ])->first();

        return [
            'net_income' => $netIncome,
            'increase_in_receivables' => $operatingChanges->increase_in_receivables ?? 0,
            'increase_in_payables' => $operatingChanges->increase_in_payables ?? 0,
            'net_operating_cash' => $netIncome - ($operatingChanges->increase_in_receivables ?? 0) + ($operatingChanges->increase_in_payables ?? 0)
        ];
    }

    public function getAdjustedTrialBalance(string $periodUuid, ?string $branchUuid = null)
    {
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $period = DB::table('closing_periods')
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('uuid', $periodUuid)
            ->where(function ($query) {
                $query->where('deleted_at', 0)->orWhereNull('deleted_at');
            })
            ->first();

        if (!$period) {
            $period = DB::table('closing_periods')
                ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
                ->where('period', $periodUuid)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->where(function ($query) {
                    $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                })
                ->first();
        }

        if (!$period) {
            return [];
        }

        $branchUuidParam = $branchUuid ?: '0';
        $companyUuidParam = $companyUuid ?: '0';

        return DB::select("
            SELECT 
                a.code,
                a.name,
                SUM(CASE WHEN j.reference_type != 'adjustment' THEN l.debit ELSE 0 END) as debit,
                SUM(CASE WHEN j.reference_type != 'adjustment' THEN l.credit ELSE 0 END) as credit,
                SUM(CASE WHEN j.reference_type = 'adjustment' THEN l.debit ELSE 0 END) as adjusted_debit,
                SUM(CASE WHEN j.reference_type = 'adjustment' THEN l.credit ELSE 0 END) as adjusted_credit,
                SUM(l.debit - l.credit) as balance
            FROM accounts a
            JOIN journal_lines l ON a.uuid = l.account_uuid
            JOIN journals j ON l.journal_uuid = j.uuid
            WHERE j.journal_date <= ?
              AND (? = '0' OR j.branch_uuid = ?)
              AND (? = '0' OR j.company_uuid = ?)
              AND (? = '0' OR a.company_uuid = ?)
              AND j.status = 'posted'
              AND a.deleted_at = 0
              AND l.deleted_at = 0
              AND j.deleted_at = 0
            GROUP BY a.uuid, a.code, a.name
        ", [
            $period->end_date,
            $branchUuidParam,
            $branchUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
        ]);
    }

    public function getEquityStatement(string $periodUuid, ?string $branchUuid = null)
    {
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $period = DB::table('closing_periods')
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('uuid', $periodUuid)
            ->where(function ($query) {
                $query->where('deleted_at', 0)->orWhereNull('deleted_at');
            })
            ->first();

        if (!$period) {
            $period = DB::table('closing_periods')
                ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
                ->where('period', $periodUuid)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->where(function ($query) {
                    $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                })
                ->first();
        }

        if (!$period) {
            return [
                'period_info' => ['uuid' => $periodUuid],
                'branch_info' => ['uuid' => $branchUuid],
                'initial_equity' => 0,
                'net_income' => 0,
                'additional_capital' => 0,
                'prive' => 0,
                'final_equity' => 0,
                'details' => [],
            ];
        }

        $branchUuidParam = $branchUuid ?: '0';
        $companyUuidParam = $companyUuid ?: '0';
        
        $results = DB::select("
            SELECT 
                SUM(CASE WHEN a.code = '3101' THEN (l.credit - l.debit) ELSE 0 END) as additional_capital,
                SUM(CASE WHEN a.code = '3102' THEN (l.debit - l.credit) ELSE 0 END) as prive,
                SUM(CASE WHEN a.type IN ('revenue', 'expense') THEN (l.credit - l.debit) ELSE 0 END) as net_income
            FROM journals j
            JOIN journal_lines l ON j.uuid = l.journal_uuid
            JOIN accounts a ON l.account_uuid = a.uuid
            WHERE j.journal_date >= ? AND j.journal_date <= ?
              AND (? = '0' OR j.branch_uuid = ?)
              AND (? = '0' OR j.company_uuid = ?)
              AND (? = '0' OR a.company_uuid = ?)
              AND j.status = 'posted'
              AND (j.deleted_at = 0 OR j.deleted_at IS NULL)
              AND (l.deleted_at = 0 OR l.deleted_at IS NULL)
              AND (a.deleted_at = 0 OR a.deleted_at IS NULL)
        ", [
            $period->start_date,
            $period->end_date,
            $branchUuidParam,
            $branchUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
        ]);

        $row = $results[0];
        
        return [
            'period_info' => ['uuid' => $periodUuid],
            'branch_info' => ['uuid' => $branchUuidParam],
            'initial_equity' => 0,
            'net_income' => (float)($row->net_income ?? 0),
            'additional_capital' => (float)($row->additional_capital ?? 0),
            'prive' => (float)($row->prive ?? 0),
            'final_equity' => (float)(($row->net_income ?? 0) + ($row->additional_capital ?? 0) - ($row->prive ?? 0)),
            'details' => $results
        ];
    }

    public function getARAging(?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $date30 = strtotime('-30 days');
        $date60 = strtotime('-60 days');

        return DB::table('journals as j')
            ->join('journal_lines as l', 'j.uuid', '=', 'l.journal_uuid')
            ->join('accounts as a', 'l.account_uuid', '=', 'a.uuid')
            ->join('patients as p', 'j.reference_uuid', '=', 'p.uuid')
            ->where('a.code', '1201')
            ->where('j.deleted_at', 0)
            ->where('l.deleted_at', 0)
            ->where('a.deleted_at', 0)
            ->where('p.deleted_at', 0)
            ->when($companyUuid, fn ($query) => $query->where('j.company_uuid', $companyUuid))
            ->where(function ($q) use ($branchUuidParam) {
                if ($branchUuidParam !== '0') $q->where('j.branch_uuid', $branchUuidParam);
            })
            ->where('j.status', 'posted')
            ->select([
                'p.name as customer_name',
                DB::raw("SUM(CASE WHEN j.journal_date >= {$date30} THEN (l.debit - l.credit) ELSE 0 END) as bucket_0_30"),
                DB::raw("SUM(CASE WHEN j.journal_date < {$date30} AND j.journal_date >= {$date60} THEN (l.debit - l.credit) ELSE 0 END) as bucket_31_60"),
                DB::raw("SUM(CASE WHEN j.journal_date < {$date60} THEN (l.debit - l.credit) ELSE 0 END) as bucket_over_60"),
                DB::raw("SUM(l.debit - l.credit) as total_balance")
            ])
            ->groupBy('p.name')
            ->get();
    }

    public function getAPAging(?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuid = $this->resolveCompanyUuid($branchUuid);
        $date30 = strtotime('-30 days');
        $date60 = strtotime('-60 days');

        return DB::table('journals as j')
            ->join('journal_lines as l', 'j.uuid', '=', 'l.journal_uuid')
            ->join('accounts as a', 'l.account_uuid', '=', 'a.uuid')
            ->join('suppliers as s', 'j.reference_uuid', '=', 's.uuid')
            ->where('a.code', '2101')
            ->where('j.deleted_at', 0)
            ->where('l.deleted_at', 0)
            ->where('a.deleted_at', 0)
            ->where('s.deleted_at', 0)
            ->when($companyUuid, fn ($query) => $query->where('j.company_uuid', $companyUuid))
            ->where(function ($q) use ($branchUuidParam) {
                if ($branchUuidParam !== '0') $q->where('j.branch_uuid', $branchUuidParam);
            })
            ->where('j.status', 'posted')
            ->select([
                's.name as vendor_name',
                DB::raw("SUM(CASE WHEN j.journal_date >= {$date30} THEN (l.credit - l.debit) ELSE 0 END) as bucket_0_30"),
                DB::raw("SUM(CASE WHEN j.journal_date < {$date30} AND j.journal_date >= {$date60} THEN (l.credit - l.debit) ELSE 0 END) as bucket_31_60"),
                DB::raw("SUM(CASE WHEN j.journal_date < {$date60} THEN (l.credit - l.debit) ELSE 0 END) as bucket_over_60"),
                DB::raw("SUM(l.credit - l.debit) as total_balance")
            ])
            ->groupBy('s.name')
            ->get();
    }

    public function getInventoryReport(?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuidParam = $this->resolveCompanyUuid($branchUuid) ?: '0';
        return DB::select("
            SELECT 
                pr.name as item_name,
                COALESCE(st.total_stock, 0) as stock_on_hand,
                AVG(l.debit - l.credit) as average_price,
                SUM(l.debit - l.credit) as total_value
            FROM journals j
            JOIN journal_lines l ON j.uuid = l.journal_uuid
            JOIN items pr ON j.reference_uuid = pr.uuid
            LEFT JOIN (
                SELECT item_uuid, SUM(on_hand_qty) as total_stock
                FROM item_stocks
                WHERE deleted_at = 0 OR deleted_at IS NULL
                GROUP BY item_uuid
            ) st ON st.item_uuid = pr.uuid
            WHERE (? = '0' OR j.branch_uuid = ?)
              AND (? = '0' OR j.company_uuid = ?)
              AND (? = '0' OR pr.company_uuid = ?)
              AND j.status = 'posted'
              AND (j.deleted_at = 0 OR j.deleted_at IS NULL)
              AND (l.deleted_at = 0 OR l.deleted_at IS NULL)
              AND (pr.deleted_at = 0 OR pr.deleted_at IS NULL)
            GROUP BY pr.uuid, pr.name, st.total_stock
        ", [
            $branchUuidParam,
            $branchUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
        ]);
    }

    public function getFixedAssetReport(?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuidParam = $this->resolveCompanyUuid($branchUuid) ?: '0';
        return DB::select("
            SELECT 
                a.name as asset_name,
                SUM(l.debit) as cost,
                SUM(l.credit) as accumulated_depreciation,
                SUM(l.debit - l.credit) as net_book_value
            FROM journals j
            JOIN journal_lines l ON j.uuid = l.journal_uuid
            JOIN accounts a ON l.account_uuid = a.uuid
            WHERE a.type = 'asset' AND (a.code LIKE '13%' OR a.name LIKE '%Peralatan%')
              AND (? = '0' OR j.branch_uuid = ?)
              AND (? = '0' OR j.company_uuid = ?)
              AND (? = '0' OR a.company_uuid = ?)
              AND j.status = 'posted'
              AND j.deleted_at IS NULL
              AND l.deleted_at IS NULL
              AND a.deleted_at IS NULL
            GROUP BY a.uuid, a.name
        ", [
            $branchUuidParam,
            $branchUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
        ]);
    }

    public function getTaxReport(string $periodUuid, ?string $branchUuid = null)
    {
        $branchUuidParam = $branchUuid ?: '0';
        $companyUuidParam = $this->resolveCompanyUuid($branchUuid) ?: '0';
        return DB::select("
            SELECT 
                a.name as tax_type,
                SUM(l.debit - l.credit) as tax_amount
            FROM journals j
            JOIN journal_lines l ON j.uuid = l.journal_uuid
            JOIN accounts a ON l.account_uuid = a.uuid
            JOIN closing_periods cp ON j.journal_date BETWEEN cp.start_date AND cp.end_date
            WHERE cp.uuid = ?
              AND a.code IN ('1106', '2106', '2107')
              AND (? = '0' OR j.branch_uuid = ?)
              AND (? = '0' OR j.company_uuid = ?)
              AND (? = '0' OR a.company_uuid = ?)
              AND (? = '0' OR cp.company_uuid = ?)
              AND j.status = 'posted'
              AND (j.deleted_at = 0 OR j.deleted_at IS NULL)
              AND (l.deleted_at = 0 OR l.deleted_at IS NULL)
              AND (a.deleted_at = 0 OR a.deleted_at IS NULL)
              AND (cp.deleted_at = 0 OR cp.deleted_at IS NULL)
            GROUP BY a.uuid, a.name
        ", [
            $periodUuid,
            $branchUuidParam,
            $branchUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
            $companyUuidParam,
        ]);
    }
}
