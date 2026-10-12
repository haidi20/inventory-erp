<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Entities\BarcodePrintFilterEntity;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Support\Collection;

class BarcodePrintRepository
{
    public function getItems(BarcodePrintFilterEntity $filterEntity): Collection
    {
        $query = Product::query()
            ->where('is_active', true)
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->orderBy('name');

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

        return $query->limit($filterEntity->limit ?? 12)->get()->map(function (Product $product): array {
            return [
                'code' => strtoupper((string) $product->code),
                'name' => $product->name,
                'category' => $product->category ?: '-',
                'unit' => $product->unit ?: '-',
                'price_sell' => (float) ($product->price_sell ?? 0),
                'stock' => (int) ($product->stock ?? 0),
                'status' => $product->is_active ? 'Aktif' : 'Nonaktif',
                'low_stock' => (int) ($product->stock ?? 0) <= (float) ($product->min_stock ?? 0),
            ];
        });
    }
}

