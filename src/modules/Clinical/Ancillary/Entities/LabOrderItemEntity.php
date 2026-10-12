<?php

namespace App\Modules\Clinical\Ancillary\Entities;

use App\Modules\Clinical\Ancillary\Models\LabOrderItem;

class LabOrderItemEntity
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly ?string $lab_order_uuid = null,
        public readonly ?string $branch_uuid = null,
        public readonly ?string $company_uuid = null,
        public readonly string  $test_name = '',
        public readonly ?string $test_code = null,
        public readonly ?string $result = null,
        public readonly ?string $unit = null,
        public readonly ?string $normal_range = null,
        public readonly ?string $flag = null,
    ) {}

    public static function fromModel(LabOrderItem $model): self
    {
        return new self(
            uuid:           $model->uuid,
            lab_order_uuid: $model->lab_order_uuid,
            branch_uuid:    $model->branch_uuid,
            company_uuid:   $model->company_uuid,
            test_name:      $model->test_name,
            test_code:      $model->test_code,
            result:         $model->result,
            unit:           $model->unit,
            normal_range:   $model->normal_range,
            flag:           $model->flag,
        );
    }
}
