<?php

namespace Tests\Feature\Controllers\Analytics\Dashboard;

use App\Modules\Analytics\Dashboard\Http\Controllers\AnalyticsController;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{

    public function test_show_overview(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showOverview');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_doctor_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showDoctorProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_service_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showServiceProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_patient_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showPatientProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_product_margin(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showProductMargin');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_best_selling_services(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showBestSellingServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_profitable_patients(): void
    {
        $method = new \ReflectionMethod(AnalyticsController::class, 'showProfitablePatients');
        $this->assertTrue($method->isPublic());
    }
}