<?php

namespace App\Modules\Clinical\Integration\Entities;

/**
 * SatusehatPayloadEntity
 * 
 * Entitas pembungkus untuk payload FHIR Satusehat.
 */
class SatusehatPayloadEntity
{
    public function __construct(
        public ?string $resource_type = null,
        public array $data = [],
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resource_type: $data['resource_type'] ?? null,
            data: $data['data'] ?? $data,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}

