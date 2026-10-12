<?php

namespace Tests\Feature\Repositories\Core\Platform;

use App\Modules\Core\Platform\Repositories\NoticeRepository;
use Tests\TestCase;

class NoticeRepositoryTest extends TestCase
{

    public function test_all(): void
    {
        $method = new \ReflectionMethod(NoticeRepository::class, 'all');
        $this->assertTrue($method->isPublic());
    }
}