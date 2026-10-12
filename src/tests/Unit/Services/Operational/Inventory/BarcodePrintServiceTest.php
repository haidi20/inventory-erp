<?php

namespace Tests\Unit\Services\Operational\Inventory;

use App\Modules\Operational\Inventory\Services\BarcodePrintService;
use Tests\TestCase;

class BarcodePrintServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BarcodePrintService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}