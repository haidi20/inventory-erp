<?php

use App\Modules\Operational\Manufacturing\Http\Controllers\BomController;
use App\Modules\Operational\Manufacturing\Http\Controllers\AssemblyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:operational.manage'])->group(function () {
    Route::apiResource('operational/boms', BomController::class, [
        'parameters' => ['bom' => 'uuid'],
    ])->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('operational/assemblies', AssemblyController::class, [
        'parameters' => ['assembly' => 'uuid'],
    ])->only(['index', 'store', 'show', 'destroy']);
});
