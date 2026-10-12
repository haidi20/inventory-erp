<?php

namespace Tests\Feature\Controllers\Clinical\Integration;

use App\Modules\Clinical\Integration\Http\Controllers\WebSatusehatController;
use Tests\TestCase;

class WebSatusehatControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebSatusehatController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_sync(): void
    {
        $method = new \ReflectionMethod(WebSatusehatController::class, 'sync');
        $this->assertTrue($method->isPublic());
    }

    public function test_encounter(): void
    {
        $method = new \ReflectionMethod(WebSatusehatController::class, 'encounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_fhir(): void
    {
        $method = new \ReflectionMethod(WebSatusehatController::class, 'fhir');
        $this->assertTrue($method->isPublic());
    }

    public function test_config(): void
    {
        $method = new \ReflectionMethod(WebSatusehatController::class, 'config');
        $this->assertTrue($method->isPublic());
    }
}