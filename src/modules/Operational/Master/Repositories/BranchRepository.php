<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * BranchRepository
 * 
 * Implementasi repository untuk manajemen data master Cabang (Branch).
 * Menangani organisasi multi-cabang, penamaan singkat unik, dan validasi infrastruktur.
 */
class BranchRepository implements BranchRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(BranchEntity $entity): array
    {
        $query = Branch::query()->orderBy('name', 'ASC');

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all') {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(short_name) LIKE ?", ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        // Stats
        $activeBranches = Branch::query()->where('is_active', true)->count();

        return [
            'items' => array_map(fn($m) => BranchEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'search' => $entity->search,
            'stats' => [
                'active_branches' => $activeBranches,
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid): ?BranchEntity
    {
        $model = Branch::query()->where('uuid', $uuid)->first();
        return $model ? BranchEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(BranchEntity $entity): BranchEntity
    {
        DB::beginTransaction();
        try {
            $branch = Branch::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'company_uuid' => $entity->company_uuid,
                    'name' => trim($entity->name),
                    'short_name' => strtoupper(trim($entity->short_name)),
                    'description' => $entity->description,
                    'is_active' => $entity->is_active,
                    'updated_by' => $entity->actor_uuid,
                ]
            );

            DB::commit();
            return BranchEntity::fromModel($branch->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(BranchEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Branch::query()->where('uuid', $entity->uuid)->firstOrFail()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getByShortName(BranchEntity $entity): ?BranchEntity
    {
        $model = Branch::query()
            ->whereRaw("UPPER(short_name) = ?", [strtoupper(trim($entity->short_name))])
            ->first();
            
        return $model ? BranchEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function hasWarehouses(string $uuid): bool
    {
        return Location::query()->where('branch_uuid', $uuid)->exists();
    }

    /**
     * @inheritDoc
     */
    public function listForExport(BranchEntity $entity): array
    {
        $query = Branch::query()->orderBy('name', 'ASC');

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(short_name) LIKE ?", ["%{$search}%"]);
            });
        }

        $items = $query->get();
        return $items->map(fn($m) => BranchEntity::fromModel($m))->toArray();
    }
}
