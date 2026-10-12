<?php

namespace App\Modules\Operational\Master\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ProductFilterBuilder
{
    public function applyScope(Builder $query, ProductFilter $filter): void
    {
        if ($filter->isSuperadmin) {
            if (! empty($filter->filterCompanyUuid) && $filter->filterCompanyUuid !== 'all') {
                $query->where('company_uuid', $filter->filterCompanyUuid);
            }

            return;
        }

        if (! empty($filter->branchUuid)) {
            $query->where('branch_uuid', $filter->branchUuid);
        }

        if (! empty($filter->companyUuid)) {
            $query->where('company_uuid', $filter->companyUuid);
        }
    }

    public function apply(Builder $query, ProductFilter $filter): void
    {
        $this->applyScope($query, $filter);
        $this->applySearch($query, $filter);

        if (! empty($filter->filterCategory)) {
            $query->where('product_uuid', $filter->filterCategory);
        }

        foreach ([
            'filterBarcode' => 'barcode',
            'filterCode' => 'code',
            'filterName' => 'name',
        ] as $property => $column) {
            if ($value = $filter->{$property}) {
                $query->where($column, 'like', "%{$value}%");
            }
        }

        if (! empty($filter->filterUnit)) {
            $value = $filter->filterUnit;
            if ($filter->isUuid($value)) {
                $query->where('base_unit_uuid', $value);
            } else {
                $query->where(function (Builder $builder) use ($value): void {
                    $builder->where('unit', 'like', "%{$value}%")
                        ->orWhereHas('baseUnit', fn (Builder $unit) => $unit->where('name', 'like', "%{$value}%"));
                });
            }
        }

        foreach ([
            'filterGroupUuid' => 'item_group_lookup_uuid',
            'filterTypeUuid' => 'item_type_lookup_uuid',
            'filterBrandUuid' => 'brand_lookup_uuid',
            'filterOriginUuid' => 'origin_lookup_uuid',
            'filterGradeUuid' => 'grade_lookup_uuid',
            'filterCreatedBy' => 'created_by',
            'filterUpdatedBy' => 'updated_by',
        ] as $property => $column) {
            if ($value = $filter->{$property}) {
                $query->where($column, $value);
            }
        }

        if (! empty($filter->filterKeterangan)) {
            $value = $filter->filterKeterangan;
            $query->where(function (Builder $builder) use ($value): void {
                $builder->where('description', 'like', "%{$value}%")
                    ->orWhere('note', 'like', "%{$value}%");
            });
        }

        if (! empty($filter->filterKind)) {
            $query->where('kind', $filter->filterKind);
        }

        foreach ([
            'filterCreatedFrom' => ['created_at', '>=', ' 00:00:00'],
            'filterCreatedTo' => ['created_at', '<=', ' 23:59:59'],
            'filterUpdatedFrom' => ['updated_at', '>=', ' 00:00:00'],
            'filterUpdatedTo' => ['updated_at', '<=', ' 23:59:59'],
        ] as $property => [$column, $operator, $time]) {
            if ($value = $filter->{$property}) {
                $query->where($column, $operator, strtotime($value.$time));
            }
        }

        if (! empty($filter->filterPeriode)) {
            $periodStart = Carbon::createFromFormat('!Y-m', $filter->filterPeriode)
                ->startOfDay()
                ->timestamp;
            $periodEnd = Carbon::createFromFormat('!Y-m', $filter->filterPeriode)
                ->endOfMonth()
                ->endOfDay()
                ->timestamp;

            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        }
    }

    private function applySearch(Builder $query, ProductFilter $filter): void
    {
        if (empty($filter->search)) {
            return;
        }

        $search = strtolower($filter->search);
        $column = strtolower($filter->searchColumn);

        if (in_array($column, ['name', 'item_name'], true)) {
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);

            return;
        }

        if (in_array($column, ['code', 'item_code'], true)) {
            $query->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);

            return;
        }

        if (in_array($column, ['barcode', 'item_barcode'], true)) {
            $query->whereRaw('LOWER(barcode) LIKE ?', ["%{$search}%"]);

            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$search}%"]);
        });
    }
}
