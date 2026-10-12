<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Operational\Inventory\Repositories\BarcodePrintRepository;
use Tests\TestCase;

class BarcodePrintRepositoryTest extends TestCase
{

    public function test_get_items(): void
    {
        $method = new \ReflectionMethod(BarcodePrintRepository::class, 'getItems');
        $this->assertTrue($method->isPublic());
    }
}