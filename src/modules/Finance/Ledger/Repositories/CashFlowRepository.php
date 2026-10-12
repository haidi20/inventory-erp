<?php
namespace App\Modules\Finance\Ledger\Repositories;

use App\Modules\Finance\Ledger\Entities\CashFlowEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CashFlowRepository
 * 
 * Handles database operations for the Cash Flow Statement.
 */
class CashFlowRepository
{
    /**
     * Get the indirect cash flow entity from stored procedure.
     */
    public function getIndirectCashFlow(string|int $periodId, ?string $branchId = null): CashFlowEntity
    {
        if (DB::connection()->getDriverName() === 'sqlite' || !is_int($periodId)) {
            return $this->calculateIndirectCashFlow((string) $periodId, $branchId);
        }

        $result = DB::selectOne("CALL sp_get_cash_flow_indirect(?, ?)", [$periodId, $branchId]);
        
        // Debugging
        if (app()->environment('testing')) {
             \Log::info('SP Result:', (array)$result);
        }

        if (!$result) {
            return new CashFlowEntity();
        }

        return CashFlowEntity::fromArray([
            'operating_activities' => [
                ['name' => 'Laba Bersih', 'amount' => (float)$result->net_income],
                ['name' => 'Penyusutan dan Amortisasi', 'amount' => (float)$result->depreciation_amortization],
                ['name' => 'Kenaikan/Penurunan Piutang', 'amount' => -(float)$result->increase_in_receivables],
                ['name' => 'Kenaikan/Penurunan Persediaan', 'amount' => -(float)$result->increase_in_inventory],
                ['name' => 'Kenaikan/Penurunan Utang', 'amount' => (float)$result->increase_in_payables],
            ],
            'operating_total' => (float)$result->net_operating_cash,
            'investing_activities' => [
                ['name' => 'Arus Kas Bersih dari Aktivitas Investasi', 'amount' => (float)$result->net_investing_cash]
            ],
            'investing_total' => (float)$result->net_investing_cash,
            'financing_activities' => [
                ['name' => 'Arus Kas Bersih dari Aktivitas Pendanaan', 'amount' => (float)$result->net_financing_cash]
            ],
            'financing_total' => (float)$result->net_financing_cash,
            'net_cash_flow' => (float)$result->net_operating_cash + (float)$result->net_investing_cash + (float)$result->net_financing_cash,
            'beginning_cash' => (float)$result->beginning_cash_balance,
            'ending_cash' => (float)$result->ending_cash_balance,
        ]);
    }

    /**
     * Get beginning cash balance for a period.
     */
    public function getBeginningCashBalance(int $periodId, ?int $branchId = null): float
    {
        $period = ClosingPeriod::findOrFail($periodId);
        
        // Sum all cash accounts opening balance
        return (float) AccountBalance::where('period', $period->period)
            ->whereHas('account', function($q) {
                $q->where('type', 'asset')->where('name', 'LIKE', '%Kas%');
            })
            ->when($branchId, fn($q) => $q->where('branch_uuid', $branchId))
            ->sum('opening_balance');
    }

    /**
     * Get ending cash balance for a period.
     */
    public function getEndingCashBalance(int $periodId, ?int $branchId = null): float
    {
        $period = ClosingPeriod::findOrFail($periodId);
        
        return (float) AccountBalance::where('period', $period->period)
            ->whereHas('account', function($q) {
                $q->where('type', 'asset')->where('name', 'LIKE', '%Kas%');
            })
            ->when($branchId, fn($q) => $q->where('branch_uuid', $branchId))
            ->sum('closing_balance');
    }

    /**
     * Get depreciation expense for a period.
     */
    public function getDepreciationExpense(int $periodId, ?int $branchId = null): float
    {
        $period = ClosingPeriod::findOrFail($periodId);
        
        return (float) GeneralLedger::whereBetween('journal_date', [$period->start_date, $period->end_date])
            ->whereHas('account', function($q) {
                $q->where('code', '5103')->orWhere('name', 'LIKE', '%Penyusutan%');
            })
            ->when($branchId, fn($q) => $q->where('branch_uuid', $branchId))
            ->sum('debit');
    }

    /**
     * Get the direct cash flow entity from stored procedure.
     */
    public function getDirectCashFlow(string|int $periodId, ?string $branchId = null): CashFlowEntity
    {
        if (DB::connection()->getDriverName() === 'sqlite' || !is_int($periodId)) {
            return $this->calculateDirectCashFlow((string) $periodId, $branchId);
        }

        $result = DB::selectOne("CALL sp_get_cash_flow_direct(?, ?)", [$periodId, $branchId]);

        if (!$result) {
            return new CashFlowEntity();
        }

        return CashFlowEntity::fromArray([
            'operating_activities' => [
                ['name' => 'Penerimaan Kas dari Pelanggan', 'amount' => (float)$result->receipts_from_customers],
                ['name' => 'Pembayaran Kas kepada Pemasok & Karyawan', 'amount' => -(float)$result->payments_to_suppliers_employees],
            ],
            'operating_total' => (float)$result->net_operating_cash,
            'investing_activities' => [
                ['name' => 'Arus Kas Bersih dari Aktivitas Investasi', 'amount' => (float)$result->net_investing_cash]
            ],
            'investing_total' => (float)$result->net_investing_cash,
            'financing_activities' => [
                ['name' => 'Arus Kas Bersih dari Aktivitas Pendanaan', 'amount' => (float)$result->net_financing_cash]
            ],
            'financing_total' => (float)$result->net_financing_cash,
            'net_cash_flow' => (float)$result->net_operating_cash + (float)$result->net_investing_cash + (float)$result->net_financing_cash,
            'beginning_cash' => (float)$result->beginning_cash_balance,
            'ending_cash' => (float)$result->ending_cash_balance,
        ]);
    }

    private function calculateIndirectCashFlow(string $periodUuid, ?string $branchUuid = null): CashFlowEntity
    {
        $period = ClosingPeriod::withoutGlobalScopes()->where('uuid', $periodUuid)->firstOrFail();
        $baseQuery = DB::table('general_ledgers as gl')
            ->join('accounts as a', 'gl.account_uuid', '=', 'a.uuid')
            ->whereBetween('gl.journal_date', [(int) $period->start_date, (int) $period->end_date])
            ->where('gl.deleted_at', 0)
            ->where('a.deleted_at', 0)
            ->when($branchUuid, fn ($query) => $query->where('gl.branch_uuid', $branchUuid));

        $netIncome = (clone $baseQuery)
            ->whereIn('a.type', ['revenue', 'expense'])
            ->selectRaw("SUM(CASE WHEN a.type = 'revenue' THEN gl.credit - gl.debit ELSE gl.credit - gl.debit END) as amount")
            ->value('amount') ?? 0;

        $depreciation = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('a.code', '5103')->orWhere('a.name', 'like', '%Penyusutan%');
            })
            ->sum('gl.debit');

        $receivables = (clone $baseQuery)
            ->where('a.code', '1201')
            ->selectRaw('SUM(gl.debit - gl.credit) as amount')
            ->value('amount') ?? 0;

        $payables = (clone $baseQuery)
            ->whereIn('a.code', ['2101', '2102'])
            ->selectRaw('SUM(gl.credit - gl.debit) as amount')
            ->value('amount') ?? 0;

        $investing = -1 * (float) ((clone $baseQuery)
            ->where('a.code', '1301')
            ->selectRaw('SUM(gl.debit - gl.credit) as amount')
            ->value('amount') ?? 0);

        $financing = (float) ((clone $baseQuery)
            ->where('a.code', '2201')
            ->selectRaw('SUM(gl.credit - gl.debit) as amount')
            ->value('amount') ?? 0);

        $operating = (float) $netIncome + (float) $depreciation - (float) $receivables + (float) $payables;

        return CashFlowEntity::fromArray([
            'operating_activities' => [
                ['name' => 'Laba Bersih', 'amount' => (float) $netIncome],
                ['name' => 'Penyusutan dan Amortisasi', 'amount' => (float) $depreciation],
                ['name' => 'Kenaikan/Penurunan Piutang', 'amount' => -1 * (float) $receivables],
                ['name' => 'Kenaikan/Penurunan Utang', 'amount' => (float) $payables],
            ],
            'operating_total' => $operating,
            'investing_total' => $investing,
            'financing_total' => $financing,
            'net_cash_flow' => $operating + $investing + $financing,
        ]);
    }

    private function calculateDirectCashFlow(string $periodUuid, ?string $branchUuid = null): CashFlowEntity
    {
        $period = ClosingPeriod::withoutGlobalScopes()->where('uuid', $periodUuid)->firstOrFail();
        $cashQuery = DB::table('general_ledgers as gl')
            ->join('accounts as a', 'gl.account_uuid', '=', 'a.uuid')
            ->whereBetween('gl.journal_date', [(int) $period->start_date, (int) $period->end_date])
            ->where('gl.deleted_at', 0)
            ->where('a.deleted_at', 0)
            ->where('a.code', '1101')
            ->when($branchUuid, fn ($query) => $query->where('gl.branch_uuid', $branchUuid));

        $receipts = (float) (clone $cashQuery)->sum('gl.debit');
        $payments = -1 * (float) (clone $cashQuery)->sum('gl.credit');
        $operating = $receipts + $payments;

        return CashFlowEntity::fromArray([
            'operating_activities' => [
                ['name' => 'Penerimaan Kas dari Pelanggan', 'amount' => $receipts],
                ['name' => 'Pembayaran Kas kepada Pemasok & Karyawan', 'amount' => $payments],
            ],
            'operating_total' => $operating,
            'net_cash_flow' => $operating,
        ]);
    }
}
