<?php

namespace Tests\Feature\Controllers\Clinical\Registration;

use App\Modules\Clinical\Registration\Http\Controllers\PendaftaranController;
use Tests\TestCase;

class PendaftaranControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_patient_list(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'patientList');
        $this->assertTrue($method->isPublic());
    }

    public function test_patient_show(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'patientShow');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(PendaftaranController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}