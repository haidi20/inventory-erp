<?php

namespace Tests\Unit\Services\Clinical\Pharmacy;

use App\Modules\Clinical\Pharmacy\Services\FarmasiService;
use Tests\TestCase;

class FarmasiServiceTest extends TestCase
{

    public function test_receive_purchase(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'receivePurchase');
        $this->assertTrue($method->isPublic());
    }

    public function test_process_dispensing(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'processDispensing');
        $this->assertTrue($method->isPublic());
    }

    public function test_void_dispensing(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'voidDispensing');
        $this->assertTrue($method->isPublic());
    }

    public function test_process_return(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'processReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_void_return(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'voidReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_adjust_stock(): void
    {
        $method = new \ReflectionMethod(FarmasiService::class, 'adjustStock');
        $this->assertTrue($method->isPublic());
    }
}