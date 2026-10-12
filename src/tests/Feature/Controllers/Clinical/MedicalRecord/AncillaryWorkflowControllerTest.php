<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\AncillaryWorkflowController;
use Tests\TestCase;

class AncillaryWorkflowControllerTest extends TestCase
{

    public function test_show_request_entry(): void
    {
        $method = new \ReflectionMethod(AncillaryWorkflowController::class, 'showRequestEntry');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_result_entry(): void
    {
        $method = new \ReflectionMethod(AncillaryWorkflowController::class, 'showResultEntry');
        $this->assertTrue($method->isPublic());
    }
}