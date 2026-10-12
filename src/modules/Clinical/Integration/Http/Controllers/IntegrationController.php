<?php
namespace App\Modules\Clinical\Integration\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
class IntegrationController extends Controller {
    public function __construct() { parent::__construct(); }
    public function showBpjsOverview(): Response              { return Inertia::render('Clinical/Integration/presentations/views/BpjsIndex'); }
    public function showBpjsParticipantRegistration(): Response { return Inertia::render('Clinical/Integration/presentations/views/BpjsRegistrationIndex'); }
    public function showBpjsMembershipVerification(): Response  { return Inertia::render('Clinical/Integration/presentations/views/BpjsVerificationIndex'); }
    public function showBpjsOnlineReferral(): Response          { return Inertia::render('Clinical/Integration/presentations/views/BpjsReferralIndex'); }
    public function showBpjsSep(): Response                     { return Inertia::render('Clinical/Integration/presentations/views/BpjsSepIndex'); }
    public function showBpjsClaimMonitoring(): Response         { return Inertia::render('Clinical/Integration/presentations/views/BpjsClaimIndex'); }
    public function showSatusehatOverview(): Response           { return Inertia::render('Clinical/Integration/presentations/views/SatusehatIndex'); }
    public function showSatusehatSync(): Response               { return Inertia::render('Clinical/Integration/presentations/views/SatusehatSyncIndex'); }
    public function showSatusehatEncounter(): Response          { return Inertia::render('Clinical/Integration/presentations/views/SatusehatEncounterIndex'); }
    public function showSatusehatFhir(): Response               { return Inertia::render('Clinical/Integration/presentations/views/SatusehatFhirIndex'); }
    public function showSatusehatConfig(): Response             { return Inertia::render('Clinical/Integration/presentations/views/SatusehatSettingsIndex'); }
}