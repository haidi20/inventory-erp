<?php

namespace App\Modules\Clinical\Classification\Entities;

use App\Modules\Clinical\Classification\Models\Icd10Code;

class Icd10CodeEntity
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly ?string $code = null,
        public readonly ?string $name_uuid = null,
        public readonly ?string $name_en = null,
        public readonly ?string $category = null,
        public readonly ?string $actor_uuid = null,
        public readonly int     $page = 1,
        public readonly int     $per_page = 15,
        public readonly ?string $search = null,
    ) {}

    public static function fromModel(Icd10Code $model): self
    {
        return new self(
            uuid:      $model->uuid,
            code:      $model->code,
            name_uuid: $model->name_uuid,
            name_en:   $model->name_en,
            category:  $model->category,
        );
    }
}
