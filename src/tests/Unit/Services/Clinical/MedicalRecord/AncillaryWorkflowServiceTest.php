<?php

namespace Tests\Unit\Services\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Services\AncillaryWorkflowService;
use Tests\TestCase;

class AncillaryWorkflowServiceTest extends TestCase
{

    public function test_get_request_entry_page_data(): void
    {
        $method = new \ReflectionMethod(AncillaryWorkflowService::class, 'getRequestEntryPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_result_entry_page_data(): void
    {
        $method = new \ReflectionMethod(AncillaryWorkflowService::class, 'getResultEntryPageData');
        $this->assertTrue($method->isPublic());
    }
}