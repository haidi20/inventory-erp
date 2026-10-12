<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use App\Modules\Operational\Master\Support\ProductFilter;
use App\Modules\Operational\Master\Support\ProductFilterBuilder;
use Illuminate\Support\LazyCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportService
{
    private const HEADERS = [
        'code', 'name', 'barcode', 'category', 'unit', 'price_buy',
        'price_sell', 'min_stock', 'max_stock', 'max_order', 'is_active',
    ];

    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function stream(ProductEntity $entity): StreamedResponse
    {
        return response()->streamDownload(function () use ($entity): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, self::HEADERS);

            foreach ($this->lazyExport($entity) as $product) {
                fputcsv($output, [
                    $product->code,
                    $product->name,
                    $product->barcode,
                    $product->category,
                    $product->unit_name ?? $product->unit,
                    $product->base_price_buy ?? $product->price_buy,
                    $product->base_price_sell ?? $product->price_sell,
                    $product->min_stock,
                    $product->max_stock,
                    $product->max_order,
                    $product->is_active ? 1 : 0,
                ]);
            }

            fclose($output);
        }, 'products-export.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function lazyExport(ProductEntity $entity): LazyCollection
    {
        $query = $this->repository->query();
        (new ProductFilterBuilder)->apply($query, ProductFilter::fromEntity($entity));

        return $query->orderBy('code')->lazyById(500, 'uuid');
    }
}
