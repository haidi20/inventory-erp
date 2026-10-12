<?php

use App\Modules\Core\Platform\Http\Controllers\WebBackupController;
use App\Modules\Core\Platform\Http\Controllers\SettingsController;
use App\Modules\Core\Platform\Http\Controllers\HimbauanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/announcements', [HimbauanController::class, 'index'])->name('announcements.index');
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/backup', [WebBackupController::class, 'index'])->name('settings.backup');
    Route::get('/settings/backup/history-datatable', [WebBackupController::class, 'historyDatatable'])->name('settings.backup.history.datatable');
    Route::post('/settings/backup/export', [WebBackupController::class, 'export'])->name('settings.backup.export');
    Route::get('/settings/backup/download/{filename}', [WebBackupController::class, 'download'])->name('settings.backup.download');
    Route::post('/settings/backup/restore', [WebBackupController::class, 'restore'])->name('settings.backup.restore');
    Route::redirect('/settings/database', '/settings/backup');
    Route::redirect('/settings/bpjs', '/bpjs/settings');
    Route::redirect('/settings/satusehat', '/satusehat/settings');
    Route::get('/settings/icd10', [SettingsController::class, 'icd10'])->name('settings.icd10');
    Route::post('/settings/icd10', [SettingsController::class, 'storeIcd10'])->name('settings.icd10.store');
    Route::delete('/settings/icd10/{uuid}', [SettingsController::class, 'destroyIcd10'])->name('settings.icd10.destroy');
    Route::redirect('/management/users', '/settings/users');
    Route::redirect('/management/roles', '/settings/roles');
});
