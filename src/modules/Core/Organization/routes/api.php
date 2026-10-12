<?php

use App\Modules\Core\Organization\Http\Controllers\BranchController;
use App\Modules\Core\Organization\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('master')->group(function () {
    Route::apiResource('branches', BranchController::class)->parameters(['branch' => 'uuid']);
    Route::apiResource('companies', CompanyController::class)->parameters(['company' => 'uuid']);
});
