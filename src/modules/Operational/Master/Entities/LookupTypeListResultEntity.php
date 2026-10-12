<?php

namespace App\Modules\Operational\Master\Entities;

class LookupTypeListResultEntity
{
    /**
     * @param LookupTypeEntity[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $per_page,
        public int $current_page,
    ) {
    }
}
