<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Tests\TestCase;

class TrialBalanceServiceTest extends TestCase
{

    public function test_generate(): void
    {
        $method = new \ReflectionMethod(TrialBalanceService::class, 'generate');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_balance(): void
    {
        $method = new \ReflectionMethod(TrialBalanceService::class, 'validateBalance');
        $this->assertTrue($method->isPublic());
    }
}