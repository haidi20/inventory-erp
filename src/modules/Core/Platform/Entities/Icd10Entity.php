<?php

namespace App\Modules\Core\Platform\Entities;

use Illuminate\Support\Str;

class Icd10Entity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name_id = null,
        public ?string $name_en = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? (string) Str::uuid(),
            code: $data['code'] ?? null,
            name_id: $data['name_id'] ?? null,
            name_en: $data['name_en'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'uuid'    => $this->uuid,
            'code'    => $this->code,
            'name_id' => $this->name_id,
            'name_en' => $this->name_en,
        ], fn($v) => $v !== null);
    }
}

