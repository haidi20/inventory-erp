<?php

use App\Modules\Core\Identity\Http\Controllers\WebAuthController;
use App\Modules\Core\Identity\Http\Controllers\PasswordResetController;
use App\Modules\Core\Identity\Http\Controllers\WebUserController;
use Illuminate\Support\Facades\Route;

Route::get('/kanban', \App\Livewire\PublicKanbanBoard::class)->name('kanban');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::post('/login/pre-check', [WebAuthController::class, 'preCheck']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/change-password', [WebAuthController::class, 'changePassword'])->middleware('auth')->name('change-password');
Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/users', [WebUserController::class, 'index'])->name('users.index');
    Route::post('/users', [WebUserController::class, 'store'])->name('users.store');
    Route::put('/users/{uuid}', [WebUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{uuid}', [WebUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/export', [WebUserController::class, 'export'])->name('users.export');
    Route::post('/users/import', [WebUserController::class, 'import'])->name('users.import');
    Route::get('/users/import/template', [WebUserController::class, 'downloadImportTemplate'])->name('users.import.template');
});
