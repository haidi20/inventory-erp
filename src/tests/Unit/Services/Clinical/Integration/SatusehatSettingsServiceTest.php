<?php

namespace Tests\Unit\Services\Clinical\Integration;

use App\Modules\Clinical\Integration\Services\SatusehatSettingsService;
use Tests\TestCase;

class SatusehatSettingsServiceTest extends TestCase
{

    public function test_get_settings(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsService::class, 'getSettings');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_settings(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsService::class, 'saveSettings');
        $this->assertTrue($method->isPublic());
    }
}