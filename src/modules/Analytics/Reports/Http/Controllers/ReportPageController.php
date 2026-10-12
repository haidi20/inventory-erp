<?php

namespace App\Modules\Analytics\Reports\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportPageController extends Controller
{
    private function render(string $view, Request $request): Response
    {
        return Inertia::render($view, [
            'filters' => [
                'period'   => (string) $request->string('period', now()->format('Y-m'))->trim(),
                'date_from'=> (string) $request->string('date_from')->trim(),
                'date_to'  => (string) $request->string('date_to')->trim(),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function showCashFlow(Request $request): Response               { return $this->render('Analytics/Reports/presentations/views/CashFlowReportIndex', $request); }
    public function showExaminationReport(Request $request): Response      { return $this->render('Analytics/Reports/presentations/views/ExaminationReportIndex', $request); }
    public function showPointOfSaleSalesReport(Request $request): Response { return $this->render('Analytics/Reports/presentations/views/PossSalesReportIndex', $request); }
    public function showDoctorIncomeReport(Request $request): Response     { return $this->render('Analytics/Reports/presentations/views/DoctorIncomeReportIndex', $request); }
    public function showProductVsServiceReport(Request $request): Response { return $this->render('Analytics/Reports/presentations/views/ProductVsServiceReportIndex', $request); }
    public function showOperationalExpenseReport(Request $request): Response { return $this->render('Analytics/Reports/presentations/views/OperationalExpenseReportIndex', $request); }
}
