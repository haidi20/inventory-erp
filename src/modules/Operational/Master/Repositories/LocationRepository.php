<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Inventory\Enums\StockSourceType;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * LocationRepository
 *
 * Implementasi repository untuk manajemen data Gudang (Location).
 * Menangani logika akses data, inisialisasi stok otomatis, dan integrasi dengan cabang.
 */
class LocationRepository implements LocationRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getActiveLocations(): array
    {
        $items = Location::query()
            ->select('locations.*')
            ->join('shifts', 'shifts.location_uuid', '=', 'locations.uuid')
            ->where('locations.is_active', true)
            ->groupBy('locations.uuid')
            ->orderBy('locations.name', 'ASC')
            ->get();

        return $items->map(fn($m) => LocationEntity::fromModel($m))->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function findByUuid(LocationEntity $entity): ?LocationEntity
    {
        $unit = Location::query()
            ->where('uuid', trim($entity->uuid))
            ->where('branch_uuid', $entity->branch_uuid)
            ->where('company_uuid', $entity->company_uuid)
            ->first();

        return $unit ? LocationEntity::fromModel($unit) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPageData(LocationEntity $entity): array
    {
        $query = Location::query()
            ->leftJoin('branches', 'branches.uuid', '=', 'locations.branch_uuid')
            ->orderBy('locations.name', 'ASC');

        if ($entity->filter_active !== null) {
            $query->where('locations.is_active', (bool) $entity->filter_active);
        }

        if (! empty($entity->company_uuid)) {
            $query->where('locations.company_uuid', trim($entity->company_uuid));
        }

        if (! empty($entity->branch_uuid)) {
            $query->where('locations.branch_uuid', trim($entity->branch_uuid));
        }

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            switch ($column) {
                case 'branch':
                    $query->where(function ($q) use ($search) {
                        $q->whereRaw('LOWER(branches.name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(branches.short_name) LIKE ?', ["%{$search}%"]);
                    });
                    break;
                case 'code':
                case 'name':
                case 'description':
                    $query->whereRaw("LOWER(locations.{$column}) LIKE ?", ["%{$search}%"]);
                    break;
                default:
                    $query->where(function ($q) use ($search) {
                        $q->whereRaw('LOWER(locations.code) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(locations.name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw("LOWER(COALESCE(locations.description, '')) LIKE ?", ["%{$search}%"])
                            ->orWhereRaw('LOWER(branches.name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(branches.short_name) LIKE ?', ["%{$search}%"]);
                    });
            }
        }

        $paginator = $query->select('locations.*')
            ->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => LocationEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'search_column' => $entity->search_column,
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid,
            'active_locations' => Location::query()
                ->where('is_active', true)
                ->when(! empty($entity->company_uuid), fn ($builder) => $builder->where('company_uuid', trim($entity->company_uuid)))
                ->count(),
            'branches' => $this->getBranchOptions($entity->company_uuid),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function save(LocationEntity $entity): LocationEntity
    {
        DB::beginTransaction();
        try {
            $location = Location::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'branch_uuid' => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'code' => strtoupper(trim($entity->code)),
                    'name' => trim($entity->name),
                    'description' => $entity->description ? trim($entity->description) : null,
                    'is_active' => $entity->is_active,
                    'can_sell' => $entity->can_sell,
                    'is_primary' => $entity->is_primary,
                    'address_uuid' => $entity->address_uuid,
                ]
            );

            // AUTOMATIC INITIALIZATION: Create zero stock records for all products in the branch
            // This only runs for NEW locations (checked by existence of stock record)
        $products = Product::query()
                ->where('branch_uuid', $location->branch_uuid)
                ->where('company_uuid', $location->company_uuid)
                ->get();

            foreach ($products as $product) {
                ProductLocation::query()->firstOrCreate(
                    [
                        'item_uuid' => $product->uuid,
                        'source_id' => $location->uuid,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $location->branch_uuid,
                        'company_uuid' => $location->company_uuid,
                        'on_hand_qty' => 0,
                        'available_qty' => 0,
                        'deleted_at' => 0,
                    ]
                );
            }

            DB::commit();
            return LocationEntity::fromModel($location->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(LocationEntity $entity): void
    {
        Location::query()
            ->where('uuid', trim($entity->uuid))
            ->where('branch_uuid', $entity->branch_uuid)
            ->where('company_uuid', $entity->company_uuid)
            ->firstOrFail()
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getByBranchAndCode(LocationEntity $entity): ?LocationEntity
    {
        $unit = Location::query()
            ->where('branch_uuid', trim($entity->branch_uuid))
            ->when(! empty($entity->company_uuid), fn ($builder) => $builder->where('company_uuid', trim($entity->company_uuid)))
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($entity->code))])
            ->first();

        return $unit ? LocationEntity::fromModel($unit) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function listForExport(LocationEntity $entity): array
    {
        $query = Location::query()
            ->leftJoin('branches', 'branches.uuid', '=', 'locations.branch_uuid')
            ->orderBy('locations.name', 'ASC');

        if (! empty($entity->company_uuid)) {
            $query->where('locations.company_uuid', trim($entity->company_uuid));
        }

        if (! empty($entity->branch_uuid)) {
            $query->where('locations.branch_uuid', trim($entity->branch_uuid));
        }

        if (! empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(locations.code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(locations.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(locations.description, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw('LOWER(branches.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(branches.short_name) LIKE ?', ["%{$search}%"]);
            });
        }

        $items = $query->select('locations.*')->get();
        return $items->map(fn($m) => LocationEntity::fromModel($m))->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function hasShifts(string $uuid): bool
    {
        if (! Schema::hasTable('shifts')) {
            return false;
        }

        return Shift::withoutGlobalScopes()
            ->where('location_uuid', trim($uuid))
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function getBranchOptions(?string $companyUuid = null): array
    {
        return Branch::query()
            ->where('deleted_at', 0)
            ->when(! empty($companyUuid), fn ($builder) => $builder->where('company_uuid', trim($companyUuid)))
            ->orderBy('name', 'ASC')
            ->get()
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getBranchByShortName(string $shortName): ?array
    {
        $branch = Branch::query()
            ->whereRaw('UPPER(short_name) = ?', [strtoupper(trim($shortName))])
            ->where('deleted_at', 0)
            ->first();

        return $branch?->toArray();
    }
}
