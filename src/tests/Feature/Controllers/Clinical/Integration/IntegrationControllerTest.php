<?php

namespace Tests\Feature\Controllers\Clinical\Integration;

use App\Modules\Clinical\Integration\Http\Controllers\IntegrationController;
use Tests\TestCase;

class IntegrationControllerTest extends TestCase
{

    public function test_show_bpjs_overview(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsOverview');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_bpjs_participant_registration(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsParticipantRegistration');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_bpjs_membership_verification(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsMembershipVerification');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_bpjs_online_referral(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsOnlineReferral');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_bpjs_sep(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsSep');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_bpjs_claim_monitoring(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showBpjsClaimMonitoring');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_satusehat_overview(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showSatusehatOverview');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_satusehat_sync(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showSatusehatSync');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_satusehat_encounter(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showSatusehatEncounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_satusehat_fhir(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showSatusehatFhir');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_satusehat_config(): void
    {
        $method = new \ReflectionMethod(IntegrationController::class, 'showSatusehatConfig');
        $this->assertTrue($method->isPublic());
    }
}