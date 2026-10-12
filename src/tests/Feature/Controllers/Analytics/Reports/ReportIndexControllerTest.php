<?php

namespace Tests\Feature\Controllers\Analytics\Reports;

use App\Modules\Analytics\Reports\Http\Controllers\ReportIndexController;
use Tests\TestCase;

class ReportIndexControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(ReportIndexController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}