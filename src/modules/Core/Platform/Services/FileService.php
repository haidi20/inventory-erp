<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Repositories\FileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FileService
 *
 * Port dari BE: file_service.go. Service file generik yang mendukung upload,
 * query, soft/hard delete, restore, backup zip, restore zip, verifikasi integritas,
 * dan statistik penggunaan storage.
 *
 * Catatan: Implementasi PHP menyederhanakan beberapa detail Go yang bergantung
 * pada paket native (image.Decode, http.DetectContentType, zip). Operasi
 * re-encoding image dan scan konten berbahaya dilakukan dengan utilitas PHP
 * (finfo, gd/imagick, ZipArchive) agar tetap parity fungsional.
 */
use App\Modules\Core\Platform\Entities\FileRequestEntity;

class FileService
{
    private const MAX_RESTORE_ARCHIVE_ENTRIES = 512;
    private const MAX_RESTORE_ARCHIVE_BYTES = 536870912; // 512 MB
    private const MAX_RESTORE_SINGLE_ENTRY_BYTES = 33554432; // 32 MB
    private const DEFAULT_MAX_SIZE_BYTES = 10485760; // 10 MB

    public function __construct(
        protected FileRepositoryInterface $fileRepository,
        protected string $storageDisk = 'local',
        protected string $storagePrefix = 'files',
        protected string $backupPrefix = 'backups/files',
    ) {}

    /** Validasi request upload file generik. */
    public function uploadFile(FileRequestEntity $entity): mixed
    {
        $req = $entity->toArray();
        $this->validateUploadRequest($req);

        [$sanitizedContent, $normalizedMime] = $this->sanitizeUploadedContent($req);
        $actualFileSize = strlen($sanitizedContent);

        if (!$this->fileRepository->parentExists($entity->parent_model, $entity->parent_uuid)) {
            throw new \InvalidArgumentException('parent_uuid tidak ditemukan.');
        }

        $hash = hash('sha256', $sanitizedContent);
        $skipDuplicate = (bool) ($entity->options['skip_duplicate_check'] ?? false);
        if (!$skipDuplicate) {
            $existing = $this->fileRepository->findByHash($hash, $entity->branch_uuid);
            if ($existing !== null) {
                $existing->is_duplicate = true;
                $existing->existing_file_uuid = $existing->uuid;
                return $existing;
            }
        }

        $branchQuota = (int) ($entity->options['branch_quota_bytes'] ?? 0);
        if ($branchQuota > 0) {
            $usage = $this->fileRepository->getStorageUsage($entity->branch_uuid, false);
            if (((int) ($usage['total_size_bytes'] ?? 0)) + $actualFileSize > $branchQuota) {
                throw new \InvalidArgumentException('Kuota storage cabang terlampaui.');
            }
        }

        $contextFilter = [
            'parent_model' => $entity->parent_model,
            'parent_uuid'  => $entity->parent_uuid,
            'feature'      => $entity->feature,
            'context_type' => $entity->context_type,
        ];

        $maxFilesPerContext = (int) ($entity->options['max_files_per_context'] ?? 0);
        if ($maxFilesPerContext > 0) {
            $count = $this->fileRepository->countByContext($contextFilter);
            if ($count >= $maxFilesPerContext) {
                throw new \InvalidArgumentException('Jumlah file maksimum untuk context ini sudah tercapai.');
            }
        }

        $version = $this->fileRepository->getLatestVersion($contextFilter) + 1;

        $originalFilename = trim((string) ($entity->original_filename ?? ''));
        $customFileName = trim((string) ($entity->options['custom_file_name'] ?? ''));
        if ($customFileName !== '') {
            $originalFilename = $customFileName;
        }

        $filePath = $this->buildRelativeFilePath(
            $entity->branch_uuid, $entity->parent_model, $entity->parent_uuid, $entity->feature, $originalFilename,
        );
        Storage::disk($this->storageDisk)->put($this->absoluteStoragePath($filePath), $sanitizedContent);

        $fileCategory = $this->categorizeMime($normalizedMime);
        $sortOrder = (int) ($entity->options['sort_order'] ?? 1);

        $file = $this->fileRepository->createFile([
            'uuid'              => (string) Str::uuid(),
            'branch_uuid'       => $entity->branch_uuid,
            'parent_uuid'       => $entity->parent_uuid,
            'parent_model'      => $entity->parent_model,
            'feature'           => $entity->feature,
            'context_type'      => $entity->context_type,
            'context_identifier'=> $entity->context_identifier,
            'sort_order'        => $sortOrder,
            'is_primary'        => ((bool) ($entity->options['is_primary'] ?? false)) || $version === 1,
            'version'           => $version,
            'is_latest'         => true,
            'original_filename' => $originalFilename,
            'file_path'         => $filePath,
            'file_size'         => $actualFileSize,
            'mime_type'         => $normalizedMime,
            'file_hash'         => $hash,
            'file_category'     => $fileCategory,
            'processing_status' => 'completed',
            'disk'              => $this->storageDisk,
            'uploaded_by_uuid'  => $entity->uploaded_by_uuid,
            'uploaded_ip'       => $entity->uploaded_ip,
        ]);

        $this->fileRepository->markLatestVersion($file->uuid);
        if ($file->is_primary) {
            $this->fileRepository->setPrimaryFile($file->uuid);
        }

        [$variants, $thumbnailPath] = $this->buildVariants($file, $sanitizedContent, $fileCategory);
        $this->fileRepository->createVariants($variants);
        if ($thumbnailPath !== '') {
            $file->thumbnail_path = $thumbnailPath;
        }

        $this->fileRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => $file->uuid,
            'actor_uuid' => $entity->uploaded_by_uuid,
            'action'     => 'upload',
            'note'       => 'upload file ' . $file->original_filename,
            'actor_ip'   => $entity->uploaded_ip,
        ]);

        return $file;
    }

    /** Mengambil file by UUID. (R1: return type model dinyatakan via docblock) */
    public function getFile(string $uuid): ?object
    {
        $uuid = trim($uuid);
        if ($uuid === '') {
            throw new \InvalidArgumentException('file_uuid wajib diisi.');
        }
        return $this->fileRepository->findByUUID($uuid);
    }

    public function getFilesByParent(FileRequestEntity $entity): array
    {
        if (empty($entity->parent_model) || empty($entity->parent_uuid)) {
            throw new \InvalidArgumentException('parent_model dan parent_uuid wajib diisi.');
        }
        return $this->fileRepository->findByParent($entity->toArray());
    }

    public function downloadFile(FileRequestEntity $entity): array
    {
        $file = $this->resolveDownloadFile($entity->toArray());
        $targetPath = $file->file_path;
        $selectedVariant = null;

        $variantRequested = strtolower(trim((string) ($entity->variant ?? '')));
        if ($variantRequested !== '' && $variantRequested !== 'original') {
            foreach ($this->fileRepository->getVariants($file->uuid) as $variant) {
                if (strtolower((string) ($variant->variant_type ?? '')) === $variantRequested) {
                    $targetPath = $variant->file_path;
                    $selectedVariant = $variant;
                    break;
                }
            }
        }

        $content = Storage::disk($this->storageDisk)->get($this->absoluteStoragePath($targetPath));

        $this->fileRepository->updateFile($file->uuid, ['accessed_at' => now()]);
        $disposition = trim((string) ($entity->disposition ?? 'attachment'));
        $action = strtolower($disposition) === 'inline' ? 'view' : 'download';

        $this->fileRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => $file->uuid,
            'actor_uuid' => $entity->actor_uuid,
            'action'     => $action,
            'actor_ip'   => $entity->actor_ip,
        ]);

        return [
            'file'         => $file,
            'variant'      => $selectedVariant,
            'content'      => $content,
            'content_type' => $file->mime_type,
            'disposition'  => $disposition,
            'filename'     => $file->original_filename,
        ];
    }

    public function softDeleteFile(FileRequestEntity $entity): void
    {
        if (empty($entity->file_uuid) || empty($entity->actor_uuid)) {
            throw new \InvalidArgumentException('file_uuid dan actor_uuid wajib diisi.');
        }
        if ($this->fileRepository->findByUUID($entity->file_uuid) === null) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $this->fileRepository->softDeleteFile($entity->file_uuid, $entity->actor_uuid);
        $this->fileRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => $entity->file_uuid,
            'actor_uuid' => $entity->actor_uuid,
            'action'     => 'delete',
            'note'       => $entity->reason,
            'actor_ip'   => $entity->actor_ip,
        ]);
    }

    public function restoreFile(FileRequestEntity $entity): void
    {
        if (empty($entity->file_uuid) || empty($entity->actor_uuid)) {
            throw new \InvalidArgumentException('file_uuid dan actor_uuid wajib diisi.');
        }
        if ($this->fileRepository->findAnyByUUID($entity->file_uuid) === null) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $this->fileRepository->restoreFile($entity->file_uuid, $entity->actor_uuid);
        $this->fileRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => $entity->file_uuid,
            'actor_uuid' => $entity->actor_uuid,
            'action'     => 'restore',
        ]);
    }

    public function hardDeleteFile(FileRequestEntity $entity): void
    {
        if (empty($entity->file_uuid) || empty($entity->actor_uuid)) {
            throw new \InvalidArgumentException('file_uuid dan actor_uuid wajib diisi.');
        }
        if (strcasecmp($entity->actor_role ?? '', 'super_admin') !== 0) {
            throw new \RuntimeException('Hard delete hanya boleh oleh super_admin.');
        }

        $file = $this->fileRepository->findAnyByUUID($entity->file_uuid);
        if ($file === null) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $paths = [$file->file_path];
        if ($entity->delete_variants) {
            foreach ($this->fileRepository->getVariants($entity->file_uuid) as $variant) {
                $paths[] = $variant->file_path;
            }
        }
        foreach (array_unique(array_filter($paths)) as $relPath) {
            Storage::disk($this->storageDisk)->delete($this->absoluteStoragePath($relPath));
        }

        $this->fileRepository->hardDeleteFile($entity->file_uuid);
        $this->fileRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => null,
            'actor_uuid' => $entity->actor_uuid,
            'action'     => 'hard_delete',
            'note'       => 'hard delete file ' . $entity->file_uuid,
            'actor_ip'   => $entity->actor_ip,
        ]);
    }

    public function backupFiles(FileRequestEntity $entity): array
    {
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('branch_uuid wajib diisi.');
        }
        if (empty($entity->actor_uuid)) { // mapped to triggered_by_uuid
            throw new \InvalidArgumentException('actor_uuid wajib diisi.');
        }

        $input = $entity->toArray();
        $input['triggered_by_uuid'] = $entity->actor_uuid;
        
        $bundle = $this->fileRepository->buildBackupBundle($input);
        $backupId = sprintf('FILE-BACKUP-%s-%s', now()->format('Ymd-His'), substr((string) Str::uuid(), 0, 8));
        $filename = sprintf('file-backup-%s.zip', now()->format('Ymd_His'));
        $archiveRelativePath = $this->backupPrefix . '/' . $filename;

        $totalSize = 0;
        foreach (($bundle['files'] ?? []) as $f) {
            $totalSize += (int) ($f['file_size'] ?? 0);
        }

        $manifest = [
            'backup_id'         => $backupId,
            'branch_uuid'       => $entity->branch_uuid,
            'backup_type'       => $entity->backup_type,
            'triggered_by_uuid' => $entity->actor_uuid,
            'created_at'        => now()->timestamp,
            'total_files'       => count($bundle['files'] ?? []),
            'total_size_bytes'  => $totalSize,
            'checksum'          => $this->calculateBundleChecksum($bundle),
        ];

        $tmp = tempnam(sys_get_temp_dir(), 'file-backup-');
        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat archive backup.');
        }
        $zip->addFromString('metadata/files.json', json_encode($bundle['files'] ?? [], JSON_PRETTY_PRINT));
        $zip->addFromString('metadata/file_variants.json', json_encode($bundle['variants'] ?? [], JSON_PRETTY_PRINT));
        $zip->addFromString('metadata/file_audits.json', json_encode($bundle['audits'] ?? [], JSON_PRETTY_PRINT));

        foreach ($this->buildBundleStoragePaths($bundle) as $relPath) {
            $content = Storage::disk($this->storageDisk)->get($this->absoluteStoragePath($relPath));
            $zip->addFromString('storage/' . ltrim($relPath, '/'), (string) $content);
        }
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        Storage::disk($this->storageDisk)->put($archiveRelativePath, file_get_contents($tmp));
        @unlink($tmp);

        return [
            'backup_id'        => $backupId,
            'archive_path'     => $archiveRelativePath,
            'filename'         => $filename,
            'total_files'      => $manifest['total_files'],
            'total_size_bytes' => $totalSize,
            'manifest'         => $manifest,
        ];
    }

    public function restoreFiles(FileRequestEntity $entity): array
    {
        if (empty($entity->archive_path)) {
            throw new \InvalidArgumentException('archive_path wajib diisi.');
        }
        if (empty($entity->actor_uuid)) { // mapped to restored_by_uuid
            throw new \InvalidArgumentException('actor_uuid wajib diisi.');
        }

        $startedAt = microtime(true);
        [$manifest, $bundle, $storageEntries] = $this->readBackupArchive($entity->archive_path);

        if ($entity->verify_checksum && !empty($manifest['checksum'])) {
            if ($manifest['checksum'] !== $this->calculateBundleChecksum($bundle)) {
                throw new \RuntimeException('Checksum backup tidak valid.');
            }
        }
        $this->validateRestoreBundle($manifest, $bundle);

        $applied = $this->fileRepository->applyRestoreBundle($bundle, $entity->restore_mode);
        $applied['backup_id'] = $manifest['backup_id'] ?? null;
        $applied['archive_path'] = $entity->archive_path;
        $applied['total_size_bytes'] = $manifest['total_size_bytes'] ?? 0;
        $applied['duration_seconds'] = (int) (microtime(true) - $startedAt);
        
        if ($entity->restore_mode !== 'dry-run') {
            foreach ($this->buildBundleStoragePaths($bundle) as $relPath) {
                $clean = ltrim($relPath, '/');
                if (!isset($storageEntries[$clean])) {
                    throw new \RuntimeException('Entry backup untuk ' . $clean . ' tidak ditemukan.');
                }
                Storage::disk($this->storageDisk)->put($this->absoluteStoragePath($relPath), $storageEntries[$clean]);
            }
            foreach (($bundle['files'] ?? []) as $file) {
                $this->fileRepository->createAudit([
                    'uuid'       => (string) Str::uuid(),
                    'file_uuid'  => $file['uuid'] ?? null,
                    'actor_uuid' => $entity->actor_uuid,
                    'action'     => 'restore_from_backup',
                    'note'       => 'restore backup ' . ($manifest['backup_id'] ?? ''),
                ]);
            }
        } else {
            $applied['dry_run'] = true;
        }

        return $applied;
    }

    public function verifyFileIntegrity(FileRequestEntity $entity): array
    {
        if (empty($entity->file_uuid)) {
            throw new \InvalidArgumentException('file_uuid wajib diisi.');
        }

        $file = $this->fileRepository->findAnyByUUID($entity->file_uuid);
        if ($file === null) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $result = [
            'checked_files'       => 1,
            'checked_size_bytes'  => (int) $file->file_size,
            'missing_files'       => 0,
            'hash_mismatch_files' => 0,
            'repaired_files'      => 0,
        ];

        $absolute = $this->absoluteStoragePath($file->file_path);
        if (!Storage::disk($this->storageDisk)->exists($absolute)) {
            $result['missing_files'] = 1;
            return $result;
        }

        $content = (string) Storage::disk($this->storageDisk)->get($absolute);
        $currentHash = hash('sha256', $content);
        if ($currentHash !== $file->file_hash) {
            $result['hash_mismatch_files'] = 1;
            if ($entity->repair_mode) {
                $this->fileRepository->updateFile($file->uuid, ['file_hash' => $currentHash]);
                $result['repaired_files'] = 1;
            }
        }

        return $result;
    }

    public function getStorageUsage(FileRequestEntity $entity): array
    {
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('branch_uuid wajib diisi.');
        }
        return $this->fileRepository->getStorageUsage($entity->branch_uuid, $entity->include_deleted);
    }

    // ------------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------------

    private function validateUploadRequest(array $req): void
    {
        foreach (['branch_uuid', 'parent_uuid', 'parent_model', 'feature', 'context_type', 'uploaded_by_uuid', 'original_filename'] as $field) {
            if (trim((string) ($req[$field] ?? '')) === '') {
                throw new \InvalidArgumentException($field . ' wajib diisi.');
            }
        }

        $content = (string) ($req['file_content'] ?? '');
        $size = (int) ($req['file_size'] ?? 0);
        if ($content === '' || $size <= 0) {
            throw new \InvalidArgumentException('File kosong.');
        }
        if ($size !== strlen($content)) {
            throw new \InvalidArgumentException('Ukuran file tidak konsisten.');
        }

        $maxSize = (int) ($req['options']['max_size_bytes'] ?? self::DEFAULT_MAX_SIZE_BYTES);
        if ($maxSize <= 0) {
            $maxSize = self::DEFAULT_MAX_SIZE_BYTES;
        }
        if ($size > $maxSize) {
            throw new \InvalidArgumentException('Ukuran file melebihi batas maksimum.');
        }

        $allowed = $req['options']['allowed_extensions'] ?? ['.jpg', '.jpeg', '.png', '.pdf'];
        $ext = '.' . strtolower(pathinfo((string) $req['original_filename'], PATHINFO_EXTENSION));
        if ($this->isDangerousExtension($ext)) {
            throw new \InvalidArgumentException('Extension file berbahaya tidak diizinkan.');
        }
        if (!in_array($ext, array_map('strtolower', $allowed), true)) {
            throw new \InvalidArgumentException('Extension file tidak diizinkan.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = (string) $finfo->buffer($content);
        if (!$this->mimeMatches((string) ($req['mime_type'] ?? ''), $detected)) {
            throw new \InvalidArgumentException('Mime file tidak sesuai dengan isi file.');
        }
        if ($this->isDangerousExecutableContent($content)) {
            throw new \InvalidArgumentException('Konten file terindikasi berbahaya.');
        }

        if (strcasecmp((string) ($req['uploaded_by_role'] ?? ''), 'guest') === 0) {
            throw new \RuntimeException('Role tidak diizinkan untuk upload file.');
        }

        if (stripos((string) ($req['mime_type'] ?? ''), 'pdf') !== false) {
            $this->validatePdfContent($content);
        }
    }

    /** @return array{0: string, 1: string} [sanitizedContent, normalizedMime] */
    private function sanitizeUploadedContent(array $req): array
    {
        $mime = strtolower(trim((string) ($req['mime_type'] ?? '')));
        $content = (string) $req['file_content'];

        if (str_starts_with($mime, 'image/')) {
            // Best-effort re-encode jika ekstensi image dan GD tersedia.
            if (function_exists('imagecreatefromstring') && ($img = @imagecreatefromstring($content))) {
                ob_start();
                $ext = strtolower(pathinfo((string) ($req['original_filename'] ?? ''), PATHINFO_EXTENSION));
                if ($ext === 'png') {
                    imagepng($img, null, 9);
                    $normalized = 'image/png';
                } else {
                    imagejpeg($img, null, 90);
                    $normalized = 'image/jpeg';
                }
                $encoded = (string) ob_get_clean();
                imagedestroy($img);
                return [$encoded, $normalized];
            }
            return [$content, $mime];
        }
        if (str_contains($mime, 'pdf')) {
            $this->validatePdfContent($content);
            return [$content, 'application/pdf'];
        }
        if ($this->isDangerousScriptContent($content)) {
            throw new \InvalidArgumentException('Konten file terindikasi berbahaya.');
        }
        return [$content, $mime];
    }

    private function validatePdfContent(string $content): void
    {
        $trimmed = trim($content);
        $upper = strtoupper($trimmed);
        if (!str_starts_with($trimmed, '%PDF-')) {
            throw new \InvalidArgumentException('PDF tidak valid.');
        }
        if (!str_contains($upper, '%%EOF')) {
            throw new \InvalidArgumentException('PDF tidak valid.');
        }
        if (str_contains($upper, '/ENCRYPT')) {
            throw new \InvalidArgumentException('PDF terenkripsi tidak diizinkan.');
        }
        foreach (['/JAVASCRIPT', '/JS', '/OPENACTION', '/LAUNCH', '/EMBEDDEDFILE', '/RICHMEDIA'] as $marker) {
            if (str_contains($upper, $marker)) {
                throw new \InvalidArgumentException('PDF mengandung konten aktif berbahaya.');
            }
        }
    }

    /** @param object $file \App\Modules\Core\Platform\Models\File
     *  @return array{0: array<int, array>, 1: string} [variants, thumbnailPath] */
    private function buildVariants(object $file, string $content, string $category): array
    {
        $variants = [[
            'uuid'         => (string) Str::uuid(),
            'file_uuid'    => $file->uuid,
            'variant_type' => 'original',
            'file_path'    => $file->file_path,
            'file_size'    => $file->file_size,
        ]];

        $thumbnailPath = '';
        if ($category === 'image') {
            $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);
            $base = preg_replace('/\.' . preg_quote($ext, '/') . '$/', '', $file->file_path);
            $thumbnailPath = $base . '-thumbnail.' . $ext;
            Storage::disk($this->storageDisk)->put($this->absoluteStoragePath($thumbnailPath), $content);

            $variants[] = [
                'uuid'         => (string) Str::uuid(),
                'file_uuid'    => $file->uuid,
                'variant_type' => 'thumbnail',
                'file_path'    => $thumbnailPath,
                'file_size'    => strlen($content),
            ];
        }

        return [$variants, $thumbnailPath];
    }

    private function buildRelativeFilePath(string $branchUuid, string $parentModel, string $parentUuid, string $feature, string $originalFilename): string
    {
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $baseName = sprintf('%s-%s%s', now()->format('Ymd-His'), (string) Str::uuid(), $ext !== '' ? ('.' . $ext) : '');
        $datePath = now()->format('Y/m/d');

        return implode('/', [
            $this->sanitizePathSegment($branchUuid),
            $this->sanitizePathSegment($parentModel),
            $this->sanitizePathSegment($parentUuid),
            $this->sanitizePathSegment($feature),
            $datePath,
            $baseName,
        ]);
    }

    private function absoluteStoragePath(string $relativePath): string
    {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        return $this->storagePrefix . '/' . $clean;
    }

    private function resolveDownloadFile(array $input): mixed
    {
        $uuid = trim((string) ($input['file_uuid'] ?? ''));
        if ($uuid === '') {
            throw new \InvalidArgumentException('file_uuid wajib diisi.');
        }
        $file = ((bool) ($input['allow_deleted'] ?? false))
            ? $this->fileRepository->findAnyByUUID($uuid)
            : $this->fileRepository->findByUUID($uuid);
        if ($file === null) {
            throw new \RuntimeException('File tidak ditemukan.');
        }
        return $file;
    }

    /** @return array{0: array, 1: array, 2: array<string, string>} [manifest, bundle, storageEntries] */
    private function readBackupArchive(string $archiveRelPath): array
    {
        $absolute = Storage::disk($this->storageDisk)->path($archiveRelPath);
        $zip = new \ZipArchive();
        if ($zip->open($absolute) !== true) {
            throw new \RuntimeException('Gagal membuka archive backup.');
        }
        if ($zip->numFiles > self::MAX_RESTORE_ARCHIVE_ENTRIES) {
            $zip->close();
            throw new \InvalidArgumentException('Arsip backup melebihi batas jumlah entry.');
        }

        $manifest = [];
        $bundle = ['files' => [], 'variants' => [], 'audits' => []];
        $storageEntries = [];
        $totalBytes = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false || str_ends_with($stat['name'], '/')) {
                continue;
            }
            if ($stat['size'] > self::MAX_RESTORE_SINGLE_ENTRY_BYTES) {
                $zip->close();
                throw new \InvalidArgumentException('Arsip backup melebihi batas ukuran entry.');
            }
            $totalBytes += (int) $stat['size'];
            if ($totalBytes > self::MAX_RESTORE_ARCHIVE_BYTES) {
                $zip->close();
                throw new \InvalidArgumentException('Arsip backup melebihi batas ukuran restore.');
            }

            $content = $zip->getFromIndex($i);
            $name = str_replace('\\', '/', $stat['name']);
            if (str_starts_with($name, '/') || str_contains($name, '..')) {
                $zip->close();
                throw new \InvalidArgumentException('Entry zip tidak aman.');
            }

            switch ($name) {
                case 'manifest.json':
                    $manifest = json_decode((string) $content, true) ?? [];
                    break;
                case 'metadata/files.json':
                    $bundle['files'] = json_decode((string) $content, true) ?? [];
                    break;
                case 'metadata/file_variants.json':
                    $bundle['variants'] = json_decode((string) $content, true) ?? [];
                    break;
                case 'metadata/file_audits.json':
                    $bundle['audits'] = json_decode((string) $content, true) ?? [];
                    break;
                default:
                    if (str_starts_with($name, 'storage/')) {
                        $rel = substr($name, strlen('storage/'));
                        if (!$this->isSafeStorageRelativePath($rel)) {
                            $zip->close();
                            throw new \InvalidArgumentException('Entry zip tidak aman.');
                        }
                        $storageEntries[$rel] = (string) $content;
                        continue 2;
                    }
                    $zip->close();
                    throw new \InvalidArgumentException('Entry zip tidak dikenali.');
            }
        }
        $zip->close();

        return [$manifest, $bundle, $storageEntries];
    }

    private function validateRestoreBundle(array $manifest, array $bundle): void
    {
        if (trim((string) ($manifest['backup_id'] ?? '')) === '') {
            throw new \InvalidArgumentException('Manifest backup tidak valid.');
        }
        if (trim((string) ($manifest['branch_uuid'] ?? '')) === '') {
            throw new \InvalidArgumentException('Manifest branch_uuid wajib diisi.');
        }
        foreach (($bundle['files'] ?? []) as $file) {
            if (trim((string) ($file['branch_uuid'] ?? '')) === '') {
                throw new \InvalidArgumentException('Metadata file branch_uuid wajib diisi.');
            }
            if ($file['branch_uuid'] !== $manifest['branch_uuid']) {
                throw new \InvalidArgumentException('Branch backup tidak konsisten.');
            }
            if (!$this->isSafeStorageRelativePath((string) ($file['file_path'] ?? ''))) {
                throw new \InvalidArgumentException('Path metadata file tidak aman.');
            }
        }
        foreach (($bundle['variants'] ?? []) as $variant) {
            if (!$this->isSafeStorageRelativePath((string) ($variant['file_path'] ?? ''))) {
                throw new \InvalidArgumentException('Path metadata variant tidak aman.');
            }
        }
    }

    private function buildBundleStoragePaths(array $bundle): array
    {
        $paths = [];
        foreach (($bundle['files'] ?? []) as $f) {
            $paths[] = (string) ($f['file_path'] ?? '');
        }
        foreach (($bundle['variants'] ?? []) as $v) {
            $paths[] = (string) ($v['file_path'] ?? '');
        }
        $paths = array_unique(array_map(
            fn ($p) => ltrim(str_replace('\\', '/', trim((string) $p)), '/'),
            $paths
        ));
        return array_values(array_filter($paths, fn ($p) => $p !== ''));
    }

    private function calculateBundleChecksum(array $bundle): string
    {
        $ctx = hash_init('sha256');
        foreach (($bundle['files'] ?? []) as $f) {
            hash_update($ctx, (string) ($f['uuid'] ?? ''));
            hash_update($ctx, (string) ($f['file_hash'] ?? ''));
        }
        return hash_final($ctx);
    }

    private function categorizeMime(string $mime): string
    {
        $mime = strtolower(trim($mime));
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_contains($mime, 'pdf')) {
            return 'document';
        }
        return 'other';
    }

    private function isDangerousExtension(string $ext): bool
    {
        $dangerous = [
            '.php', '.phtml', '.php3', '.php4', '.php5', '.phar',
            '.exe', '.dll', '.bat', '.cmd', '.com', '.scr', '.msi',
            '.sh', '.bash', '.zsh', '.ps1', '.jar', '.js',
        ];
        return in_array(strtolower(trim($ext)), $dangerous, true);
    }

    private function isDangerousExecutableContent(string $content): bool
    {
        $trim = ltrim($content);
        if ($trim === '') {
            return false;
        }
        if (str_starts_with($trim, 'MZ')) {
            return true;
        }
        if (substr($trim, 0, 4) === "\x7fELF") {
            return true;
        }
        return str_starts_with($trim, '#!');
    }

    private function isDangerousScriptContent(string $content): bool
    {
        $upper = strtoupper(trim($content));
        foreach (['<?PHP', '<SCRIPT', 'POWERSHELL', 'WSCRIPT.SHELL', 'SHELL_EXEC'] as $marker) {
            if (str_contains($upper, $marker)) {
                return true;
            }
        }
        return false;
    }

    private function mimeMatches(string $expected, string $detected): bool
    {
        $expected = strtolower(trim($expected));
        $detected = strtolower(trim($detected));
        if ($expected === '' || $expected === $detected) {
            return true;
        }
        if (str_starts_with($expected, 'image/') && str_starts_with($detected, 'image/')) {
            return $expected === $detected;
        }
        if (str_contains($expected, 'pdf') && str_contains($detected, 'pdf')) {
            return true;
        }
        return false;
    }

    private function sanitizePathSegment(string $value): string
    {
        $value = trim($value);
        $value = str_replace(['..', '/', '\\'], ['', '-', '-'], $value);
        return $value === '' ? 'unknown' : $value;
    }

    private function isSafeStorageRelativePath(string $path): bool
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '..')) {
            return false;
        }
        return true;
    }
}
