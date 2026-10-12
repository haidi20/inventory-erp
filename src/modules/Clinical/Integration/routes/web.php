<?php

use App\Modules\Clinical\Integration\Http\Controllers\BpjsController;
use App\Modules\Clinical\Integration\Http\Controllers\BpjsSettingsController;
use App\Modules\Clinical\Integration\Http\Controllers\WebSatusehatController;
use App\Modules\Clinical\Integration\Http\Controllers\SatusehatSettingsController;
use App\Modules\Clinical\Integration\Http\Controllers\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('bpjs')->name('bpjs.')->group(function () {
        Route::get('/', [BpjsController::class, 'index'])->name('index');
        Route::get('/settings', [BpjsSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [BpjsSettingsController::class, 'store'])->name('settings.store');
    });

    Route::prefix('satusehat')->name('satusehat.')->group(function () {
        Route::get('/', [WebSatusehatController::class, 'index'])->name('index');
        Route::get('/sync', [WebSatusehatController::class, 'sync'])->name('sync');
        Route::get('/encounter', [WebSatusehatController::class, 'encounter'])->name('encounter');
        Route::get('/fhir', [WebSatusehatController::class, 'fhir'])->name('fhir');
        Route::get('/config', [WebSatusehatController::class, 'config'])->name('config');
        Route::get('/settings', [SatusehatSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SatusehatSettingsController::class, 'store'])->name('settings.store');
    });

    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [IntegrationController::class, 'showBpjsOverview'])->name('index');
        Route::get('/bpjs/registration', [IntegrationController::class, 'showBpjsParticipantRegistration'])->name('bpjs.registration');
        Route::get('/bpjs/verification', [IntegrationController::class, 'showBpjsMembershipVerification'])->name('bpjs.verification');
        Route::get('/bpjs/referral', [IntegrationController::class, 'showBpjsOnlineReferral'])->name('bpjs.referral');
        Route::get('/bpjs/sep', [IntegrationController::class, 'showBpjsSep'])->name('bpjs.sep');
        Route::get('/bpjs/claims', [IntegrationController::class, 'showBpjsClaimMonitoring'])->name('bpjs.claims');
        Route::get('/satusehat/overview', [IntegrationController::class, 'showSatusehatOverview'])->name('satusehat.overview');
        Route::get('/satusehat/sync', [IntegrationController::class, 'showSatusehatSync'])->name('satusehat.sync');
        Route::get('/satusehat/encounter', [IntegrationController::class, 'showSatusehatEncounter'])->name('satusehat.encounter');
        Route::get('/satusehat/fhir', [IntegrationController::class, 'showSatusehatFhir'])->name('satusehat.fhir');
        Route::get('/satusehat/config', [IntegrationController::class, 'showSatusehatConfig'])->name('satusehat.config');
    });
});
