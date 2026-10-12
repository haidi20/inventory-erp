<?php

namespace App\Modules\Operational\Master\Entities;

class EmployeeEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?int $id = null,
        public ?string $search = null,
        public ?string $filter_role = null,
        public ?string $nik = null,
        public ?string $name = null,
        public ?string $role = null,
        public ?string $user_uuid = null,
        public ?string $specialization = null,
        public float|int|null $fee_default = 0.0,
        public ?string $sip_number = null,
        public ?string $str_number = null,
        public ?string $ihs_practitioner_id = null,
        public ?bool $is_active = true,
        public ?int $page = 1,
        public ?int $per_page = 25,
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'nik' => $this->nik,
            'name' => $this->name,
            'role' => $this->role,
            'user_uuid' => $this->user_uuid,
            'specialization' => $this->specialization,
            'fee_default' => $this->fee_default,
            'sip_number' => $this->sip_number,
            'str_number' => $this->str_number,
            'ihs_practitioner_id' => $this->ihs_practitioner_id,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            nik: $model->nik,
            name: $model->name,
            role: $model->role,
            user_uuid: $model->user_uuid,
            specialization: $model->specialization,
            fee_default: (float) ($model->fee_default ?? 0.0),
            sip_number: $model->sip_number,
            str_number: $model->str_number,
            ihs_practitioner_id: $model->ihs_practitioner_id,
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            search: isset($data['search']) ? trim((string) $data['search']) : null,
            filter_role: $data['filter_role'] ?? $data['role_filter'] ?? $data['role'] ?? null,
            nik: $data['nik'] ?? null,
            name: $data['name'] ?? null,
            role: $data['role'] ?? null,
            user_uuid: $data['user_uuid'] ?? null,
            specialization: $data['specialization'] ?? null,
            fee_default: isset($data['fee_default']) ? (float) $data['fee_default'] : 0.0,
            sip_number: $data['sip_number'] ?? null,
            str_number: $data['str_number'] ?? null,
            ihs_practitioner_id: $data['ihs_practitioner_id'] ?? $data['ihs_practitioner_uuid'] ?? null,
            is_active: array_key_exists('is_active', $data) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            page: $data['page'] ?? 1,
            per_page: $data['per_page'] ?? 25,
        );
    }
}
