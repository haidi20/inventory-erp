<?php

namespace App\Modules\Operational\Master\Entities;

class LookupSetValueListResultEntity
{
    /**
     * @param LookupSetValueEntity[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $filtered,
        public int $draw = 0,
    ) {
    }
}
