<?php

use App\Modules\Core\Identity\Http\Controllers\AuthController;
use App\Modules\Core\Identity\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::prefix('settings')->group(function () {
        Route::middleware('permission:settings.user.view')->group(function () {
            Route::get('users/export', [UserController::class, 'export']);
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{user}', [UserController::class, 'show']);
        });
        Route::middleware('permission:settings.user.manage')->group(function () {
            Route::post('users/import', [UserController::class, 'import']);
            Route::get('users/import/template', [UserController::class, 'downloadTemplate']);
            Route::post('users', [UserController::class, 'store']);
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);
        });
    });
});
