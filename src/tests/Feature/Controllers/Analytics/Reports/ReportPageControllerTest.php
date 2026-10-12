<?php

namespace Tests\Feature\Controllers\Analytics\Reports;

use App\Modules\Analytics\Reports\Http\Controllers\ReportPageController;
use Tests\TestCase;

class ReportPageControllerTest extends TestCase
{

    public function test_show_cash_flow(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showCashFlow');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_examination_report(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showExaminationReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_point_of_sale_sales_report(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showPointOfSaleSalesReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_doctor_income_report(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showDoctorIncomeReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_product_vs_service_report(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showProductVsServiceReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_operational_expense_report(): void
    {
        $method = new \ReflectionMethod(ReportPageController::class, 'showOperationalExpenseReport');
        $this->assertTrue($method->isPublic());
    }
}