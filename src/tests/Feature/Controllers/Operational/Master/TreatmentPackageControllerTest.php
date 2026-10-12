<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\TreatmentPackageController;
use Tests\TestCase;

class TreatmentPackageControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(TreatmentPackageController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(TreatmentPackageController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(TreatmentPackageController::class, 'show');
        $this->assertTrue($method->isPublic());
    }
}