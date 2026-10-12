<?php

namespace Tests\Feature\Repositories\Clinical\Integration;

use App\Modules\Clinical\Integration\Repositories\BpjsMenuRepository;
use Tests\TestCase;

class BpjsMenuRepositoryTest extends TestCase
{

    public function test_get_overview(): void
    {
        $method = new \ReflectionMethod(BpjsMenuRepository::class, 'getOverview');
        $this->assertTrue($method->isPublic());
    }
}