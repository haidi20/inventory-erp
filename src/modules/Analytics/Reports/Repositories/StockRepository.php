<?php

namespace App\Modules\Analytics\Reports\Repositories;

use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use Illuminate\Support\Collection;

class StockRepository
{
    public function getValueList(StockFilterEntity $filterEntity): Collection
    {
        $query = Product::query()->orderBy('name');

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Product $product): array {
            $stock = (int) $product->stock;
            $priceBuy = (float) ($product->price_buy ?? 0);

            return [
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category ?: '-',
                'unit' => $product->unit ?: '-',
                'stock' => $stock,
                'price_buy' => $priceBuy,
                'stock_value' => $stock * $priceBuy,
            ];
        });
    }

    public function getItemsAndServices(StockFilterEntity $filterEntity): Collection
    {
        $search = $filterEntity->search ?? '';

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function (Product $product): array {
                return [
                    'type' => 'Barang',
                    'code' => $product->code,
                    'name' => $product->name,
                    'category' => $product->category ?: '-',
                    'unit' => $product->unit ?: '-',
                    'price' => (float) ($product->price_sell ?? 0),
                    'status' => $product->is_active ? 'Aktif' : 'Nonaktif',
                ];
            });

        $services = Service::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function (Service $service): array {
                return [
                    'type' => 'Jasa',
                    'code' => $service->code,
                    'name' => $service->name,
                    'category' => 'Layanan',
                    'unit' => '-',
                    'price' => (float) ($service->price ?? 0),
                    'status' => $service->is_active ? 'Aktif' : 'Nonaktif',
                ];
            });

        return $products->concat($services)
            ->sortBy(fn (array $item): string => $item['type'] . '|' . $item['name'])
            ->values();
    }

    public function getFormulaBom(StockFilterEntity $filterEntity): Collection
    {
        $query = ServicePackage::query()
            ->with('items')
            ->orderBy('name');

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (ServicePackage $package): array {
            $componentSummary = $package->items
                ->groupBy('item_type')
                ->map(fn (Collection $items, string $type): string => ucfirst($type) . ': ' . $items->count())
                ->values()
                ->implode(', ');

            return [
                'code' => $package->code,
                'name' => $package->name,
                'items_count' => $package->items->count(),
                'total_qty' => (int) $package->items->sum('qty'),
                'component_summary' => $componentSummary ?: 'Belum ada komponen',
                'status' => $package->is_active ? 'Aktif' : 'Nonaktif',
            ];
        });
    }

    public function getUnits(StockFilterEntity $filterEntity): Collection
    {
        $query = Product::query();

        if (($filterEntity->search ?? '') !== '') {
            $query->where('unit', 'like', "%{$filterEntity->search}%");
        }

        return $query->get()
            ->groupBy(fn (Product $product): string => trim((string) ($product->unit ?: 'Tanpa Satuan')))
            ->map(function (Collection $items, string $unit): array {
                return [
                    'unit' => $unit,
                    'products_count' => $items->count(),
                    'total_stock' => (int) $items->sum('stock'),
                    'stock_value' => (float) $items->sum(fn (Product $product): float => ((int) $product->stock) * (float) ($product->price_buy ?? 0)),
                ];
            })
            ->sortBy('unit')
            ->values();
    }

    public function getOpeningBalance(StockFilterEntity $filterEntity): Collection
    {
        $query = Product::query()
            ->where(function ($builder) {
                $builder->where('stock', '>', 0)
                    ->orWhere('price_buy', '>', 0);
            })
            ->orderBy('name');

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Product $product): array {
            $stock = (int) $product->stock;
            $priceBuy = (float) ($product->price_buy ?? 0);

            return [
                'code' => $product->code,
                'name' => $product->name,
                'unit' => $product->unit ?: '-',
                'opening_qty' => $stock,
                'opening_price' => $priceBuy,
                'opening_value' => $stock * $priceBuy,
            ];
        });
    }

    public function getItemConversions(StockFilterEntity $filterEntity): Collection
    {
        $units = Product::query()
            ->select('unit')
            ->when(($filterEntity->search ?? '') !== '', fn ($query) => $query->where('unit', 'like', "%{$filterEntity->search}%"))
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit');

        return $units->map(function (string $unit): array {
            return [
                'item' => 'Belum ada master konversi',
                'from_unit' => $unit,
                'to_unit' => '-',
                'ratio' => '-',
                'note' => 'Siapkan tabel konversi item jika ingin mendukung multi-unit.',
            ];
        });
    }

    public function getWarehouses(StockFilterEntity $filterEntity): Collection
    {
        $branches = Branch::query()
            ->when(($filterEntity->search ?? '') !== '', fn ($query) => $query->where('name', 'like', "%{$filterEntity->search}%"))
            ->orderBy('name')
            ->get();

        return $branches->map(function (Branch $branch): array {
            $products = Product::query()->where('branch_uuid', $branch->uuid)->get();

            return [
                'warehouse' => $branch->name,
                'code' => $branch->short_name ?: '-',
                'products_count' => $products->count(),
                'total_stock' => (int) $products->sum('stock'),
                'low_stock' => $products->filter(fn (Product $product): bool => $product->isLowStock())->count(),
                'stock_value' => (float) $products->sum(fn (Product $product): float => ((int) $product->stock) * (float) ($product->price_buy ?? 0)),
            ];
        });
    }
}
