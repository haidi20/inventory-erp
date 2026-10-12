<?php

namespace App\Modules\Analytics\Dashboard\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    private function render(string $view, Request $request, array $extra = []): Response
    {
        return Inertia::render($view, array_merge([
            'filters' => [
                'period'   => (string) $request->string('period', now()->format('Y-m'))->trim(),
                'date_from'=> (string) $request->string('date_from')->trim(),
                'date_to'  => (string) $request->string('date_to')->trim(),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ], $extra));
    }

    public function showOverview(Request $request): Response         { return $this->render('Analytics/Dashboard/presentations/views/AnalyticsOverviewIndex', $request); }
    public function showDoctorProfit(Request $request): Response     { return $this->render('Analytics/Dashboard/presentations/views/DoctorProfitIndex', $request); }
    public function showServiceProfit(Request $request): Response    { return $this->render('Analytics/Dashboard/presentations/views/ServiceProfitIndex', $request); }
    public function showPatientProfit(Request $request): Response    { return $this->render('Analytics/Dashboard/presentations/views/PatientProfitIndex', $request); }
    public function showProductMargin(Request $request): Response    { return $this->render('Analytics/Dashboard/presentations/views/ProductMarginIndex', $request); }
    public function showBestSellingServices(Request $request): Response { return $this->render('Analytics/Dashboard/presentations/views/BestSellingServicesIndex', $request); }
    public function showProfitablePatients(Request $request): Response  { return $this->render('Analytics/Dashboard/presentations/views/ProfitablePatientsIndex', $request); }
}
