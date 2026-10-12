<?php

namespace Tests\Feature\Controllers\Core\Organization;

use App\Modules\Core\Organization\Http\Controllers\WebBranchController;
use Tests\TestCase;

class WebBranchControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebBranchController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}