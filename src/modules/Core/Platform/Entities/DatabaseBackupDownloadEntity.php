<?php

namespace App\Modules\Core\Platform\Entities;

class DatabaseBackupDownloadEntity
{
    public function __construct(
        public ?string $filename = null,
        public ?string $history_type = 'manual',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            filename: isset($data['filename']) ? trim((string) $data['filename']) : null,
            history_type: isset($data['history_type']) ? trim((string) $data['history_type']) : 'manual',
        );
    }
}

