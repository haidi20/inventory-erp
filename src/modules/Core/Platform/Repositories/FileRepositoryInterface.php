<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Models\File;
use App\Modules\Core\Platform\Models\FileAudit;
use App\Modules\Core\Platform\Models\FileVariant;

/**
 * Interface FileRepositoryInterface
 *
 * Kontrak untuk pengelolaan file generik, variant, dan audit metadata.
 */
interface FileRepositoryInterface
{
    public function createFile(array $data): File;
    public function createVariants(array $variants): bool;
    public function createAudit(array $auditData): FileAudit;
    public function findByUUID(string $uuid): ?File;
    public function findAnyByUUID(string $uuid): ?File;
    public function findByHash(string $hash, string $branchUuid): ?File;
    public function findByParent(array $filter): array;
    public function getVariants(string $fileUuid): array;
    public function getAudits(string $fileUuid): array;
    public function updateFile(string $uuid, array $data): bool;
    public function countByContext(array $filter): int;
    public function getLatestVersion(array $filter): int;
    public function markLatestVersion(string $uuid): bool;
    public function setPrimaryFile(string $uuid): bool;
    public function softDeleteFile(string $uuid, string $actorUuid): bool;
    public function restoreFile(string $uuid, string $actorUuid): bool;
    public function hardDeleteFile(string $uuid): bool;
    public function buildBackupBundle(array $input): array;
    public function applyRestoreBundle(array $bundle, string $mode): array;
    public function getStorageUsage(string $branchUuid, bool $includeDeleted): array;
    public function parentExists(string $parentModel, string $parentUuid): bool;
}
