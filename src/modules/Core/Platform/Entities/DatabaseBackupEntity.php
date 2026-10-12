<?php

namespace App\Modules\Core\Platform\Entities;

class DatabaseBackupEntity
{
    public function __construct(
        public ?string $notes = null,
        public ?bool $download_after_export = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            notes: isset($data['notes']) ? trim((string) $data['notes']) : null,
            download_after_export: array_key_exists('download_after_export', $data)
                ? filter_var($data['download_after_export'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : true,
        );
    }
}

