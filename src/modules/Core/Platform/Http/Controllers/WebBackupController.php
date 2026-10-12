<?php

namespace App\Modules\Core\Platform\Http\Controllers;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Platform\Services\BackupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class WebBackupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private BackupService $backupService)
    {
        parent::__construct();
    }

    public function index()
    {
        $this->authorize('settings.view');
        
        return \Inertia\Inertia::render('Core/Platform/presentations/views/backupIndex', $this->backupService->index());
    }

    public function export(Request $request)
    {
        try {
            $entity = DatabaseBackupEntity::fromArray($request->all());

            if ($request->ajax()) {
                $backup = $this->backupService->prepareExport($entity);

                return response()->json([
                    'message' => 'Backup database berhasil dibuat. File SQL akan segera diunduh.',
                    'filename' => $backup['filename'],
                    'download_url' => route('settings.backup.download', ['filename' => $backup['filename']]),
                ]);
            }

            return $this->backupService->export($entity);
        } catch (\Throwable $throwable) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => $throwable->getMessage(),
                ], 422);
            }

            return back()->with('error', $throwable->getMessage());
        }
    }

    public function download(string $filename)
    {
        return $this->backupService->download(
            DatabaseBackupDownloadEntity::fromArray([
                'filename' => $filename,
                'history_type' => request()->query('history_type', 'manual'),
            ])
        );
    }

    public function historyDatatable(Request $request)
    {
        return response()->json(
            $this->backupService->historyDatatable(
                DatabaseBackupHistoryTableEntity::fromArray($request->all())
            )
        );
    }

    public function restore(Request $request)
    {
        try {
            $this->backupService->restore(
                DatabaseRestoreEntity::fromArray([
                    ...$request->all(),
                    'backup_file' => $request->file('backup_file'),
                ])
            );

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.',
                ]);
            }

            return back()->with('success', 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.');
        } catch (\Throwable $throwable) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => $throwable->getMessage(),
                ], 422);
            }

            return back()->with('error', $throwable->getMessage());
        }
    }
}
