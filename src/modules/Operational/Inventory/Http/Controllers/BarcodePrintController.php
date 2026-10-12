<?php

namespace App\Modules\Operational\Inventory\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Models\Product;
use App\Shared\Traits\ResolvesKlinikContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BarcodePrintController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $companyUuid = $this->resolveCompanyUuid($request);

        return Inertia::render('Operational/Inventory/presentations/views/BarcodePrintIndex', [
            'filters' => [
                'search'   => (string) $request->string('search')->trim(),
                'company_uuid' => $companyUuid,
                'branch_uuid' => $this->resolveBranchUuid($request),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
            'items' => Product::query()
                ->where('company_uuid', $companyUuid)
                ->where('is_active', true)
                ->whereNotNull('code')
                ->where('code', '!=', '')
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product): array => [
                    'code' => $product->code,
                    'name' => $product->name,
                    'barcode' => $product->barcode ?: $product->code,
                    'unit_name' => $product->unit ?: '-',
                ])
                ->all(),
        ]);
    }
}
