<?php

namespace Tests\Unit\Services\Clinical\Integration;

use App\Modules\Clinical\Integration\Services\BpjsSettingsService;
use Tests\TestCase;

class BpjsSettingsServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsService::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsService::class, 'store');
        $this->assertTrue($method->isPublic());
    }
}