<?php

use App\Modules\Core\Authorization\Http\Controllers\WebRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/roles', [WebRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [WebRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [WebRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{uuid}/edit', [WebRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{uuid}', [WebRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{uuid}', [WebRoleController::class, 'destroy'])->name('roles.destroy');
});
