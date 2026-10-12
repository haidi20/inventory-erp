<?php

namespace App\Modules\Clinical\Integration\Entities;

class SatusehatSettingsEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $org_uuid = null,
        public ?string $client_uuid = null,
        public ?string $client_secret = null,
        public ?string $base_url = null,
        public ?string $auth_url = null,
        public bool $is_active = false,
        public ?string $actor_uuid = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            org_uuid: $model->org_uuid,
            client_uuid: $model->client_uuid,
            client_secret: $model->client_secret,
            base_url: $model->base_url,
            auth_url: $model->auth_url,
            is_active: (bool) $model->is_active,
            actor_uuid: $model->updated_by ?? $model->created_by,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            org_uuid: $data['org_uuid'] ?? $data['org_id'] ?? null,
            client_uuid: $data['client_uuid'] ?? $data['client_id'] ?? null,
            client_secret: $data['client_secret'] ?? null,
            base_url: $data['base_url'] ?? null,
            auth_url: $data['auth_url'] ?? null,
            is_active: (bool) ($data['is_active'] ?? false),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'uuid'          => $this->uuid,
            'org_uuid'      => $this->org_uuid,
            'client_uuid'   => $this->client_uuid,
            'base_url'      => $this->base_url,
            'auth_url'      => $this->auth_url,
            'is_active'     => $this->is_active,
        ];

        if ($this->client_secret !== null) {
            $arr['client_secret'] = $this->client_secret;
        }

        return $arr;
    }
}