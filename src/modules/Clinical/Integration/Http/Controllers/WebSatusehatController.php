<?php
namespace App\Modules\Clinical\Integration\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class WebSatusehatController extends Controller {
    public function __construct() { parent::__construct(); }
    public function index(): Response    { return Inertia::render('Clinical/Integration/presentations/views/SatusehatIndex'); }
    public function sync(): Response     { return Inertia::render('Clinical/Integration/presentations/views/SatusehatSyncIndex'); }
    public function encounter(): Response{ return Inertia::render('Clinical/Integration/presentations/views/SatusehatEncounterIndex'); }
    public function fhir(): Response     { return Inertia::render('Clinical/Integration/presentations/views/SatusehatFhirIndex'); }
    public function config(): Response   { return Inertia::render('Clinical/Integration/presentations/views/SatusehatSettingsIndex'); }
}