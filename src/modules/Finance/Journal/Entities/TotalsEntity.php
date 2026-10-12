<?php

namespace App\Modules\Finance\Journal\Entities;

class TotalsEntity
{
    public function __construct(
        public float $total_debit,
        public float $total_credit,
        public float $difference
    ) {}

    public function toArray(): array
    {
        return [
            'total_debit' => $this->total_debit,
            'total_credit' => $this->total_credit,
            'difference' => $this->difference,
        ];
    }
}
