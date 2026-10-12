<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductAccount;
use App\Modules\Operational\Master\Models\ProductDiscountRule;
use App\Modules\Operational\Master\Models\ProductImage;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\ProductOpeningBalance;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Models\ProductUnit;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Support\ProductFilter;
use App\Modules\Operational\Master\Support\ProductFilterBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ProductRepository
 *
 * Implementasi repository untuk manajemen data Master Produk (Barang/Jasa).
 * Menangani logika kompleks terkait stok, integrasi unit/harga multi-level, dan mutasi stok.
 */
class ProductRepository implements ProductRepositoryInterface
{
    private function productSortColumn(?string $sortBy): string
    {
        return in_array($sortBy, ['code', 'name', 'created_at', 'updated_at'], true) ? $sortBy : 'id';
    }

    private function productSortDirection(?string $sortDirection): string
    {
        if (empty($sortDirection)) {
            return empty($sortBy) ? 'desc' : 'asc';
        }

        return strtolower((string) $sortDirection) === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Terapkan scope perusahaan/cabang pada query agregasi yang sudah alias tabel.
     */
    private function applyAggregationScope($query, ProductFilter $filter): void
    {
        if ($filter->isSuperadmin) {
            if (! empty($filter->filterCompanyUuid) && $filter->filterCompanyUuid !== 'all') {
                $query->where('items.company_uuid', $filter->filterCompanyUuid);
            }

            return;
        }

        if (! empty($filter->branchUuid)) {
            $query->where('items.branch_uuid', $filter->branchUuid);
        }

        if (! empty($filter->companyUuid)) {
            $query->where('items.company_uuid', $filter->companyUuid);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function query(): Builder
    {
        return Product::query();
    }

    /**
     * {@inheritDoc}
     */
    public function getPageData(ProductEntity $entity): array
    {
        $query = Product::query()
            ->with(['categoryLookup', 'groupLookup', 'typeLookup', 'brandLookup', 'originLookup', 'gradeLookup', 'baseUnit', 'createdByUser', 'updatedByUser'])
            ->orderBy($this->productSortColumn($entity->sort_by), $this->productSortDirection($entity->sort_direction));

        (new ProductFilterBuilder)->apply($query, ProductFilter::fromEntity($entity));

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        // Metadata statistics
        $filter = ProductFilter::fromEntity($entity);
        $activeProductsQuery = Product::query()->where('is_active', true);
        (new ProductFilterBuilder)->applyScope($activeProductsQuery, $filter);
        $activeProducts = $activeProductsQuery->count();

        $lowStockQuery = Product::query()
            ->select('items.uuid', 'items.min_stock')
            ->join('item_stocks', 'item_stocks.item_uuid', '=', 'items.uuid')
            ->where('items.is_active', true)
            ->where('items.min_stock', '>', 0)
            ->groupBy('items.uuid', 'items.min_stock');

        $this->applyAggregationScope($lowStockQuery, $filter);

        $lowStockCount = $lowStockQuery
            ->havingRaw('SUM(item_stocks.on_hand_qty) <= items.min_stock')
            ->get()
            ->count();

        $stockValueQuery = DB::table('item_stocks')
            ->join('items', 'items.uuid', '=', 'item_stocks.item_uuid')
            ->where('items.is_active', true)
            ->select(DB::raw('COALESCE(SUM(item_stocks.on_hand_qty * items.base_price_buy), 0) as total'));

        $this->applyAggregationScope($stockValueQuery, $filter);

        $stockValueTotal = $stockValueQuery->value('total');

        $categoriesQuery = Product::query()
            ->whereNotNull('product_uuid')
            ->where('product_uuid', '!=', '')
            ->groupBy('product_uuid')
            ->orderBy('product_uuid', 'ASC');

        (new ProductFilterBuilder)->applyScope($categoriesQuery, $filter);

        $categories = $categoriesQuery->pluck('product_uuid')->toArray();

        // Map total stock and transform to Entity for each item in the results
        $paginator->through(function ($item) {
            $item->stock = (float) $item->stocks()->sum('on_hand_qty');

            return ProductEntity::fromModel($item);
        });

        return [
            // Pass the paginator itself so blade views can call ->links().
            'items' => $paginator,
            'total' => $paginator->total(),
            // 'page' clashes with Inertia's reserved blade variable. Use current_page.
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'search_column' => $entity->search_column,
            'category' => $entity->filter_category,
            'active_products' => $activeProducts,
            'low_stock_count' => $lowStockCount,
            'stock_value_total' => (float) $stockValueTotal,
            'categories' => $categories,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions(ProductEntity $entity): array
    {
        $items = Product::query()
            ->with(['groupLookup', 'typeLookup', 'brandLookup', 'originLookup', 'gradeLookup'])
            ->when(true, function ($query) use ($entity) {
                (new ProductFilterBuilder)->applyScope($query, ProductFilter::fromEntity($entity));

                return $query;
            })
            ->get(['uuid', 'unit', 'kind', 'item_group_lookup_uuid', 'item_type_lookup_uuid', 'brand_lookup_uuid', 'origin_lookup_uuid', 'grade_lookup_uuid']);

        $collectLookupOptions = fn ($items, string $relation) => $items
            ->map(fn ($item) => $item->{$relation})
            ->filter()
            ->unique('uuid')
            ->map(fn ($lookup) => ['uuid' => $lookup->uuid, 'label' => $lookup->display_name])
            ->sortBy('label')
            ->values()
            ->all();

        return [
            'units' => $items->map(fn ($item) => $item->unit)->filter()->unique()->sort()->values()->all(),
            'kinds' => $items->map(fn ($item) => $item->kind)->filter()->unique()->sort()->values()->all(),
            'groups' => $collectLookupOptions($items, 'groupLookup'),
            'types' => $collectLookupOptions($items, 'typeLookup'),
            'brands' => $collectLookupOptions($items, 'brandLookup'),
            'origins' => $collectLookupOptions($items, 'originLookup'),
            'grades' => $collectLookupOptions($items, 'gradeLookup'),
            'users' => User::query()
                ->orderBy('name')
                ->get(['uuid', 'name'])
                ->map(fn ($user) => ['uuid' => $user->uuid, 'name' => $user->name])
                ->all(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findByUuid(ProductEntity $entity): ?ProductEntity
    {
        $product = Product::query()
            ->with(['categoryLookup', 'groupLookup', 'typeLookup', 'brandLookup', 'originLookup', 'gradeLookup', 'prices', 'units', 'stocks', 'images'])
            ->where('uuid', trim($entity->uuid))
            ->where('branch_uuid', $entity->branch_uuid)
            ->where('company_uuid', $entity->company_uuid)
            ->first();

        if ($product) {
            $product->stock = (float) $product->stocks()->sum('on_hand_qty');

            return ProductEntity::fromModel($product);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ProductEntity $entity, array $extraData = []): ProductEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'code' => trim($entity->code),
                'name' => trim($entity->name),
                'barcode' => $entity->barcode,
                'product_uuid' => $entity->category,
                'base_unit_uuid' => $entity->unit,
                'min_stock' => $entity->min_stock ?? 0,
                'max_stock' => $entity->max_stock ?? 0,
                'max_order' => $entity->max_order ?? 0,
                'is_bom_enabled' => $entity->is_assembly ?? false,
                'is_active' => $entity->is_active ?? true,
                'base_price_buy' => $entity->price_buy ?? 0,
                'base_price_sell' => $entity->price_sell ?? 0,
                'note' => $entity->remarks,
                'storage_bin_code' => $entity->location,
                'image' => $entity->image,
                'brand' => $entity->brand,
                'kind' => $entity->kind,
                'origin' => $entity->origin,
                'grade' => $entity->grade,
                'item_type' => $entity->item_type,
                'location' => $entity->location,
                'remarks' => $entity->remarks,
                'category' => $entity->category_name,
                'unit' => $entity->unit_name,
                'description' => $entity->description,
                'item_name_alt' => $entity->item_name_alt,
                'item_group_lookup_uuid' => $entity->group_uuid,
                'item_type_lookup_uuid' => $entity->type_uuid,
                'brand_lookup_uuid' => $entity->brand_uuid,
                'origin_lookup_uuid' => $entity->origin_uuid,
                'grade_lookup_uuid' => $entity->grade_uuid,
                'opening_cost' => $entity->opening_cost ?? 0,
                'coa_sales_uuid' => $entity->coa_sales_uuid,
                'coa_inventory_uuid' => $entity->coa_inventory_uuid,
                'coa_sales_discount_uuid' => $entity->coa_sales_discount_uuid,
                'coa_hpp_uuid' => $entity->coa_hpp_uuid,
                'coa_sales_return_uuid' => $entity->coa_sales_return_uuid,
                'coa_purchase_return_uuid' => $entity->coa_purchase_return_uuid,
                'coa_suspend_uuid' => $entity->coa_suspend_uuid,
            ];

            if (! empty($entity->uuid)) {
                $product = Product::query()
                    ->where('uuid', $entity->uuid)
                    ->where('branch_uuid', $entity->branch_uuid)
                    ->where('company_uuid', $entity->company_uuid)
                    ->firstOrFail();
                $product->update($data);
            } else {
                $product = new Product;
                $product->uuid = $entity->uuid ?: (string) Str::uuid();
                $product->branch_uuid = $entity->branch_uuid;
                $product->company_uuid = $entity->company_uuid;
                $product->fill($data);
                $product->save();

                // AUTOMATIC INITIALIZATION: Create zero stock records for all warehouses in the branch
                $warehouses = Location::query()
                    ->where('branch_uuid', $product->branch_uuid)
                    ->where('company_uuid', $product->company_uuid)
                    ->get();

                foreach ($warehouses as $w) {
                    // Check if active stock record already exists to avoid duplicates
                    $exists = ProductLocation::query()
                        ->where('item_uuid', $product->uuid)
                        ->where('location_uuid', $w->uuid)
                        ->exists();

                    if (! $exists) {
                        ProductLocation::query()->create([
                            'uuid' => (string) Str::uuid(),
                            'item_uuid' => $product->uuid,
                            'location_uuid' => $w->uuid,
                            'branch_uuid' => $product->branch_uuid,
                            'company_uuid' => $product->company_uuid,
                            'on_hand_qty' => 0,
                            'available_qty' => 0,
                            'deleted_at' => 0,
                        ]);
                    }
                }
            }

            if (! is_null($entity->images)) {
                ProductImage::query()
                    ->where('item_uuid', $product->uuid)
                    ->where('branch_uuid', $product->branch_uuid)
                    ->where('company_uuid', $product->company_uuid)
                    ->delete();

                foreach ($entity->images as $index => $imagePath) {
                    ProductImage::query()->create([
                        'item_uuid' => $product->uuid,
                        'branch_uuid' => $product->branch_uuid,
                        'company_uuid' => $product->company_uuid,
                        'path' => $imagePath,
                        'sort_order' => $index,
                    ]);
                }
            }

            $this->syncProductRelations($product, $extraData);

            DB::commit();
            $product->load('images');

            return ProductEntity::fromModel($product);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function syncProductRelations(Product $product, array $extraData): void
    {
        $scope = ['item_uuid' => $product->uuid];
        $sync = function (string $modelClass, array $rows, callable $naturalKeys, callable $attributes) use ($scope, $product): void {
            $submittedUuids = collect($rows)->pluck('uuid')->filter()->values()->all();
            $staleQuery = $modelClass::query()->where($scope);
            if ($submittedUuids) {
                $staleQuery->whereNotIn('uuid', $submittedUuids);
            }
            $staleQuery->delete();

            foreach ($rows as $row) {
                $data = [
                    ...$scope,
                    'branch_uuid' => $product->branch_uuid,
                    'company_uuid' => $product->company_uuid,
                    ...$attributes($row),
                ];
                $existing = null;
                if (! empty($row['uuid'])) {
                    $existing = $modelClass::query()->where($scope)->where('uuid', $row['uuid'])->first();
                }
                if (! $existing) {
                    $existing = $modelClass::query()->where($scope)->where($naturalKeys($row))->first();
                }
                if ($existing) {
                    $existing->update($data);
                } else {
                    $modelClass::query()->create(['uuid' => $row['uuid'] ?? (string) Str::uuid(), ...$data]);
                }
            }
        };

        $sync(ProductAccount::class, $extraData['accounts'] ?? [], fn (array $row): array => ['account_role' => $row['account_role']], fn (array $row): array => [
            'account_role' => $row['account_role'],
            'account_uuid' => $row['account_uuid'],
        ]);
        $sync(ProductUnit::class, $extraData['units'] ?? [], fn (array $row): array => ['unit_uuid' => $row['unit_uuid']], fn (array $row): array => [
            'unit_uuid' => $row['unit_uuid'],
            'conversion_ratio' => (float) ($row['conversion_ratio'] ?? 1),
            'is_base' => (bool) ($row['is_default'] ?? false),
            'is_active' => (bool) ($row['is_active'] ?? true),
            'note' => $row['note'] ?? null,
            'is_purchase_unit' => false,
            'is_sales_unit' => false,
        ]);
        $sync(ProductPrice::class, $extraData['prices'] ?? [], fn (array $row): array => ['price_level_uuid' => $row['price_level_uuid']], fn (array $row): array => [
            'price_level_uuid' => $row['price_level_uuid'],
            'price' => (float) ($row['price_sell'] ?? 0),
            'price_buy' => (float) ($row['price_buy'] ?? 0),
            'discount_amount' => (float) ($row['discount_amount'] ?? 0),
            'discount_percent' => (float) ($row['discount_percent'] ?? 0),
            'base_qty' => (float) ($row['base_qty'] ?? 1),
            'is_active' => (bool) ($row['is_active'] ?? true),
        ]);
        $sync(ProductOpeningBalance::class, $extraData['opening_balances'] ?? [], fn (array $row): array => ['location_uuid' => $row['location_uuid'], 'period_month' => $row['period'] ?? now()->format('Y-m')], fn (array $row): array => [
            'location_uuid' => $row['location_uuid'],
            'period_month' => $row['period'] ?? now()->format('Y-m'),
            'opening_qty' => (float) ($row['opening_qty'] ?? 0),
        ]);
        $sync(ProductDiscountRule::class, $extraData['discounts'] ?? [], fn (array $row): array => ['min_qty' => (float) ($row['quantity'] ?? 0), 'unit_uuid' => $row['unit_uuid'] ?? null], fn (array $row): array => [
            'unit_uuid' => $row['unit_uuid'] ?? null,
            'discount_type' => (float) ($row['discount_percent'] ?? 0) > 0 ? 'percentage' : 'amount',
            'min_qty' => (float) ($row['quantity'] ?? 0),
            'base_qty' => (float) ($row['base_qty'] ?? 1),
            'amount' => (float) (($row['discount_percent'] ?? 0) > 0 ? ($row['discount_percent'] ?? 0) : ($row['discount_amount'] ?? 0)),
            'is_active' => true,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasTransactions(string $uuid): bool
    {
        $checks = [
            ['table' => 'stock_movements', 'column' => 'product_uuid'],
            ['table' => 'purchase_items', 'column' => 'product_uuid'],
            ['table' => 'sale_items', 'column' => 'item_uuid'],
        ];

        foreach ($checks as $check) {
            if (Schema::hasTable($check['table'])) {
                if (DB::table($check['table'])->where($check['column'], $uuid)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ProductEntity $entity): void
    {
        Product::query()
            ->where('uuid', trim($entity->uuid))
            ->where('branch_uuid', $entity->branch_uuid)
            ->where('company_uuid', $entity->company_uuid)
            ->firstOrFail()
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getByCode(ProductEntity $entity): ?ProductEntity
    {
        $model = Product::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($entity->code))]);

        if (! empty($entity->branch_uuid)) {
            $model->where('branch_uuid', $entity->branch_uuid);
        }
        if (! empty($entity->company_uuid)) {
            $model->where('company_uuid', $entity->company_uuid);
        }

        $model = $model->first();

        return $model ? ProductEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function listForExport(ProductEntity $entity): array
    {
        $query = Product::query()->orderBy('name', 'ASC');
        (new ProductFilterBuilder)->apply($query, ProductFilter::fromEntity($entity));

        $items = $query->get();

        return $items->map(fn ($m) => ProductEntity::fromModel($m))->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function reduceStock(string $warehouseUuid, string $productUuid, float $qty, string $refType, string $refUuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            $warehouse = Location::query()->where('uuid', $warehouseUuid)->firstOrFail();

            $stock = ProductLocation::query()->where('item_uuid', $productUuid)
                ->where('location_uuid', $warehouseUuid)
                ->first();

            if (! $stock) {
                $stock = new ProductLocation;
                $stock->uuid = (string) Str::uuid();
                $stock->item_uuid = $productUuid;
                $stock->location_uuid = $warehouseUuid;
                $stock->branch_uuid = $warehouse->branch_uuid;
                $stock->company_uuid = $warehouse->company_uuid;
                $stock->on_hand_qty = -$qty;
                $stock->available_qty = -$qty;
            } else {
                $stock->on_hand_qty -= $qty;
                $stock->available_qty -= $qty;
            }
            $stock->save();

            StockMovement::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $warehouse->branch_uuid,
                'company_uuid' => $warehouse->company_uuid,
                'product_uuid' => $productUuid,
                'location_uuid' => $warehouseUuid,
                'type' => 'OUT',
                'reference_type' => $refType,
                'reference_uuid' => $refUuid,
                'qty' => $qty,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getFormOptions(): array
    {
        $lookupOptions = function (string $setCode): array {
            return LookupValue::query()
                ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
                ->where('lookup_sets.code', $setCode)
                ->where('lookup_values.active_flag', 'Y')
                ->orderBy('lookup_values.seq')
                ->get(['lookup_values.uuid', 'lookup_values.display_name'])
                ->map(fn ($row) => ['uuid' => $row->uuid, 'label' => $row->display_name])
                ->all();
        };

        return [
            'units' => Unit::query()->orderBy('name')->get(['uuid', 'name'])
                ->map(fn ($unit) => ['uuid' => $unit->uuid, 'label' => $unit->name])->all(),
            'warehouses' => Location::query()->orderBy('name')->get(['uuid', 'name'])
                ->map(fn ($location) => ['uuid' => $location->uuid, 'label' => $location->name])->all(),
            'priceLevels' => PriceLevel::query()->orderBy('name')->get(['uuid', 'name'])
                ->map(fn ($level) => ['uuid' => $level->uuid, 'label' => $level->name])->all(),
            'accounts' => Account::query()->where('is_header', false)->where('is_active', true)->orderBy('code')->get(['uuid', 'code', 'name'])
                ->map(fn ($account) => ['uuid' => $account->uuid, 'code' => $account->code, 'name' => $account->name, 'label' => $account->code.' - '.$account->name])->all(),
            'kinds' => $lookupOptions('JENIS'),
            'groups' => $lookupOptions('KELOMPOK'),
            'types' => $lookupOptions('TIPE'),
            'brands' => $lookupOptions('MERK'),
            'origins' => $lookupOptions('MADE_IN'),
            'grades' => $lookupOptions('GRADE'),
        ];
    }

    public function getCompanyOptions(): array
    {
        return Company::query()
            ->orderBy('name')
            ->get(['uuid', 'name'])
            ->map(fn ($company) => ['uuid' => $company->uuid, 'name' => $company->name])
            ->all();
    }

    public function getDropdownOptions(string $type, string $search, string $selected, int $limit): array
    {
        $query = match ($type) {
            'company' => Company::query()->select(['uuid', 'name']),
            'unit' => Unit::query()->select(['uuid', 'name']),
            'warehouse' => Location::query()->select(['uuid', 'name']),
            'price_level' => PriceLevel::query()->select(['uuid', 'name']),
            'account' => Account::query()->where('is_header', false)->where('is_active', true)
                ->select(['uuid', 'code', 'name'])
                ->selectRaw("CONCAT(code, ' - ', name) AS label"),
            'user' => User::query()->select(['uuid', 'name']),
            default => LookupValue::query()
                ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
                ->where('lookup_sets.code', strtoupper($type))
                ->where('lookup_values.active_flag', 'Y')
                ->select(['lookup_values.uuid', 'lookup_values.display_name as label']),
        };

        $nameTypes = ['company', 'unit', 'user', 'warehouse', 'price_level'];
        $labelColumn = in_array($type, $nameTypes, true) ? 'name' : 'label';
        $searchColumn = in_array($type, $nameTypes, true) ? 'name' : ($type === 'account' ? 'name' : 'display_name');
        $isLookupType = ! in_array($type, $nameTypes, true) && $type !== 'account';
        $uuidColumn = $isLookupType ? 'lookup_values.uuid' : 'uuid';
        $query->when($search !== '', function ($builder) use ($search, $selected, $searchColumn, $uuidColumn) {
            $builder->where(function ($nested) use ($search, $selected, $searchColumn, $uuidColumn) {
                $nested->where($searchColumn, 'like', "%{$search}%");
                if ($selected !== '') {
                    $nested->orWhere($uuidColumn, $selected);
                }
            });
        });

        $rows = $query->orderBy($labelColumn)->limit($limit)->get();

        // Pastikan item terpilih selalu ikut agar label tetap tampil meski di luar limit.
        if ($selected !== '' && ! $rows->contains('uuid', $selected)) {
            $selectedRow = (clone $query)->where($uuidColumn, $selected)->first();
            if ($selectedRow) {
                $rows->prepend($selectedRow);
            }
        }

        return $rows->map(fn ($row) => $row->toArray())->all();
    }

    public function getDefaultCompanyUuid(): ?string
    {
        return DB::table('items')->orderBy('created_at')->value('company_uuid')
            ?? Company::query()->orderBy('id')->value('uuid');
    }

    public function findWithRelations(string $uuid, string $branchUuid, string $companyUuid, bool $isSuperadmin = false): ?Product
    {
        return Product::query()
            ->with(['baseUnit', 'groupLookup', 'typeLookup', 'brandLookup', 'originLookup', 'gradeLookup', 'accounts.account', 'units', 'prices', 'openingBalances', 'discountRules'])
            ->where('uuid', $uuid)
            ->when(! $isSuperadmin, fn ($query) => $query
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid))
            ->first();
    }
}
