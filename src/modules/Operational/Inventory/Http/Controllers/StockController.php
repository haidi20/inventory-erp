<?php

namespace App\Modules\Operational\Inventory\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Shared\Traits\ResolvesKlinikContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct()
    {
        parent::__construct();
    }

    public function showValueList(Request $request): Response
    {
        return Inertia::render('Operational/Inventory/presentations/views/StockValueListIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showItemsAndServices(Request $request)
    {
        return redirect('/master/products');
    }

    public function showFormulaBom(Request $request): Response
    {
        $companyUuid = $this->resolveCompanyUuid($request);

        return Inertia::render('Operational/Inventory/presentations/views/BomFormulaIndex', [
            'filters' => $this->buildFilters($request),
            'items' => $this->getFormulaBomItems($companyUuid),
        ]);
    }

    public function showUnitList(Request $request)
    {
        return redirect('/master/units');
    }

    public function showOpeningBalance(Request $request): Response
    {
        $companyUuid = $this->resolveCompanyUuid($request);

        return Inertia::render('Operational/Inventory/presentations/views/OpeningBalanceIndex', [
            'filters' => $this->buildFilters($request),
            'items' => $this->getOpeningBalanceItems($companyUuid),
        ]);
    }

    public function showItemConversion(Request $request)
    {
        return redirect('/inventory?tab=utama');
    }

    public function showWarehouseList(Request $request)
    {
        return redirect('/master/warehouses');
    }

    private function buildFilters(Request $request): array
    {
        return [
            'search'         => (string) $request->string('search')->trim(),
            'company_uuid'   => $this->resolveCompanyUuid($request),
            'location_uuid' => (string) $request->string('location_uuid')->trim(),
            'product_uuid'   => (string) $request->string('product_uuid')->trim(),
            'page'           => $request->integer('page', 1),
            'per_page'       => $request->integer('per_page', 10),
        ];
    }

    private function getFormulaBomItems(string $companyUuid): array
    {
        return ServicePackage::query()
            ->with('items')
            ->where('company_uuid', $companyUuid)
            ->orderBy('name')
            ->get()
            ->map(fn (ServicePackage $package): array => [
                'code' => $package->code,
                'name' => $package->name,
                'base_product' => $package->items->count() . ' komponen',
                'qty' => (int) $package->items->sum('qty'),
                'unit_name' => '-',
            ])
            ->all();
    }

    private function getOpeningBalanceItems(string $companyUuid): array
    {
        return Product::query()
            ->with('stocks.warehouse')
            ->where('company_uuid', $companyUuid)
            ->where(function ($query): void {
                $query->whereHas('stocks', fn ($stocks) => $stocks->where('on_hand_qty', '>', 0))
                    ->orWhere('price_buy', '>', 0);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'code' => $product->code,
                'name' => $product->name,
                'warehouse_name' => $product->stocks->first()?->warehouse?->name ?? '-',
                'opening_qty' => (int) $product->stocks->sum('on_hand_qty'),
                'unit_name' => $product->unit ?: '-',
            ])
            ->all();
    }
}
