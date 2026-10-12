<?php

use App\Modules\Core\Platform\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:settings.view'])->prefix('settings')->group(function () {
    Route::get('backups', [BackupController::class, 'index']);
    Route::get('backups/history-datatable', [BackupController::class, 'historyDatatable']);
    Route::get('backups/download/{filename}', [BackupController::class, 'download']);
    Route::post('backups/export', [BackupController::class, 'export']);
    Route::post('backups/restore', [BackupController::class, 'restore']);
});
