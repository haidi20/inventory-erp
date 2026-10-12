<?php

namespace App\Modules\Operational\Master\Entities;

class LookupDocumentEntity
{
    public function __construct(
        public string $sheet,
        public array $headers = [],
        public array $items = [],
        public array $guide = [],
        public ?string $filename = null,
        public array $sample_row = [],
    ) {
    }
}
