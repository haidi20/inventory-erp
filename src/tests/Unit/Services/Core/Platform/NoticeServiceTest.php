<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\NoticeService;
use Tests\TestCase;

class NoticeServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(NoticeService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}