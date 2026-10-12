<?php

namespace App\Modules\Core\Platform\Entities;

/**
 * FileRequestEntity
 *
 * Entitas untuk permintaan upload, download, dan pengelolaan file.
 * Dipulihkan dari app/Entities/Infrastructure (pre-modularisasi) dan
 * ditempatkan di Platform sejajar FileService/ImageService yang
 * mengonsumsinya.
 */
class FileRequestEntity
{
    public function __construct(
        public ?string $file_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $parent_uuid = null,
        public ?string $parent_model = null,
        public ?string $feature = null,
        public ?string $context_type = null,
        public ?string $context_identifier = null,
        public ?string $original_filename = null,
        public ?string $mime_type = null,
        public ?string $file_content = null,
        public int $file_size = 0,
        public array $options = [],
        public ?string $uploaded_by_uuid = null,
        public ?string $uploaded_ip = null,
        public ?string $uploaded_by_role = null,
        public ?string $actor_uuid = null,
        public ?string $actor_ip = null,
        public ?string $actor_role = null,
        public ?string $reason = null,
        public ?string $variant = 'original',
        public ?string $disposition = 'attachment',
        public ?string $archive_path = null,
        public ?string $backup_type = 'full',
        public ?string $restore_mode = 'replace',
        public bool $verify_checksum = true,
        public bool $include_deleted = false,
        public bool $repair_mode = false,
        public bool $delete_variants = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            file_uuid: $data['file_uuid'] ?? $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            parent_uuid: $data['parent_uuid'] ?? null,
            parent_model: $data['parent_model'] ?? null,
            feature: $data['feature'] ?? null,
            context_type: $data['context_type'] ?? null,
            context_identifier: $data['context_identifier'] ?? null,
            original_filename: $data['original_filename'] ?? null,
            mime_type: $data['mime_type'] ?? null,
            file_content: $data['file_content'] ?? null,
            file_size: (int) ($data['file_size'] ?? 0),
            options: $data['options'] ?? [],
            uploaded_by_uuid: $data['uploaded_by_uuid'] ?? $data['user_uuid'] ?? null,
            uploaded_ip: $data['uploaded_ip'] ?? $data['user_ip'] ?? null,
            uploaded_by_role: $data['uploaded_by_role'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? $data['user_uuid'] ?? null,
            actor_ip: $data['actor_ip'] ?? $data['user_ip'] ?? null,
            actor_role: $data['actor_role'] ?? null,
            reason: $data['reason'] ?? null,
            variant: $data['variant'] ?? 'original',
            disposition: $data['disposition'] ?? 'attachment',
            archive_path: $data['archive_path'] ?? null,
            backup_type: $data['backup_type'] ?? 'full',
            restore_mode: $data['restore_mode'] ?? 'replace',
            verify_checksum: filter_var($data['verify_checksum'] ?? true, FILTER_VALIDATE_BOOLEAN),
            include_deleted: filter_var($data['include_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            repair_mode: filter_var($data['repair_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            delete_variants: filter_var($data['delete_variants'] ?? false, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}
