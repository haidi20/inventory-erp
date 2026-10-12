<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Entities\PriceListFilterEntity;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use Illuminate\Support\Collection;

class PriceListRepository
{
    public function getProducts(PriceListFilterEntity $filterEntity): Collection
    {
        $query = Product::query()->orderBy('name');

        if (($filterEntity->company_uuid ?? '') !== '') {
            $query->where('company_uuid', $filterEntity->company_uuid);
        }

        if (($filterEntity->branch_uuid ?? '') !== '') {
            $query->where('branch_uuid', $filterEntity->branch_uuid);
        }

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Product $product): array {
            $priceBuy = (float) ($product->price_buy ?? 0);
            $priceSell = (float) ($product->price_sell ?? 0);

            return [
                'code' => $product->code ?: '-',
                'name' => $product->name,
                'category' => $product->category ?: '-',
                'unit' => $product->unit ?: '-',
                'price_buy' => $priceBuy,
                'price_sell' => $priceSell,
                'margin' => $priceSell - $priceBuy,
                'status' => $product->is_active ? 'Aktif' : 'Nonaktif',
            ];
        });
    }

    public function getServices(PriceListFilterEntity $filterEntity): Collection
    {
        $query = Service::query()->orderBy('name');

        if (($filterEntity->company_uuid ?? '') !== '') {
            $query->where('company_uuid', $filterEntity->company_uuid);
        }

        if (($filterEntity->branch_uuid ?? '') !== '') {
            $query->where('branch_uuid', $filterEntity->branch_uuid);
        }

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Service $service): array {
            return [
                'code' => $service->code ?: '-',
                'name' => $service->name,
                'duration' => (int) ($service->duration_minutes ?? 0) . ' menit',
                'price' => (float) ($service->price ?? 0),
                'doctor_fee' => (float) ($service->doctor_fee ?? 0),
                'status' => $service->is_active ? 'Aktif' : 'Nonaktif',
            ];
        });
    }

    public function getPackages(PriceListFilterEntity $filterEntity): Collection
    {
        $query = ServicePackage::query()->withCount('items')->orderBy('name');

        if (($filterEntity->company_uuid ?? '') !== '') {
            $query->where('company_uuid', $filterEntity->company_uuid);
        }

        if (($filterEntity->branch_uuid ?? '') !== '') {
            $query->where('branch_uuid', $filterEntity->branch_uuid);
        }

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (ServicePackage $servicePackage): array {
            return [
                'code' => $servicePackage->code ?: '-',
                'name' => $servicePackage->name,
                'items_count' => (int) ($servicePackage->items_count ?? 0),
                'price' => (float) ($servicePackage->price ?? 0),
                'status' => $servicePackage->is_active ? 'Aktif' : 'Nonaktif',
            ];
        });
    }
}
