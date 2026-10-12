<?php

namespace Tests\Unit\Services\Operational\Inventory;

use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use Tests\TestCase;

class InventoryPublicServiceTest extends TestCase
{

    public function test_save_movement(): void
    {
        $method = new \ReflectionMethod(InventoryPublicService::class, 'saveMovement');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_item_opening_balance(): void
    {
        $method = new \ReflectionMethod(InventoryPublicService::class, 'deleteItemOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_make_movement_entity(): void
    {
        $method = new \ReflectionMethod(InventoryPublicService::class, 'makeMovementEntity');
        $this->assertTrue($method->isPublic());
    }
}