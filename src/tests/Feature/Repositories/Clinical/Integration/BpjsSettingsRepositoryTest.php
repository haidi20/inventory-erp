<?php

namespace Tests\Feature\Repositories\Clinical\Integration;

use App\Modules\Clinical\Integration\Repositories\BpjsSettingsRepository;
use Tests\TestCase;

class BpjsSettingsRepositoryTest extends TestCase
{

    public function test_get(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsRepository::class, 'get');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }
}