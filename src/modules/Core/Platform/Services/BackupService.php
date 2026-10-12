<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Platform\Repositories\BackupRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupService
{
    public function __construct(
        private BackupRepositoryInterface $backupRepository,
        private ResponseFactory $responseFactory,
    ) {
    }

    public function index(): array
    {
        return [
            'database' => $this->backupRepository->getBackupManagementData(),
            'recentBackups' => $this->formatRecentBackups(
                $this->backupRepository->getRecentBackups()
            ),
        ];
    }

    public function export(DatabaseBackupEntity $backupEntity): BinaryFileResponse
    {
        $backupResult = $this->prepareExport($backupEntity);

        return $this->responseFactory->download(
            $backupResult['path'],
            $backupResult['filename'],
            ['Content-Type' => 'application/sql']
        );
    }

    public function prepareExport(DatabaseBackupEntity $backupEntity): array
    {
        $payload = Validator::make([
            'notes' => $backupEntity->notes,
            'download_after_export' => $backupEntity->download_after_export,
        ], [
            'notes' => ['nullable', 'string', 'max:255'],
            'download_after_export' => ['nullable'],
        ])->validate();

        return $this->backupRepository->createBackupExport(
            DatabaseBackupEntity::fromArray($payload)
        );
    }

    public function download(DatabaseBackupDownloadEntity $backupDownloadEntity): BinaryFileResponse
    {
        $payload = Validator::make([
            'filename' => $backupDownloadEntity->filename,
            'history_type' => $backupDownloadEntity->history_type,
        ], [
            'filename' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+\.sql$/'],
            'history_type' => ['required', 'in:manual,system'],
        ])->validate();

        $backupFile = $this->backupRepository->resolveBackupDownload(
            DatabaseBackupDownloadEntity::fromArray($payload)
        );

        return $this->responseFactory->download(
            $backupFile['path'],
            $backupFile['filename'],
            ['Content-Type' => 'application/sql']
        );
    }

    public function historyDatatable(DatabaseBackupHistoryTableEntity $historyEntity): array
    {
        $payload = Validator::make([
            'draw' => $historyEntity->draw,
            'start' => $historyEntity->start,
            'length' => $historyEntity->length,
            'search' => $historyEntity->search,
            'history_type' => $historyEntity->history_type,
            'order_column' => $historyEntity->order_column,
            'order_direction' => $historyEntity->order_direction,
        ], [
            'draw' => ['nullable', 'integer', 'min:0'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'history_type' => ['required', 'in:manual,system'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:5'],
            'order_direction' => ['nullable', 'in:asc,desc'],
        ])->validate();

        $datatable = $this->backupRepository->getBackupHistoryDatatable(
            DatabaseBackupHistoryTableEntity::fromArray($payload)
        );

        return [
            'draw' => (int) ($payload['draw'] ?? 1),
            'recordsTotal' => $datatable['recordsTotal'],
            'recordsFiltered' => $datatable['recordsFiltered'],
            'data' => $this->formatBackupHistoryRows($datatable['data']),
        ];
    }

    public function restore(DatabaseRestoreEntity $restoreEntity): void
    {
        $data = Validator::make([
            'backup_file' => $restoreEntity->backup_file,
            'create_pre_restore_backup' => $restoreEntity->create_pre_restore_backup,
        ], [
            'backup_file' => ['required', 'file', 'extensions:sql,zip', 'max:204800'],
            'create_pre_restore_backup' => ['nullable'],
        ])->validate();

        $temporaryDirectory = storage_path('app/backups/tmp');
        if (!is_dir($temporaryDirectory)) {
            mkdir($temporaryDirectory, 0777, true);
        }

        $storedPath = $data['backup_file']->move(
            $temporaryDirectory,
            now()->format('Ymd_His') . '_restore_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $data['backup_file']->getClientOriginalName())
        );

        try {
            $this->backupRepository->restoreBackup(
                DatabaseRestoreEntity::fromArray([
                    'stored_file_path' => $storedPath->getRealPath(),
                    'create_pre_restore_backup' => filter_var(
                        $restoreEntity->create_pre_restore_backup ?? true,
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    ) ?? true,
                ])
            );
        } finally {
            $temporaryFile = $storedPath->getRealPath();
            if ($temporaryFile && file_exists($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }
    }

    private function formatRecentBackups(array $recentBackups): array
    {
        return array_map(fn (array $backup): array => $this->formatBackupHistoryRow($backup), $recentBackups);
    }

    private function formatBackupHistoryRows(array $rows): array
    {
        return array_map(fn (array $row): array => $this->formatBackupHistoryRow($row), $rows);
    }

    private function formatBackupHistoryRow(array $backup): array
    {
        $updatedAtLabel = '-';
        if (($backup['updated_at'] ?? '') !== '') {
            $raw = $backup['updated_at'];
            // Unix integer timestamps need explicit timezone conversion to the app's
            // configured timezone so users see local Indonesian time.
            $carbon = is_numeric($raw)
                ? Carbon::createFromTimestamp((int) $raw, config('app.timezone'))
                : Carbon::parse($raw, config('app.timezone'));
            $updatedAtLabel = $carbon->locale('id')->translatedFormat('j F Y, H:i');
        }

        $sizeLabel = number_format(((float) ($backup['size'] ?? 0)) / 1024 / 1024, 1, ',', '.') . ' MB';
        $resolvedSource = $this->resolveBackupSourceLabelKey($backup);
        $sourceLabel = match ($resolvedSource) {
            'system' => 'Dibuat oleh Sistem',
            'user' => 'Dibuat oleh Pengguna',
            default => 'Sumber tidak diketahui',
        };
        $statusLabel = match ($backup['status'] ?? null) {
            'ready' => 'Backup siap digunakan',
            'restore_safeguard' => 'Cadangan sebelum restore',
            'restore_failed_safeguard' => 'Cadangan saat restore gagal',
            default => 'Status tidak diketahui',
        };
        $historyTypeLabel = match ($backup['history_type'] ?? 'manual') {
            'system' => 'Sistem',
            default => 'Manual',
        };

        return $backup + [
            'updated_at_label' => $updatedAtLabel,
            'size_label' => $sizeLabel,
            'source_label' => $sourceLabel,
            'status_label' => $statusLabel,
            'history_type_label' => $historyTypeLabel,
            'download_url' => route('settings.backup.download', [
                'filename' => $backup['name'],
                'history_type' => $backup['history_type'] ?? 'manual',
            ]),
        ];
    }

    private function resolveBackupSourceLabelKey(array $backup): ?string
    {
        $status = $backup['status'] ?? null;
        $source = $backup['source'] ?? null;

        if (in_array($status, ['restore_safeguard', 'restore_failed_safeguard'], true) && $source === 'system') {
            return 'user';
        }

        return $source;
    }
}


