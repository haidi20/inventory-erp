<?php

namespace App\Modules\Core\Platform\Entities;

use Illuminate\Http\UploadedFile;

class DatabaseRestoreEntity
{
    public function __construct(
        public ?UploadedFile $backup_file = null,
        public ?bool $create_pre_restore_backup = true,
        public ?string $stored_file_path = null,
        public ?string $initiated_by_source = 'user',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            backup_file: $data['backup_file'] ?? null,
            create_pre_restore_backup: array_key_exists('create_pre_restore_backup', $data)
                ? filter_var($data['create_pre_restore_backup'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : true,
            stored_file_path: isset($data['stored_file_path']) ? (string) $data['stored_file_path'] : null,
            initiated_by_source: isset($data['initiated_by_source']) ? (string) $data['initiated_by_source'] : 'user',
        );
    }
}

