<?php

namespace Tests\Feature\Repositories\Core\Platform;

use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use Tests\TestCase;

class SidebarNavigationMenuRepositoryTest extends TestCase
{

    public function test_get_all(): void
    {
        $method = new \ReflectionMethod(SidebarNavigationMenuRepository::class, 'getAll');
        $this->assertTrue($method->isPublic());
    }
}