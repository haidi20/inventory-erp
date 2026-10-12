<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\ItemConversionController;
use Tests\TestCase;

class ItemConversionControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(ItemConversionController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(ItemConversionController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(ItemConversionController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}