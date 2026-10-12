<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\ReportService;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{

    public function test_cash_flow(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'cashFlow');
        $this->assertTrue($method->isPublic());
    }

    public function test_examination_report(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'examinationReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_point_of_sale_sales_report(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'pointOfSaleSalesReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_doctor_income_report(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'doctorIncomeReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_vs_service_report(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'productVsServiceReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_operational_expense_report(): void
    {
        $method = new \ReflectionMethod(ReportService::class, 'operationalExpenseReport');
        $this->assertTrue($method->isPublic());
    }
}