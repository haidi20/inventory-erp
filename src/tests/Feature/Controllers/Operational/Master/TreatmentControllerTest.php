<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\TreatmentController;
use Tests\TestCase;

class TreatmentControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(TreatmentController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(TreatmentController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(TreatmentController::class, 'show');
        $this->assertTrue($method->isPublic());
    }
}