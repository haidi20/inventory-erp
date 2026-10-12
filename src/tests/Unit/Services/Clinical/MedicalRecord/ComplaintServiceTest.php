<?php

namespace Tests\Unit\Services\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Services\ComplaintService;
use Tests\TestCase;

class ComplaintServiceTest extends TestCase
{

    public function test_get_list(): void
    {
        $method = new \ReflectionMethod(ComplaintService::class, 'getList');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(ComplaintService::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_submit_complaint(): void
    {
        $method = new \ReflectionMethod(ComplaintService::class, 'submitComplaint');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(ComplaintService::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_complaint(): void
    {
        $method = new \ReflectionMethod(ComplaintService::class, 'deleteComplaint');
        $this->assertTrue($method->isPublic());
    }
}