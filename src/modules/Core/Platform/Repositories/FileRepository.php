<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Models\File;
use App\Modules\Core\Platform\Models\FileVariant;
use App\Modules\Core\Platform\Models\FileAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Class FileRepository
 *
 * Implementasi repository file dengan dukungan metadata, variant, dan audit.
 */
class FileRepository implements FileRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function createFile(array $data): File
    {
        return File::create($data);
    }

    /**
     * @inheritDoc
     */
    public function createVariants(array $variants): bool
    {
        if (empty($variants)) {
            return true;
        }

        foreach ($variants as $v) {
            FileVariant::create($v);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function createAudit(array $auditData): FileAudit
    {
        return FileAudit::create($auditData);
    }

    /**
     * @inheritDoc
     */
    public function findByUUID(string $uuid): ?File
    {
        return File::where('uuid', $uuid)->first();
    }

    /**
     * @inheritDoc
     */
    public function findAnyByUUID(string $uuid): ?File
    {
        return File::withTrashed()->where('uuid', $uuid)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByHash(string $hash, string $branchUuid): ?File
    {
        return File::where('file_hash', $hash)
            ->where('branch_uuid', Str::trim($branchUuid))
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function findByParent(array $filter): array
    {
        $query = File::query();

        if ($filter['include_deleted'] ?? false) {
            $query->withTrashed();
        }

        $query->where('parent_model', $filter['parent_model'])
            ->where('parent_uuid', $filter['parent_uuid']);

        if (!empty($filter['context_type'])) {
            $query->where('context_type', $filter['context_type']);
        }

        if (!empty($filter['feature'])) {
            $query->where('feature', $filter['feature']);
        }

        if ($filter['only_primary'] ?? false) {
            $query->where('is_primary', true);
        }

        if ($filter['only_latest'] ?? false) {
            $query->where('is_latest', true);
        }

        return $query->orderBy('sort_order', 'asc')
            ->orderBy('version', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getVariants(string $fileUuid): array
    {
        return FileVariant::where('file_uuid', $fileUuid)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getAudits(string $fileUuid): array
    {
        return FileAudit::where('file_uuid', $fileUuid)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function updateFile(string $uuid, array $data): bool
    {
        $file = $this->findByUUID($uuid);
        if (!$file) {
            return false;
        }

        return $file->update($data);
    }

    /**
     * @inheritDoc
     */
    public function countByContext(array $filter): int
    {
        return File::where('parent_model', $filter['parent_model'])
            ->where('parent_uuid', $filter['parent_uuid'])
            ->where('feature', $filter['feature'])
            ->where('context_type', $filter['context_type'])
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function getLatestVersion(array $filter): int
    {
        return (int) File::where('parent_model', $filter['parent_model'])
            ->where('parent_uuid', $filter['parent_uuid'])
            ->where('feature', $filter['feature'])
            ->where('context_type', $filter['context_type'])
            ->max('version') ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function markLatestVersion(string $uuid): bool
    {
        $current = $this->findByUUID($uuid);
        if (!$current) {
            return false;
        }

        DB::beginTransaction();
        try {
            File::where('parent_model', $current->parent_model)
                ->where('parent_uuid', $current->parent_uuid)
                ->where('feature', $current->feature)
                ->where('context_type', $current->context_type)
                ->where('uuid', '<>', $current->uuid)
                ->update(['is_latest' => false]);

            DB::commit();
            return $current->update(['is_latest' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function setPrimaryFile(string $uuid): bool
    {
        $current = $this->findByUUID($uuid);
        if (!$current) {
            return false;
        }

        DB::beginTransaction();
        try {
            File::where('parent_model', $current->parent_model)
                ->where('parent_uuid', $current->parent_uuid)
                ->where('feature', $current->feature)
                ->where('context_type', $current->context_type)
                ->where('uuid', '<>', $current->uuid)
                ->update(['is_primary' => false]);

            DB::commit();
            return $current->update(['is_primary' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function softDeleteFile(string $uuid, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            FileVariant::where('file_uuid', $uuid)->delete();
            $file = File::where('uuid', $uuid)->first();
            if ($file) {
                // Laravel's SoftDeletes doesn't automatically set deleted_by
                // but Blameable trait might, or we set it manually if needed.
                DB::commit();
                return $file->delete();
            }
            DB::commit();
            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function restoreFile(string $uuid, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            FileVariant::withTrashed()->where('file_uuid', $uuid)->restore();
            $file = File::withTrashed()->where('uuid', $uuid)->first();
            if ($file) {
                DB::commit();
                return $file->restore();
            }
            DB::commit();
            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function hardDeleteFile(string $uuid): bool
    {
        DB::beginTransaction();
        try {
            FileVariant::withTrashed()->where('file_uuid', $uuid)->forceDelete();
            FileAudit::where('file_uuid', $uuid)->update(['file_uuid' => null]);
            $file = File::withTrashed()->where('uuid', $uuid)->first();
            if ($file) {
                DB::commit();
                return $file->forceDelete();
            }
            DB::commit();
            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function buildBackupBundle(array $input): array
    {
        $query = File::withTrashed()
            ->where('branch_uuid', Str::trim($input['branch_uuid']));

        if (!($input['include_deleted'] ?? false)) {
            $query->whereNull('deleted_at');
        }

        if (!empty($input['start_date'])) {
            $query->where('created_at', '>=', $input['start_date']);
        }

        if (!empty($input['end_date'])) {
            $query->where('created_at', '<=', $input['end_date']);
        }

        $files = $query->orderBy('created_at', 'asc')->get();
        $fileUuids = $files->pluck('uuid')->toArray();

        $variants = [];
        $audits = [];

        if (!empty($fileUuids)) {
            $variants = FileVariant::withTrashed()->whereIn('file_uuid', $fileUuids)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();

            $audits = FileAudit::whereIn('file_uuid', $fileUuids)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
        }

        return [
            'files' => $files->toArray(),
            'variants' => $variants,
            'audits' => $audits
        ];
    }

    /**
     * @inheritDoc
     */
    public function applyRestoreBundle(array $bundle, string $mode): array
    {
        $result = [
            'restored_files' => 0,
            'updated_files' => 0,
            'skipped_files' => 0
        ];

        DB::beginTransaction();
        try {
            $fileUuids = array_column($bundle['files'], 'uuid');

            foreach ($bundle['files'] as $fileData) {
                $existing = File::withTrashed()->where('uuid', $fileData['uuid'])->first();

                if (!$existing) {
                    File::create($fileData);
                    $result['restored_files']++;
                    continue;
                }

                if (Str::lower($mode) === 'merge') {
                    $result['skipped_files']++;
                    continue;
                }

                // Replace mode
                $existing->update($fileData);
                $result['updated_files']++;
            }

            if (Str::lower($mode) === 'replace' && !empty($fileUuids)) {
                FileVariant::withTrashed()->whereIn('file_uuid', $fileUuids)->forceDelete();
            }

            if (!empty($bundle['variants'])) {
                foreach ($bundle['variants'] as $variantData) {
                    if (in_array($variantData['file_uuid'], $fileUuids)) {
                        FileVariant::create($variantData);
                    }
                }
            }

            if (!empty($bundle['audits'])) {
                foreach ($bundle['audits'] as $auditData) {
                    if (!FileAudit::where('uuid', $auditData['uuid'])->exists()) {
                        if ($auditData['file_uuid'] && in_array($auditData['file_uuid'], $fileUuids)) {
                            FileAudit::create($auditData);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        };

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getStorageUsage(string $branchUuid, bool $includeDeleted): array
    {
        $query = File::where('branch_uuid', Str::trim($branchUuid));
        if ($includeDeleted) {
            $query->withTrashed();
        }

        $totalFiles = $query->count();
        $totalSize = $query->sum('file_size');

        $byCategory = $query->groupBy('file_category')
            ->select('file_category', DB::raw('SUM(file_size) as total'))
            ->get()
            ->pluck('total', 'file_category')
            ->toArray();

        $byFeature = $query->groupBy('feature')
            ->select('feature', DB::raw('SUM(file_size) as total'))
            ->get()
            ->pluck('total', 'feature')
            ->toArray();

        return [
            'total_files' => $totalFiles,
            'total_size_bytes' => (int) $totalSize,
            'size_by_category' => $byCategory,
            'size_by_feature' => $byFeature
        ];
    }

    /**
     * @inheritDoc
     */
    public function parentExists(string $parentModel, string $parentUuid): bool
    {
        $allowedTables = [
            'patients' => 'patients',
            'doctors' => 'doctors',
            'examinations' => 'examinations',
            'employees' => 'employees',
            'users' => 'users',
        ];

        $tableName = $allowedTables[Str::lower(Str::trim($parentModel))] ?? null;
        if (!$tableName) {
            return false;
        }

        return DB::table($tableName)
            ->where('uuid', $parentUuid)
            ->where(function ($query) {
                $query->whereNull('deleted_at')
                    ->orWhere('deleted_at', 0)
                    ->orWhere('deleted_at', '');
            })
            ->exists();
    }
}
