<?php

namespace App\Modules\Finance\Journal\Entities;

class AssetEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?float $asset_value = null,
        public ?int $life_year = null,
        public ?int $depreciation_method_uuid = null,
        public ?float $yearly_depreciation = null,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid ?? null,
            code: $model->code ?? null,
            name: $model->name ?? null,
            branch_uuid: $model->branch_uuid ?? null,
            company_uuid: $model->company_uuid ?? null,
            asset_value: isset($model->asset_value) ? (float) $model->asset_value : null,
            life_year: isset($model->life_year) ? (int) $model->life_year : null,
            depreciation_method_uuid: isset($model->depreciation_method_uuid) ? (int) $model->depreciation_method_uuid : null,
            yearly_depreciation: isset($model->yearly_depreciation) ? (float) $model->yearly_depreciation : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            asset_value: isset($data['asset_value']) ? (float) $data['asset_value'] : null,
            life_year: isset($data['life_year']) ? (int) $data['life_year'] : null,
            depreciation_method_uuid: isset($data['depreciation_method_uuid']) ? (int) $data['depreciation_method_uuid'] : null,
            yearly_depreciation: isset($data['yearly_depreciation']) ? (float) $data['yearly_depreciation'] : null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 10),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'asset_value' => $this->asset_value,
            'life_year' => $this->life_year,
            'depreciation_method_uuid' => $this->depreciation_method_uuid,
            'yearly_depreciation' => $this->yearly_depreciation,
        ];
    }
}
