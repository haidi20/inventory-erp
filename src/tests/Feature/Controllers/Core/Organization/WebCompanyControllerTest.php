<?php

namespace Tests\Feature\Controllers\Core\Organization;

use App\Modules\Core\Organization\Http\Controllers\WebCompanyController;
use Tests\TestCase;

class WebCompanyControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebCompanyController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}