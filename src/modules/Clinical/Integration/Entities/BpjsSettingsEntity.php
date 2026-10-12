<?php

namespace App\Modules\Clinical\Integration\Entities;

class BpjsSettingsEntity
{
    public function __construct(
        public ?string $base_url = null,
        public ?string $consumer_id = null,
        public ?string $user_key = null,
        public ?string $secret_key = null,
        public ?string $ppk_code = null,
        public bool $is_active = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            base_url: $data['base_url'] ?? null,
            consumer_id: $data['consumer_id'] ?? null,
            user_key: $data['user_key'] ?? null,
            secret_key: $data['secret_key'] ?? null,
            ppk_code: $data['ppk_code'] ?? null,
            is_active: (bool) ($data['is_active'] ?? false),
        );
    }

    public function toArray(): array
    {
        $arr = [
            'base_url'    => $this->base_url,
            'consumer_id' => $this->consumer_id,
            'user_key'    => $this->user_key,
            'ppk_code'    => $this->ppk_code,
            'is_active'   => $this->is_active,
        ];

        if ($this->secret_key !== null) {
            $arr['secret_key'] = $this->secret_key;
        }

        return $arr;
    }
}

