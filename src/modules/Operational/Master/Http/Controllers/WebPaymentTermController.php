<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\PaymentTermService;
use App\Shared\Traits\ResolvesKlinikContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebPaymentTermController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly PaymentTermService $paymentTermService) {}

    public function index(Request $request)
    {
        $entity = PaymentTermEntity::fromArray([
            'search'         => (string) $request->string('search')->trim(),
            'search_column'  => (string) $request->string('search_column', 'all')->trim(),
            'sort_by'        => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page'           => $request->integer('page', 1),
            'per_page'       => $request->integer('per_page', 15),
            'branch_uuid'    => $request->user()?->branch_uuid,
            'company_uuid'   => $this->resolveCompanyUuid($request),
        ]);

        $result = $this->paymentTermService->index($entity);

        return Inertia::render('Operational/Master/presentations/views/PaymentTermIndex', [
            'payment_terms' => [
                'data'  => array_map(fn($t) => $t->toArray(), $result['items'] ?? []),
                'total' => $result['total'] ?? 0,
            ],
            'filters' => [
                'search'         => (string) $request->string('search')->trim(),
                'search_column'  => (string) $request->string('search_column', 'all')->trim(),
                'sort_by'        => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page'           => $request->integer('page', 1),
                'per_page'       => $request->integer('per_page', 15),
            ],
        ]);
    }
}
