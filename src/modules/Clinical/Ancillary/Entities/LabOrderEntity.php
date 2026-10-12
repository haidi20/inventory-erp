<?php

namespace App\Modules\Clinical\Ancillary\Entities;

use App\Modules\Clinical\Ancillary\Models\LabOrder;

class LabOrderEntity
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly ?string $branch_uuid = null,
        public readonly ?string $company_uuid = null,
        public readonly ?string $order_no = null,
        public readonly ?string $patient_uuid = null,
        public readonly ?string $doctor_uuid = null,
        public readonly ?int    $ordered_at = null,
        public readonly string  $status = 'pending',
        public readonly ?string $clinical_notes = null,
        public readonly ?string $resulted_by = null,
        public readonly ?int    $resulted_at = null,
        public readonly ?string $actor_uuid = null,
        public readonly array   $items = [],
        public readonly int     $page = 1,
        public readonly int     $per_page = 15,
        public readonly ?string $search = null,
    ) {}

    public static function fromModel(LabOrder $model): self
    {
        return new self(
            uuid:          $model->uuid,
            branch_uuid:   $model->branch_uuid,
            company_uuid:  $model->company_uuid,
            order_no:      $model->order_no,
            patient_uuid:  $model->patient_uuid,
            doctor_uuid:   $model->doctor_uuid,
            ordered_at:    $model->ordered_at,
            status:        $model->status,
            clinical_notes: $model->clinical_notes,
            resulted_by:   $model->resulted_by,
            resulted_at:   $model->resulted_at,
        );
    }
}
