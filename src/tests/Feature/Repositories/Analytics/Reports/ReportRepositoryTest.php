<?php

namespace Tests\Feature\Repositories\Analytics\Reports;

use App\Modules\Analytics\Reports\Repositories\ReportRepository;
use Tests\TestCase;

class ReportRepositoryTest extends TestCase
{

    public function test_get_cash_flow_data(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getCashFlowData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_examination_report_data(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getExaminationReportData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sales_pos_report_data(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getSalesPosReportData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_doctor_income_stats(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getDoctorIncomeStats');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_monthly_series(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getMonthlySeries');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_operational_expense_data(): void
    {
        $method = new \ReflectionMethod(ReportRepository::class, 'getOperationalExpenseData');
        $this->assertTrue($method->isPublic());
    }
}