<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use App\Modules\Clinical\Registration\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class DashboardRepository
{
    private function activeDeletedAtFilter($query): void
    {
        $query->where(function ($builder) {
            $builder->whereNull('deleted_at')
                ->orWhere('deleted_at', 0);
        });
    }

    private function dateFromUnixExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "DATE(datetime({$column}, 'unixepoch'))"
            : "DATE(FROM_UNIXTIME({$column}))";
    }

    private function monthFromUnixExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', datetime({$column}, 'unixepoch'))"
            : "DATE_FORMAT(FROM_UNIXTIME({$column}), '%Y-%m')";
    }

    private function getBranchUuid(): ?string
    {
        return auth()->user()?->branch_uuid;
    }

    public function getActiveQueueCount(string $companyUuid): int
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        $query = Registration::where('company_uuid', $companyUuid)
            ->whereBetween('registered_at', [$start, $end])
            ->whereIn('status', ['waiting', 'in_progress']);

        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }

        return $query->count();
    }

    public function getTodayServiceRevenue(string $companyUuid): float
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        $query = EncounterItem::where('company_uuid', $companyUuid)
            ->whereBetween('created_at', [$start, $end]);

        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }

        return (float) $query->sum('subtotal');
    }

    public function getCompletedServiceCountToday(string $companyUuid): int
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        $query = Registration::where('company_uuid', $companyUuid)
            ->whereBetween('registered_at', [$start, $end])
            ->where('status', 'done');

        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }

        return $query->count();
    }

    public function getTodayPosRevenue(string $companyUuid): float
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        $query = Sale::where('company_uuid', $companyUuid)
            ->whereBetween('created_at', [$start, $end]);

        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }

        return (float) $query->sum('total_amount');
    }

    public function getTodayPosItemCount(string $companyUuid): float
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        $query = SaleItem::where('company_uuid', $companyUuid)
            ->whereBetween('created_at', [$start, $end]);

        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }

        return (float) $query->sum('qty');
    }

    public function getDailyRevenueStats(string $companyUuid, int $daysLimit): array
    {
        $today = Carbon::today();
        $from = $today->copy()->subDays($daysLimit - 1)->startOfDay()->timestamp;
        $days = collect(range(0, $daysLimit - 1))->map(fn($i) => $today->copy()->subDays($daysLimit - 1 - $i)->format('Y-m-d'));
        $branchUuid = $this->getBranchUuid();

        $serviceRevenueByDayQuery = DB::table('encounter_items')
            ->selectRaw($this->dateFromUnixExpression('created_at') . ' as day, SUM(subtotal) as total')
            ->where('company_uuid', $companyUuid)
            ->where('created_at', '>=', $from);
        $this->activeDeletedAtFilter($serviceRevenueByDayQuery);
        
        if ($branchUuid) {
            $serviceRevenueByDayQuery->where('branch_uuid', $branchUuid);
        }

        $serviceRevenueByDay = $serviceRevenueByDayQuery->groupByRaw($this->dateFromUnixExpression('created_at'))
            ->pluck('total', 'day');

        $posRevenueByDayQuery = DB::table('sales')
            ->selectRaw($this->dateFromUnixExpression('sale_at') . ' as day, SUM(total_amount) as total')
            ->where('company_uuid', $companyUuid)
            ->where('sale_at', '>=', $from);
        $this->activeDeletedAtFilter($posRevenueByDayQuery);

        if ($branchUuid) {
            $posRevenueByDayQuery->where('branch_uuid', $branchUuid);
        }

        $posRevenueByDay = $posRevenueByDayQuery->groupByRaw($this->dateFromUnixExpression('sale_at'))
            ->pluck('total', 'day');

        $dailyChartLabels = $days->map(fn($d) => 'H-' . $today->diffInDays(Carbon::parse($d)))->values()->toArray();
        $dailyServiceRevenueSeries = $days->map(fn($d) => (int)($serviceRevenueByDay[$d] ?? 0))->values()->toArray();
        $dailyPosRevenueSeries = $days->map(fn($d) => (int)($posRevenueByDay[$d] ?? 0))->values()->toArray();

        return compact('dailyChartLabels', 'dailyServiceRevenueSeries', 'dailyPosRevenueSeries');
    }

    public function getMonthlyVisitStats(string $companyUuid, int $monthsLimit): array
    {
        $monthNames = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei',
                       '06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
        $today = Carbon::today();
        $months = collect(range($monthsLimit - 1, 0))->map(fn($i) => $today->copy()->subMonths($i)->format('Y-m'))->values();
        $branchUuid = $this->getBranchUuid();

        $visitsByMonthQuery = DB::table('registrations')
            ->selectRaw($this->monthFromUnixExpression('registered_at') . " as ym, COUNT(*) as cnt")
            ->where('company_uuid', $companyUuid)
            ->where('registered_at', '>=', $today->copy()->subMonths($monthsLimit - 1)->startOfMonth()->timestamp);
        $this->activeDeletedAtFilter($visitsByMonthQuery);

        if ($branchUuid) {
            $visitsByMonthQuery->where('branch_uuid', $branchUuid);
        }

        $visitsByMonth = $visitsByMonthQuery->groupByRaw($this->monthFromUnixExpression('registered_at'))
            ->pluck('cnt', 'ym');

        $monthlyChartLabels = $months->map(fn($ym) => $monthNames[substr($ym, 5, 2)] ?? $ym)->values()->toArray();
        $monthlyVisitSeries = $months->map(fn($ym) => (int)($visitsByMonth[$ym] ?? 0))->values()->toArray();

        return compact('monthlyChartLabels', 'monthlyVisitSeries');
    }

    public function getMonthlyRevenueDistribution(string $companyUuid): array
    {
        $start = Carbon::now()->startOfMonth()->timestamp;
        $end = Carbon::now()->endOfMonth()->timestamp;
        $branchUuid = $this->getBranchUuid();

        $serviceRevenueQuery = EncounterItem::where('company_uuid', $companyUuid)
            ->whereBetween('created_at', [$start, $end]);
        $posQuery = Sale::where('company_uuid', $companyUuid)
            ->whereBetween('sale_at', [$start, $end]);

        if ($branchUuid) {
            $serviceRevenueQuery->where('branch_uuid', $branchUuid);
            $posQuery->where('branch_uuid', $branchUuid);
        }

        $serviceRevenueDistribution = (float) $serviceRevenueQuery->sum('subtotal');
        $posRevenueDistribution = (float) $posQuery->sum('total_amount');

        return compact('serviceRevenueDistribution', 'posRevenueDistribution');
    }

    public function getLatestRegistrations(string $companyUuid, int $limit): Collection
    {
        $query = Registration::where('company_uuid', $companyUuid)->with('patient');
        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }
        return $query->latest('created_at')->limit($limit)->get();
    }

    public function getLatestSales(string $companyUuid, int $limit): Collection
    {
        $query = Sale::where('company_uuid', $companyUuid);
        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }
        return $query->latest('created_at')->limit($limit)->get();
    }

    public function getPatientCount(string $companyUuid): int
    {
        $query = Patient::where('company_uuid', $companyUuid);
        if ($branchUuid = $this->getBranchUuid()) {
            $query->where('branch_uuid', $branchUuid);
        }
        return $query->count();
    }
}

