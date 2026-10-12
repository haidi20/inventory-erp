<?php

namespace App\Modules\Operational\Master\Entities;

class LookupSetEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $name = null,
        public ?string $program_code = null,
        public ?string $description = null,
        public ?string $user_can_edit_flag = 'Y',
        public ?string $company_uuid = null,
        public ?string $search = null,
        public ?string $search_column = null,
        public ?string $sort_by = null,
        public ?bool $is_active = null,
        public ?bool $filter_active = null,
        public ?string $sort_direction = null,
        public int $page = 1,
        public int $per_page = 10,
        public int $start = 0,
        public int $length = 10,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            program_code: $data['program_code'] ?? null,
            description: $data['description'] ?? null,
            user_can_edit_flag: $data['user_can_edit_flag'] ?? 'Y',
            company_uuid: $data['company_uuid'] ?? null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            sort_by: $data['sort_by'] ?? null,
            sort_direction: $data['sort_direction'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 10),
            start: (int) ($data['start'] ?? 0),
            length: (int) ($data['length'] ?? 10),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public static function fromModel(object $model): self
    {
        return new self(
            uuid: $model->uuid ?? null,
            id: isset($model->lookup_set_uuid) ? (int) $model->lookup_set_uuid : (isset($model->id) ? (int) $model->id : null),
            name: $model->name ?? null,
            program_code: $model->program_code ?? $model->code ?? null,
            is_active: isset($model->is_active) ? (bool) $model->is_active : null,
            description: $model->description ?? null,
            user_can_edit_flag: $model->user_can_edit_flag ?? 'Y',
            company_uuid: $model->company_uuid ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'program_code' => $this->program_code,
            'description' => $this->description,
            'user_can_edit_flag' => $this->user_can_edit_flag,
            'is_active' => $this->is_active,
            'company_uuid' => $this->company_uuid,
        ];
    }
}