<?php

namespace App\Modules\Core\Platform\Http\Controllers;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Platform\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService)
    {
    }

    public function index()
    {
        return response()->json($this->backupService->index());
    }

    public function export(Request $request)
    {
        return $this->backupService->export(
            DatabaseBackupEntity::fromArray($request->all())
        );
    }

    public function download(string $filename, Request $request)
    {
        return $this->backupService->download(
            DatabaseBackupDownloadEntity::fromArray([
                'filename' => $filename,
                'history_type' => $request->query('history_type', 'manual'),
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
        $this->backupService->restore(
            DatabaseRestoreEntity::fromArray([
                ...$request->all(),
                'backup_file' => $request->file('backup_file'),
            ])
        );

        return response()->json([
            'message' => 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.',
        ]);
    }
}
