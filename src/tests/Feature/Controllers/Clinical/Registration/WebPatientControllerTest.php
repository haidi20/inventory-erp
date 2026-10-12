<?php

namespace Tests\Feature\Controllers\Clinical\Registration;

use App\Modules\Clinical\Registration\Http\Controllers\WebPatientController;
use Tests\TestCase;

class WebPatientControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebPatientController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}