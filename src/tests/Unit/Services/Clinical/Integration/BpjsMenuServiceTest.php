<?php

namespace Tests\Unit\Services\Clinical\Integration;

use App\Modules\Clinical\Integration\Services\BpjsMenuService;
use Tests\TestCase;

class BpjsMenuServiceTest extends TestCase
{

    public function test_overview(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'overview');
        $this->assertTrue($method->isPublic());
    }

    public function test_participant_registration(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'participantRegistration');
        $this->assertTrue($method->isPublic());
    }

    public function test_membership_verification(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'membershipVerification');
        $this->assertTrue($method->isPublic());
    }

    public function test_online_referral(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'onlineReferral');
        $this->assertTrue($method->isPublic());
    }

    public function test_sep(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'sep');
        $this->assertTrue($method->isPublic());
    }

    public function test_claim_monitoring(): void
    {
        $method = new \ReflectionMethod(BpjsMenuService::class, 'claimMonitoring');
        $this->assertTrue($method->isPublic());
    }
}