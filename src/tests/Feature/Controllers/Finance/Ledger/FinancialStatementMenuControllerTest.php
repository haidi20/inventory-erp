<?php

namespace Tests\Feature\Controllers\Finance\Ledger;

use App\Modules\Finance\Ledger\Http\Controllers\FinancialStatementMenuController;
use Tests\TestCase;

class FinancialStatementMenuControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(FinancialStatementMenuController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}