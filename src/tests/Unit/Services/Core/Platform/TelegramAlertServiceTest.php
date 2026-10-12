<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\TelegramAlertService;
use Tests\TestCase;

class TelegramAlertServiceTest extends TestCase
{

    public function test_send_failure_alert(): void
    {
        $method = new \ReflectionMethod(TelegramAlertService::class, 'sendFailureAlert');
        $this->assertTrue($method->isPublic());
    }
}