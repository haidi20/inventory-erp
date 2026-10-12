<?php

namespace App\Modules\Finance\Journal\Entities;

class PeriodInfoEntity
{
    public function __construct(
        public string $period_uuid,
        public string $period_name,
        public string $start_date,
        public string $end_date,
        public string $status
    ) {}

    public function toArray(): array
    {
        return [
            'period_uuid' => $this->period_uuid,
            'period_name' => $this->period_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ];
    }
}
