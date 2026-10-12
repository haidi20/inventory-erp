<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Operational\Inventory\Repositories\InventoryActionRepository;
use Tests\TestCase;

class InventoryActionRepositoryTest extends TestCase
{

    public function test_create_movement(): void
    {
        $method = new \ReflectionMethod(InventoryActionRepository::class, 'createMovement');
        $this->assertTrue($method->isPublic());
    }

    public function test_build_adjustment_query(): void
    {
        $method = new \ReflectionMethod(InventoryActionRepository::class, 'buildAdjustmentQuery');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_paginated_adjustments(): void
    {
        $method = new \ReflectionMethod(InventoryActionRepository::class, 'getPaginatedAdjustments');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_by_reference(): void
    {
        $method = new \ReflectionMethod(InventoryActionRepository::class, 'deleteByReference');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_item_opening_balance(): void
    {
        $method = new \ReflectionMethod(InventoryActionRepository::class, 'deleteItemOpeningBalance');
        $this->assertTrue($method->isPublic());
    }
}