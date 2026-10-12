<?php

namespace Tests\Feature\Repositories\Clinical\Integration;

use App\Modules\Clinical\Integration\Repositories\SatusehatSettingsRepository;
use Tests\TestCase;

class SatusehatSettingsRepositoryTest extends TestCase
{

    public function test_get(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsRepository::class, 'get');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }
}