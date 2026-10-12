<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebTreatmentPackageController;
use Tests\TestCase;

class WebTreatmentPackageControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebTreatmentPackageController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}