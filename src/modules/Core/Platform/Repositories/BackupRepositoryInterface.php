<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;

interface BackupRepositoryInterface
{
    public function getBackupManagementData(): array;

    public function getRecentBackups(): array;

    public function getBackupHistoryDatatable(DatabaseBackupHistoryTableEntity $entity): array;

    public function createBackupExport(DatabaseBackupEntity $entity): array;

    public function resolveBackupDownload(DatabaseBackupDownloadEntity $entity): array;

    public function restoreBackup(DatabaseRestoreEntity $entity): void;
}


