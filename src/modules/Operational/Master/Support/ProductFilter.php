<?php

namespace App\Modules\Operational\Master\Support;

use App\Modules\Operational\Master\Entities\ProductEntity;
use Illuminate\Http\Request;

class ProductFilter
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly string $searchColumn = 'all',
        public readonly ?string $filterCategory = null,
        public readonly ?string $filterBarcode = null,
        public readonly ?string $filterCode = null,
        public readonly ?string $filterName = null,
        public readonly ?string $filterUnit = null,
        public readonly ?string $filterGroupUuid = null,
        public readonly ?string $filterKeterangan = null,
        public readonly ?string $filterKind = null,
        public readonly ?string $filterTypeUuid = null,
        public readonly ?string $filterBrandUuid = null,
        public readonly ?string $filterOriginUuid = null,
        public readonly ?string $filterGradeUuid = null,
        public readonly ?string $filterCreatedBy = null,
        public readonly ?string $filterUpdatedBy = null,
        public readonly ?string $filterCreatedFrom = null,
        public readonly ?string $filterCreatedTo = null,
        public readonly ?string $filterUpdatedFrom = null,
        public readonly ?string $filterUpdatedTo = null,
        public readonly ?string $filterPeriode = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDirection = null,
        public readonly ?int $page = 1,
        public readonly ?int $perPage = 20,
        public readonly bool $isSuperadmin = false,
        public readonly ?string $branchUuid = null,
        public readonly ?string $companyUuid = null,
        public readonly ?string $filterCompanyUuid = null,
    ) {}

    public static function fromRequest(Request $request, bool $isSuperadmin, ?string $branchUuid, ?string $companyUuid, int $perPage = 20): self
    {
        $query = static fn (string $key, ?string $default = null): ?string => $request->filled($key)
            ? trim((string) $request->query($key))
            : $default;

        return new self(
            search: $query('search'),
            searchColumn: strtolower((string) $request->query('search_column', 'all')),
            filterCategory: $query('filter_category'),
            filterBarcode: $query('filter_barcode'),
            filterCode: $query('filter_code'),
            filterName: $query('filter_name'),
            filterUnit: $query('filter_unit'),
            filterGroupUuid: $query('filter_group_uuid'),
            filterKeterangan: $query('filter_keterangan'),
            filterKind: $query('filter_kind'),
            filterTypeUuid: $query('filter_type_uuid'),
            filterBrandUuid: $query('filter_brand_uuid'),
            filterOriginUuid: $query('filter_origin_uuid'),
            filterGradeUuid: $query('filter_grade_uuid'),
            filterCreatedBy: $query('filter_created_by'),
            filterUpdatedBy: $query('filter_updated_by'),
            filterCreatedFrom: $query('filter_created_from'),
            filterCreatedTo: $query('filter_created_to'),
            filterUpdatedFrom: $query('filter_updated_from'),
            filterUpdatedTo: $query('filter_updated_to'),
            filterPeriode: $query('filter_periode'),
            sortBy: $query('sort_by'),
            sortDirection: $query('sort_direction'),
            page: max(1, (int) $request->query('page', 1)),
            perPage: $perPage,
            isSuperadmin: $isSuperadmin,
            branchUuid: $branchUuid,
            companyUuid: $companyUuid,
            filterCompanyUuid: $isSuperadmin ? ($query('filter_company_uuid') ?? 'all') : null,
        );
    }

    public static function fromEntity(ProductEntity $entity): self
    {
        return new self(
            search: $entity->search,
            searchColumn: strtolower((string) ($entity->search_column ?? 'all')),
            filterCategory: $entity->filter_category,
            filterBarcode: $entity->filter_barcode,
            filterCode: $entity->filter_code,
            filterName: $entity->filter_name,
            filterUnit: $entity->filter_unit,
            filterGroupUuid: $entity->filter_group_uuid,
            filterKeterangan: $entity->filter_keterangan,
            filterKind: $entity->filter_kind,
            filterTypeUuid: $entity->filter_type_uuid,
            filterBrandUuid: $entity->filter_brand_uuid,
            filterOriginUuid: $entity->filter_origin_uuid,
            filterGradeUuid: $entity->filter_grade_uuid,
            filterCreatedBy: $entity->filter_created_by,
            filterUpdatedBy: $entity->filter_updated_by,
            filterCreatedFrom: $entity->filter_created_from,
            filterCreatedTo: $entity->filter_created_to,
            filterUpdatedFrom: $entity->filter_updated_from,
            filterUpdatedTo: $entity->filter_updated_to,
            filterPeriode: $entity->filter_periode,
            sortBy: $entity->sort_by,
            sortDirection: $entity->sort_direction,
            page: $entity->page,
            perPage: $entity->per_page,
            isSuperadmin: (bool) $entity->is_superadmin,
            branchUuid: $entity->branch_uuid,
            companyUuid: $entity->company_uuid,
            filterCompanyUuid: $entity->filter_company_uuid,
        );
    }

    public function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
