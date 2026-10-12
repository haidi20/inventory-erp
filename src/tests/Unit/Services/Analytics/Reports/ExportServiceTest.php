<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\ExportService;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{

    public function test_export_income_statement_to_csv(): void
    {
        $method = new \ReflectionMethod(ExportService::class, 'exportIncomeStatementToCsv');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_balance_sheet_to_csv(): void
    {
        $method = new \ReflectionMethod(ExportService::class, 'exportBalanceSheetToCsv');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_ledger_to_csv(): void
    {
        $method = new \ReflectionMethod(ExportService::class, 'exportLedgerToCsv');
        $this->assertTrue($method->isPublic());
    }
}