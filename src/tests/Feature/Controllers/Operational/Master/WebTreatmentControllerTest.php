<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebTreatmentController;
use Tests\TestCase;

class WebTreatmentControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebTreatmentController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}