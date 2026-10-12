<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\FinancialStatementMenuService;
use Tests\TestCase;

class FinancialStatementMenuServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}