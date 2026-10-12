<?php

namespace Tests\Feature\Controllers\Finance\Journal;

use App\Modules\Finance\Journal\Http\Controllers\CashBankController;
use Tests\TestCase;

class CashBankControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(CashBankController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}