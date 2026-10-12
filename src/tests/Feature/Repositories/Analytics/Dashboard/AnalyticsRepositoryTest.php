<?php

namespace Tests\Feature\Repositories\Analytics\Dashboard;

use App\Modules\Analytics\Dashboard\Repositories\AnalyticsRepository;
use Tests\TestCase;

class AnalyticsRepositoryTest extends TestCase
{

    public function test_get_overview_metrics(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getOverviewMetrics');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_doctor_performance(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getDoctorPerformance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_service_profit_stats(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getServiceProfitStats');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_patient_revenue(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getPatientRevenue');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_products_by_margin(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getProductsByMargin');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_top_services(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getTopServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_monthly_series(): void
    {
        $method = new \ReflectionMethod(AnalyticsRepository::class, 'getMonthlySeries');
        $this->assertTrue($method->isPublic());
    }
}