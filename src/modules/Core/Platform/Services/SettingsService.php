<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Core\Platform\Entities\Icd10Entity;
use App\Modules\Core\Platform\Repositories\SettingsRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingsService
{
    public function __construct(
        private SettingsRepository $settingsRepository,
        private ResponseFactory $responseFactory,
    )
    {
    }

    public function index(): array
    {
        return [
            'profile' => $this->settingsRepository->getClinicProfileData(),
        ];
    }

    public function database(): array
    {
        return [
            'database' => $this->settingsRepository->getDatabaseManagementData(),
            'recentBackups' => $this->formatRecentBackups(
                $this->settingsRepository->getRecentBackupFiles()
            ),
        ];
    }

    public function exportDatabase(DatabaseBackupEntity $databaseBackupEntity): BinaryFileResponse
    {
        $backupResult = $this->prepareDatabaseExport($databaseBackupEntity);

        return $this->responseFactory->download(
            $backupResult['path'],
            $backupResult['filename'],
            ['Content-Type' => 'application/sql']
        );
    }

    public function prepareDatabaseExport(DatabaseBackupEntity $databaseBackupEntity): array
    {
        $payload = Validator::make([
            'notes' => $databaseBackupEntity->notes,
            'download_after_export' => $databaseBackupEntity->download_after_export,
        ], [
            'notes' => ['nullable', 'string', 'max:255'],
            'download_after_export' => ['nullable'],
        ])->validate();

        return $this->settingsRepository->exportDatabase(
            DatabaseBackupEntity::fromArray($payload)
        );
    }

    public function downloadDatabaseBackup(DatabaseBackupDownloadEntity $databaseBackupDownloadEntity): BinaryFileResponse
    {
        $payload = Validator::make([
            'filename' => $databaseBackupDownloadEntity->filename,
            'history_type' => $databaseBackupDownloadEntity->history_type,
        ], [
            'filename' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+\.sql$/'],
            'history_type' => ['required', 'in:manual,system'],
        ])->validate();

        $backupFile = $this->settingsRepository->resolveBackupDownload(
            DatabaseBackupDownloadEntity::fromArray($payload)
        );

        return $this->responseFactory->download(
            $backupFile['path'],
            $backupFile['filename'],
            ['Content-Type' => 'application/sql']
        );
    }

    public function backupHistoryDatatable(DatabaseBackupHistoryTableEntity $databaseBackupHistoryTableEntity): array
    {
        $payload = Validator::make([
            'draw' => $databaseBackupHistoryTableEntity->draw,
            'start' => $databaseBackupHistoryTableEntity->start,
            'length' => $databaseBackupHistoryTableEntity->length,
            'search' => $databaseBackupHistoryTableEntity->search,
            'history_type' => $databaseBackupHistoryTableEntity->history_type,
            'order_column' => $databaseBackupHistoryTableEntity->order_column,
            'order_direction' => $databaseBackupHistoryTableEntity->order_direction,
        ], [
            'draw' => ['nullable', 'integer', 'min:0'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'history_type' => ['required', 'in:manual,system'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:5'],
            'order_direction' => ['nullable', 'in:asc,desc'],
        ])->validate();

        $datatable = $this->settingsRepository->getBackupHistoryDatatable(
            DatabaseBackupHistoryTableEntity::fromArray($payload)
        );

        return [
            'draw' => (int) ($payload['draw'] ?? 1),
            'recordsTotal' => $datatable['recordsTotal'],
            'recordsFiltered' => $datatable['recordsFiltered'],
            'data' => $this->formatBackupHistoryRows($datatable['data']),
        ];
    }

    public function restoreDatabase(DatabaseRestoreEntity $databaseRestoreEntity): void
    {
        $data = Validator::make([
            'backup_file' => $databaseRestoreEntity->backup_file,
            'create_pre_restore_backup' => $databaseRestoreEntity->create_pre_restore_backup,
        ], [
            'backup_file' => ['required', 'file', 'extensions:sql', 'max:51200'],
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

        $this->settingsRepository->restoreDatabase(
            DatabaseRestoreEntity::fromArray([
                'stored_file_path' => $storedPath->getRealPath(),
                'create_pre_restore_backup' => filter_var(
                    $databaseRestoreEntity->create_pre_restore_backup ?? true,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ) ?? true,
            ])
        );
    }

    public function satusehat(): array
    {
        return [
            'cfg' => $this->settingsRepository->getSatusehatSettings(),
        ];
    }

    public function storeSatusehat(SatusehatSettingsEntity $entity): void
    {
        $this->settingsRepository->saveSatusehatSettings($entity);
    }

    public function icd10(Icd10Entity $entity): array
    {
        $search = trim((string) ($entity->search ?? ''));

        return [
            'icd10s' => $this->settingsRepository->getIcd10PageData($search),
            'search' => $search,
        ];
    }

    public function storeIcd10(Icd10Entity $entity): void
    {
        $this->settingsRepository->storeIcd10($entity);
    }

    public function destroyIcd10(Icd10Entity $entity): void
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('UUID ICD-10 wajib diisi.');
        }
        $this->settingsRepository->destroyIcd10($entity->uuid);
    }

    private function formatRecentBackups(array $recentBackups): array
    {
        return array_map(function (array $backup): array {
            return $this->formatBackupHistoryRow($backup);
        }, $recentBackups);
    }

    private function formatBackupHistoryRows(array $rows): array
    {
        return array_map(fn (array $row): array => $this->formatBackupHistoryRow($row), $rows);
    }

    private function formatBackupHistoryRow(array $backup): array
    {
        $updatedAtLabel = '-';
        if (($backup['updated_at'] ?? '') !== '') {
            $updatedAtLabel = Carbon::parse($backup['updated_at'])
                ->locale('id')
                ->translatedFormat('j F Y, H:i');
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
            'download_url' => route('settings.database.download', [
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

