<?php

use App\Modules\Core\Authorization\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('settings')->group(function () {
        Route::middleware('permission:settings.role.view')->group(function () {
            Route::get('roles/export', [RoleController::class, 'export']);
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/{role}', [RoleController::class, 'show']);
        });
        Route::middleware('permission:settings.role.manage')->group(function () {
            Route::post('roles/import', [RoleController::class, 'import']);
            Route::get('roles/import/template', [RoleController::class, 'downloadTemplate']);
            Route::post('roles', [RoleController::class, 'store']);
            Route::put('roles/{role}', [RoleController::class, 'update']);
            Route::delete('roles/{role}', [RoleController::class, 'destroy']);
        });
    });
});
