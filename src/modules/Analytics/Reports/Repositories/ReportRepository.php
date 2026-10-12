<?php

namespace App\Modules\Analytics\Reports\Repositories;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    public function getCashFlowData(): array
    {
        $sales = Sale::query()->orderBy('created_at', 'desc')->limit(10)->get();
        $purchases = DB::table('purchases')->orderBy('purchase_date', 'desc')->limit(10)->get();
        $entries = [];

        foreach ($sales as $sale) {
            $entries[] = [
                'date' => $sale->created_at,
                'ref' => $sale->invoice_no ?? $sale->sale_no,
                'desc' => 'Penerimaan kas penjualan POS',
                'in' => $sale->total_amount,
                'out' => 0,
            ];
        }

        foreach ($purchases as $purchase) {
            $entries[] = [
                'date' => Carbon::parse($purchase->purchase_date),
                'ref' => $purchase->invoice_no,
                'desc' => 'Pembayaran pembelian supplier',
                'in' => 0,
                'out' => $purchase->total,
            ];
        }

        usort($entries, fn ($a, $b) => $b['date'] <=> $a['date']);

        return [
            'entries' => $entries,
            'cashIn' => $sales->sum('total_amount'),
            'cashOut' => collect($purchases)->sum('total'),
        ];
    }

    public function getExaminationReportData(): array
    {
        return [
            'encounters' => Encounter::query()
                ->with(['patient', 'doctor'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'encounterItems' => EncounterItem::query()
                ->where('item_type', 'service')
                ->get()
                ->groupBy('encounter_uuid'),
        ];
    }

    public function getSalesPosReportData(): array
    {
        $sales = Sale::query()
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $saleItems = SaleItem::query()
            ->where('item_type', 'product')
            ->get()
            ->groupBy('sale_uuid');

        return [
            'sales' => $sales,
            'saleItems' => $saleItems,
            'totalSales' => $sales->sum('total_amount'),
            'totalItems' => $saleItems->flatten()->sum('qty'),
        ];
    }

    public function getDoctorIncomeStats()
    {
        return DB::table('encounters')
            ->join('employees', 'employees.uuid', '=', 'encounters.doctor_uuid')
            ->join('encounter_items', 'encounter_items.encounter_uuid', '=', 'encounters.uuid')
            ->where('encounter_items.item_type', 'service')
            ->select('employees.name', DB::raw('count(encounter_items.uuid) as service_count'), DB::raw('sum(encounter_items.subtotal) as revenue'))
            ->groupBy('employees.name')
            ->orderByDesc('revenue')
            ->limit(10)
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

    public function getOperationalExpenseData(): array
    {
        $expenses = JournalLine::query()
            ->join('accounts', 'accounts.uuid', '=', 'journal_lines.account_uuid')
            ->where('accounts.type', 'expense')
            ->select('accounts.name', DB::raw('sum(journal_lines.debit) as total'))
            ->groupBy('accounts.name')
            ->orderByDesc('total')
            ->get();

        return [
            'expenses' => $expenses,
            'totalExpense' => $expenses->sum('total'),
        ];
    }
}
