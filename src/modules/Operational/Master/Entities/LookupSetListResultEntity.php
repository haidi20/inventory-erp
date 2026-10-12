<?php

namespace App\Modules\Operational\Master\Entities;

class LookupSetListResultEntity
{
    /**
     * @param LookupSetEntity[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $filtered,
        public int $draw = 0,
    ) {
    }
}
