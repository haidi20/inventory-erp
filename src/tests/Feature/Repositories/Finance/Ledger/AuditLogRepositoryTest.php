<?php

namespace Tests\Feature\Repositories\Finance\Ledger;

use App\Modules\Finance\Ledger\Repositories\AuditLogRepository;
use Tests\TestCase;

class AuditLogRepositoryTest extends TestCase
{

    public function test_create(): void
    {
        $method = new \ReflectionMethod(AuditLogRepository::class, 'create');
        $this->assertTrue($method->isPublic());
    }
}