<?php

namespace Tests\Feature\Controllers\Analytics\Reports;

use App\Modules\Analytics\Reports\Http\Controllers\WebCashFlowController;
use Tests\TestCase;

class WebCashFlowControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebCashFlowController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_compare(): void
    {
        $method = new \ReflectionMethod(WebCashFlowController::class, 'compare');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_excel(): void
    {
        $method = new \ReflectionMethod(WebCashFlowController::class, 'exportExcel');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_pdf(): void
    {
        $method = new \ReflectionMethod(WebCashFlowController::class, 'exportPdf');
        $this->assertTrue($method->isPublic());
    }
}