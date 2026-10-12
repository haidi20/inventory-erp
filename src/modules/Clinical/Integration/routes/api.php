<?php

use App\Modules\Clinical\Integration\Http\Controllers\SatusehatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('integration/satusehat')->group(function () {
    Route::get('practitioner/{nik}', [SatusehatController::class, 'checkPractitioner']);
    Route::get('patient/{nik}', [SatusehatController::class, 'checkPatient']);
    Route::get('organization/{id}', [SatusehatController::class, 'checkOrganization']);
    Route::post('kyc', [SatusehatController::class, 'generateKYC']);
    Route::post('coverage-eligibility', [SatusehatController::class, 'checkCoverageEligibility']);
    Route::post('encounter', [SatusehatController::class, 'sendEncounter']);
    Route::post('condition', [SatusehatController::class, 'sendCondition']);
    Route::post('observation', [SatusehatController::class, 'sendObservation']);
    Route::post('medication-request', [SatusehatController::class, 'sendMedicationRequest']);
});
