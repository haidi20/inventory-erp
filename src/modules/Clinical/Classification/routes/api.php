<?php

use App\Modules\Clinical\Classification\Http\Controllers\Icd10CodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clinical\Classification API Routes
|--------------------------------------------------------------------------
| Endpoint freeze (Section 16): /api/v1/master/icd-codes
*/

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('master/icd-codes', Icd10CodeController::class, [
        'parameters' => ['icd-code' => 'uuid'],
    ]);
});
