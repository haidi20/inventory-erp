<?php

namespace App\Modules\Clinical\Registration\Entities;

class PatientEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?int $id = null,
        public ?string $search = null,
        public ?string $gender = null,
        public ?string $medical_record_no = null,
        public ?string $name = null,
        public ?string $birth_date = null,
        public ?string $phone = null,
        public ?string $bpjs_number = null,
        public ?bool $is_active = null,
        public ?int $page = 1,
        public ?int $per_page = 20,
        public ?string $actor_uuid = null,
        public ?string $gender_filter = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            medical_record_no: $model->medical_record_no,
            name: $model->name,
            birth_date: $model->birth_date,
            gender: $model->gender,
            phone: $model->phone,
            bpjs_number: $model->bpjs_number,
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
            gender: $data['gender'] ?? null,
            medical_record_no: $data['medical_record_no'] ?? null,
            name: $data['name'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            phone: $data['phone'] ?? null,
            bpjs_number: $data['bpjs_number'] ?? null,
            is_active: array_key_exists('is_active', $data) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'medical_record_no' => $this->medical_record_no,
            'name' => $this->name,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'phone' => $this->phone,
            'bpjs_number' => $this->bpjs_number,
            'is_active' => $this->is_active,
        ];
    }
}
