<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Operational\Master\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PaymentTermRepository
 *
 * Implementasi repository untuk manajemen data Termin Pembayaran (Payment Term).
 * Mengatur logika akses data, pencarian, dan persistence untuk model PaymentTerm.
 */
class PaymentTermRepository implements PaymentTermRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPageData(PaymentTermEntity $entity): array
    {
        $query = PaymentTerm::query()
            ->where('deleted_at', 0)
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->orderBy('name', 'ASC');

        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['name', 'description'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn ($m) => PaymentTermEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'active_terms' => PaymentTerm::query()
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
    public function findByUuid(PaymentTermEntity $entity): ?PaymentTermEntity
    {
        $model = PaymentTerm::query()
            ->where('uuid', trim($entity->uuid))
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->where('deleted_at', 0)
            ->first();

        return $model ? PaymentTermEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(PaymentTermEntity $entity): PaymentTermEntity
    {
        DB::beginTransaction();
        try {
            $term = PaymentTerm::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'company_uuid' => $entity->company_uuid,
                    'branch_uuid' => $entity->branch_uuid,
                    'name' => trim($entity->name),
                    'description' => $entity->description ? trim($entity->description) : null,
                    'due_days' => $entity->due_days,
                    'is_active' => $entity->is_active ?? true,
                    'is_primary' => $entity->is_primary ?? false,
                ]
            );

            DB::commit();

            return PaymentTermEntity::fromModel($term->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(PaymentTermEntity $entity): void
    {
        DB::beginTransaction();
        try {
            PaymentTerm::query()
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
    public function listForExport(PaymentTermEntity $entity): array
    {
        $query = PaymentTerm::query()
            ->where('deleted_at', 0)
            ->where('company_uuid', $entity->company_uuid)
            ->when(! empty($entity->branch_uuid), fn ($q) => $q->where('branch_uuid', $entity->branch_uuid))
            ->orderBy('name', 'ASC');

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        $items = $query->get();

        return $items->map(fn ($m) => PaymentTermEntity::fromModel($m))->toArray();
    }
}
