<?php

namespace App\Modules\Analytics\Dashboard\Repositories;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository
{
    public function getOverviewMetrics(): array
    {
        $serviceRevenue = EncounterItem::query()
            ->where('item_type', 'service')
            ->sum('subtotal');

        $productRevenue = SaleItem::query()
            ->where('item_type', 'product')
            ->sum('subtotal');

        return [
            'serviceRevenue' => $serviceRevenue,
            'productRevenue' => $productRevenue,
            'totalRevenue' => $serviceRevenue + $productRevenue,
            'totalServices' => Encounter::query()->count(),
            'totalPatients' => Patient::query()->count(),
        ];
    }

    public function getDoctorPerformance(int $limit = 10)
    {
        return DB::table('encounters')
            ->join('employees', 'employees.uuid', '=', 'encounters.doctor_uuid')
            ->join('encounter_items', 'encounter_items.encounter_uuid', '=', 'encounters.uuid')
            ->where('encounter_items.item_type', 'service')
            ->whereNull('encounters.deleted_at')
            ->whereNull('employees.deleted_at')
            ->whereNull('encounter_items.deleted_at')
            ->select('employees.name', DB::raw('count(encounter_items.uuid) as service_count'), DB::raw('sum(encounter_items.subtotal) as revenue'))
            ->groupBy('employees.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function getServiceProfitStats(int $limit = 10)
    {
        return DB::table('encounter_items')
            ->join('services', 'services.uuid', '=', 'encounter_items.item_uuid')
            ->where('encounter_items.item_type', 'service')
            ->whereNull('encounter_items.deleted_at')
            ->whereNull('services.deleted_at')
            ->select('services.name', DB::raw('count(*) as transaction_count'), DB::raw('sum(encounter_items.subtotal) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function getPatientRevenue()
    {
        $serviceTotals = DB::table('encounter_items')
            ->join('encounters', 'encounters.uuid', '=', 'encounter_items.encounter_uuid')
            ->join('patients', 'patients.uuid', '=', 'encounters.patient_uuid')
            ->where('encounter_items.item_type', 'service')
            ->whereNull('encounter_items.deleted_at')
            ->whereNull('encounters.deleted_at')
            ->whereNull('patients.deleted_at')
            ->select('patients.uuid', 'patients.name', DB::raw('sum(encounter_items.subtotal) as total_service_amount'), DB::raw('count(encounter_items.uuid) as service_count'))
            ->groupBy('patients.uuid', 'patients.name')
            ->get()
            ->keyBy('uuid');

        $saleTotals = DB::table('sales')
            ->join('patients', 'patients.uuid', '=', 'sales.patient_uuid')
            ->whereNull('sales.deleted_at')
            ->whereNull('patients.deleted_at')
            ->select('patients.uuid', 'patients.name', DB::raw('sum(sales.total_amount) as total_sales_amount'), DB::raw('count(sales.uuid) as sales_count'))
            ->groupBy('patients.uuid', 'patients.name')
            ->get()
            ->keyBy('uuid');

        $ids = $serviceTotals->keys()->merge($saleTotals->keys())->unique();
        $rows = collect();

        foreach ($ids as $uuid) {
            $service = $serviceTotals->get($uuid);
            $sale = $saleTotals->get($uuid);
            $total = ($service?->total_service_amount ?? 0) + ($sale?->total_sales_amount ?? 0);
            $count = ($service?->service_count ?? 0) + ($sale?->sales_count ?? 0);

            $rows->push((object) [
                'uuid' => $uuid,
                'name' => $service?->name ?? $sale?->name ?? '-',
                'total' => $total,
                'count' => $count,
                'average' => $count > 0 ? $total / $count : 0,
            ]);
        }

        return $rows->sortByDesc('total')->values();
    }

    public function getProductsByMargin(int $limit = 10)
    {
        return Product::query()
            ->orderBy('price_sell', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTopServices(int $limit = 10)
    {
        return DB::table('encounter_items')
            ->join('services', 'services.uuid', '=', 'encounter_items.item_uuid')
            ->where('encounter_items.item_type', 'service')
            ->whereNull('encounter_items.deleted_at')
            ->whereNull('services.deleted_at')
            ->select('services.name', DB::raw('count(*) as transaction_count'))
            ->groupBy('services.name')
            ->orderByDesc('transaction_count')
            ->limit($limit)
            ->get();
    }

    public function getMonthlySeries(int $months): array
    {
        $labels = [];
        $serviceSeries = [];
        $productSeries = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();

            $labels[] = $start->format('M Y');
            $serviceSeries[] = EncounterItem::query()
                ->where('item_type', 'service')
                ->whereBetween('created_at', [$start->timestamp, $end->timestamp])
                ->sum('subtotal');
            $productSeries[] = SaleItem::query()
                ->where('item_type', 'product')
                ->whereBetween('created_at', [$start->timestamp, $end->timestamp])
                ->sum('subtotal');
        }

        return [
            'labels' => $labels,
            'service' => $serviceSeries,
            'product' => $productSeries,
        ];
    }
}
