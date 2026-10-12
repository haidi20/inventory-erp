<?php

namespace Tests\Feature\Controllers\Clinical\Pharmacy;

use App\Modules\Clinical\Pharmacy\Http\Controllers\FarmasiController;
use Tests\TestCase;

class FarmasiControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_prescriptions(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'prescriptions');
        $this->assertTrue($method->isPublic());
    }

    public function test_returns(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'returns');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_detail(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'stockDetail');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_resep(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'storeResep');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_retur(): void
    {
        $method = new \ReflectionMethod(FarmasiController::class, 'storeRetur');
        $this->assertTrue($method->isPublic());
    }
}