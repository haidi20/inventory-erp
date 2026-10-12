<?php

namespace Tests\Feature\Repositories\Clinical\Integration;

use App\Modules\Clinical\Integration\Repositories\SatusehatMenuRepository;
use Tests\TestCase;

class SatusehatMenuRepositoryTest extends TestCase
{

    public function test_get_overview(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuRepository::class, 'getOverview');
        $this->assertTrue($method->isPublic());
    }
}