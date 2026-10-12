<?php

namespace Tests\Unit\Services\Finance\Ledger;

use App\Modules\Finance\Ledger\Services\AuditLogService;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{

    public function test_record(): void
    {
        $method = new \ReflectionMethod(AuditLogService::class, 'record');
        $this->assertTrue($method->isPublic());
    }
}