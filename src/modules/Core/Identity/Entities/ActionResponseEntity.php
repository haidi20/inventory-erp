<?php

namespace App\Modules\Core\Identity\Entities;

/**
 * ActionResponseEntity
 * 
 * Entitas standar untuk respon operasi (Create/Update/Delete/Post).
 */
class ActionResponseEntity
{
    public function __construct(
        public bool $success = true,
        public string $message = '',
        public string $status = 'success',
        public array $errors = [],
        public ?string $uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: filter_var($data['success'] ?? true, FILTER_VALIDATE_BOOLEAN),
            message: $data['message'] ?? '',
            status: $data['status'] ?? 'success',
            errors: $data['errors'] ?? [],
            uuid: $data['uuid'] ?? null
        );
    }
}
