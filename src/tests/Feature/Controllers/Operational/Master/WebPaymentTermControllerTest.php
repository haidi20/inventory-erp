<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebPaymentTermController;
use Tests\TestCase;

class WebPaymentTermControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebPaymentTermController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}