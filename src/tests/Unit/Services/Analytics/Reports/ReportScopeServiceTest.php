<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\ReportScopeService;
use Tests\TestCase;

class ReportScopeServiceTest extends TestCase
{

    public function test_resolve_financial_report_entity(): void
    {
        $method = new \ReflectionMethod(ReportScopeService::class, 'resolveFinancialReportEntity');
        $this->assertTrue($method->isPublic());
    }

    public function test_resolve_period_uuid(): void
    {
        $method = new \ReflectionMethod(ReportScopeService::class, 'resolvePeriodUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_resolve_branch_uuid(): void
    {
        $method = new \ReflectionMethod(ReportScopeService::class, 'resolveBranchUuid');
        $this->assertTrue($method->isPublic());
    }
}