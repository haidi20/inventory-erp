<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\TaxService;
use App\Shared\Traits\ResolvesKlinikContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebTaxController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly TaxService $taxService) {}

    public function index(Request $request)
    {
        $entity = TaxEntity::fromArray([
            'search'         => (string) $request->string('search')->trim(),
            'search_column'  => (string) $request->string('search_column', 'all')->trim(),
            'sort_by'        => (string) $request->string('sort_by', 'tax_code')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page'           => $request->integer('page', 1),
            'per_page'       => $request->integer('per_page', 15),
            'branch_uuid'    => $request->user()?->branch_uuid,
            'company_uuid'   => $this->resolveCompanyUuid($request),
        ]);

        $result = $this->taxService->index($entity);

        return Inertia::render('Operational/Master/presentations/views/TaxIndex', [
            'taxes'   => [
                'data'  => array_map(fn($t) => $t->toArray(), $result->all()),
                'total' => $result->count(),
            ],
            'filters' => [
                'search'         => (string) $request->string('search')->trim(),
                'search_column'  => (string) $request->string('search_column', 'all')->trim(),
                'sort_by'        => (string) $request->string('sort_by', 'tax_code')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page'           => $request->integer('page', 1),
                'per_page'       => $request->integer('per_page', 15),
            ],
        ]);
    }
}
