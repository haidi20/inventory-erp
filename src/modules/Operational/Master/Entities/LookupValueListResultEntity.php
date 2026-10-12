<?php

namespace App\Modules\Operational\Master\Entities;

class LookupValueListResultEntity
{
    /**
     * @param LookupValueEntity[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $per_page,
        public int $current_page,
    ) {
    }
}
