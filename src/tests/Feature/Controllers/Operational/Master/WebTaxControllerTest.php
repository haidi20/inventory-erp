<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebTaxController;
use Tests\TestCase;

class WebTaxControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebTaxController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}