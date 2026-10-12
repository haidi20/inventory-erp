<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Operational\Master\Models\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TaxRepository
 *
 * Implementasi repository untuk manajemen data Pajak (Tax).
 * Mengatur logika akses data, pencarian, dan persistence untuk model Tax.
 */
class TaxRepository implements TaxRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPageData(TaxEntity $entity): array
    {
        $query = Tax::query()
            ->where('deleted_at', 0)
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->orderBy('tax_code', 'ASC');

        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->whereRaw('LOWER(tax_code) LIKE ?', ["%{$search}%"]);
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn ($m) => TaxEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'active_taxes' => Tax::query()
                ->where('deleted_at', 0)
                ->where('company_uuid', $entity->company_uuid)
                ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
                ->where('is_active', true)
                ->count(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findByUuid(TaxEntity $entity): ?TaxEntity
    {
        $model = Tax::query()
            ->where('uuid', trim($entity->uuid))
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->where('deleted_at', 0)
            ->first();

        return $model ? TaxEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(TaxEntity $entity): TaxEntity
    {
        DB::beginTransaction();
        try {
            // Jika UUID tersedia, match by UUID (update flow dari controller).
            // Jika tidak ada UUID (import flow), match by tax_code + company_uuid agar upsert scoped by tenant.
            $matchKey = $entity->uuid
                ? ['uuid' => $entity->uuid]
                : ['tax_code' => strtoupper(trim($entity->tax_code)), 'company_uuid' => $entity->company_uuid ?? ''];

            $tax = Tax::updateOrCreate(
                $matchKey,
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                    'tax_code' => strtoupper(trim($entity->tax_code)),
                    'percentage' => $entity->percentage,
                    'legacy_tax_type_uuid' => $entity->legacy_tax_type_uuid,
                    'legacy_sale_account_uuid' => $entity->legacy_sale_account_uuid,
                    'legacy_purchase_account_uuid' => $entity->legacy_purchase_account_uuid,
                    'is_active' => $entity->is_active,
                    'is_primary' => $entity->is_primary,
                    'is_pph' => $entity->is_income_tax,
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                ]
            );

            DB::commit();

            return TaxEntity::fromModel($tax->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(TaxEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Tax::query()
                ->where('uuid', trim($entity->uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function listForExport(TaxEntity $entity): array
    {
        $query = Tax::query()
            ->where('deleted_at', 0)
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->orderBy('tax_code', 'ASC');

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->whereRaw('LOWER(tax_code) LIKE ?', ["%{$search}%"]);
        }

        $items = $query->get();

        return $items->map(fn ($m) => TaxEntity::fromModel($m))->toArray();
    }
}
