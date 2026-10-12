<?php

namespace Tests\Unit\Services\Analytics\Dashboard;

use App\Modules\Analytics\Dashboard\Services\AnalyticsService;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{

    public function test_overview(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'overview');
        $this->assertTrue($method->isPublic());
    }

    public function test_doctor_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'doctorProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_service_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'serviceProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_patient_profit(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'patientProfit');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_margin(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'productMargin');
        $this->assertTrue($method->isPublic());
    }

    public function test_best_selling_services(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'bestSellingServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_profitable_patients(): void
    {
        $method = new \ReflectionMethod(AnalyticsService::class, 'profitablePatients');
        $this->assertTrue($method->isPublic());
    }
}