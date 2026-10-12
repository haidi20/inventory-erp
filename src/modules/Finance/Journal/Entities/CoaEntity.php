<?php

namespace App\Modules\Finance\Journal\Entities;

class CoaEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $type = null,
        public ?string $account_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $actor_uuid = null
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid ?? null,
            code: $model->code ?? null,
            name: $model->name ?? null,
            type: $model->type ?? null,
            account_uuid: $model->account_uuid ?? null,
            branch_uuid: $model->branch_uuid ?? null,
            company_uuid: $model->company_uuid ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            account_uuid: $data['account_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'account_uuid' => $this->account_uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }
}
