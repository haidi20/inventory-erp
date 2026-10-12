<?php

namespace Tests\Feature\Repositories\Analytics\Reports;

use App\Modules\Analytics\Reports\Repositories\FinancialStatementMenuRepository;
use Tests\TestCase;

class FinancialStatementMenuRepositoryTest extends TestCase
{

    public function test_get_active_period(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuRepository::class, 'getActivePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_account_count(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuRepository::class, 'getAccountCount');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_journal_summary(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuRepository::class, 'getJournalSummary');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balance_coverage(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuRepository::class, 'getBalanceCoverage');
        $this->assertTrue($method->isPublic());
    }
}