<?php

namespace App\Modules\Core\Platform\Http\Controllers;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Platform\Entities\Icd10Entity;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Platform\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService)
    {
        parent::__construct();
    }

    public function index(): Response
    {
        return Inertia::render('Core/Platform/presentations/views/SettingsIndex');
    }

    public function icd10(Request $request): Response
    {
        return Inertia::render('Core/Platform/presentations/views/Icd10Index', [
            'filters' => [
                'search'   => (string) $request->string('search')->trim(),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function storeIcd10(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->settingsService->storeIcd10(Icd10Entity::fromArray($request->all()));
        return back()->with('status', 'Kode ICD-10 berhasil ditambahkan.');
    }

    public function destroyIcd10(string $uuid): \Illuminate\Http\RedirectResponse
    {
        $this->settingsService->destroyIcd10($uuid);
        return back()->with('status', 'Kode ICD-10 berhasil dihapus.');
    }

    public function exportDatabase(Request $request)
    {
        try {
            $entity = DatabaseBackupEntity::fromArray($request->all());
            if ($request->ajax()) {
                $backup = $this->settingsService->prepareDatabaseExport($entity);
                return response()->json(['message' => 'Backup berhasil dibuat.', 'filename' => $backup['filename'], 'download_url' => route('settings.backup.download', ['filename' => $backup['filename']])]);
            }
            return $this->settingsService->exportDatabase($entity);
        } catch (\Throwable $e) {
            if ($request->ajax()) return response()->json(['message' => $e->getMessage()], 422);
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadDatabaseBackup(string $filename)
    {
        return $this->settingsService->downloadDatabaseBackup(DatabaseBackupDownloadEntity::fromArray(['filename' => $filename, 'history_type' => request()->query('history_type', 'manual')]));
    }

    public function backupHistoryDatatable(Request $request)
    {
        return response()->json($this->settingsService->backupHistoryDatatable(DatabaseBackupHistoryTableEntity::fromArray($request->all())));
    }

    public function restoreDatabase(Request $request)
    {
        try {
            $this->settingsService->restoreDatabase(DatabaseRestoreEntity::fromArray([...$request->all(), 'backup_file' => $request->file('backup_file')]));
            if ($request->ajax()) return response()->json(['message' => 'Restore berhasil.']);
            return back()->with('success', 'Restore berhasil.');
        } catch (\Throwable $e) {
            if ($request->ajax()) return response()->json(['message' => $e->getMessage()], 422);
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeSatusehat(Request $request): RedirectResponse
    {
        $this->settingsService->storeSatusehat(SatusehatSettingsEntity::fromArray($request->all()));
        return redirect()->route('satusehat.settings')->with('success', 'Konfigurasi SATUSEHAT berhasil disimpan.');
    }
}
