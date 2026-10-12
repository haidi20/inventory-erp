<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Services\LookupSetService;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use Illuminate\Http\Request;
use App\Shared\Traits\ResolvesKlinikContext;
use Inertia\Response;

class WebLookUpController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly LookupSetService $lookupSetService)
    {
    }

    public function index(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/LookupIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function products(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/ProductIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function productPrices(Request $request): Response
    {
        return Inertia::render('Operational/Master/presentations/views/PriceLevelIndex', [
            'filters' => [
                'search'   => (string) $request->string('search')->trim(),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }










    public function productGroups(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/ProductGroupIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function productCategories(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/ProductCategoryIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function brands(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/BrandIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function businessUnits(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/BusinessUnitIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function accountGroups(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/AccountGroupIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function paymentMethods(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/PaymentMethodIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function advanceClassifications(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/AdvanceClassificationIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function fixedAssetCategories(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Operational/Master/presentations/views/FixedAssetCategoryIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function pharmacySettings(Request $request): Response
    {
        $payload = $this->buildSetPayload($request);

        return Inertia::render('Clinical/Pharmacy/presentations/views/PharmacySettingIndex', [
            'lookups' => $payload,
            'summary' => [
                'total' => $payload['total'],
            ],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function values(Request $request, string $uuid): Response
    {
        $set = $this->lookupSetService->showSet(LookupSetEntity::fromArray([
            'uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));
        
        if (!$set) {
            abort(404);
        }

        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 10);

        $result = $this->lookupSetService->indexValue(LookupSetValueEntity::fromArray([
            'lookup_set_uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
            'search' => $request->string('search')->trim(),
            'start' => ($page - 1) * $perPage,
            'length' => $perPage,
        ]));

        return Inertia::render('Operational/Master/presentations/views/LookupValueIndex', [
            'lookupset' => [
                'uuid' => $set->uuid,
                'id' => $set->id,
                'name' => $set->name,
                'program_code' => $set->program_code,
            ],
            'values' => $this->formatPaginatedPayload($result->items, $result->filtered, $page, $perPage),
            'filters' => [
                'search' => $request->string('search')->trim(),
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    private function buildSetPayload(Request $request): array
    {
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, $request->integer('per_page', 10));

        $result = $this->lookupSetService->indexSet(LookupSetEntity::fromArray([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page' => $page,
            'per_page' => $perPage,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return $this->formatPaginatedPayload($result->items, $result->filtered, $page, $perPage);
    }

    private function formatPaginatedPayload(array $items, int $total, int $page, int $perPage): array
    {
        $lastPage = max(1, (int) ceil(max($total, 1) / $perPage));
        $from = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to = $total === 0 ? null : min($page * $perPage, $total);

        return [
            'data' => array_map(function ($item) {
                if ($item instanceof LookupSetValueEntity) {
                    return [
                        'uuid' => $item->uuid,
                        'display_name' => $item->display_name,
                        'program_code' => $item->program_code1,
                        'description' => $item->description,
                        'seq' => $item->seq,
                        'active_flag' => $item->active_flag,
                    ];
                }
                return [
                    'uuid' => $item->uuid,
                    'id' => $item->id,
                    'name' => $item->name,
                    'program_code' => $item->program_code,
                    'description' => $item->description,
                    'user_can_edit_flag' => $item->user_can_edit_flag ?? 'Y',
                ];
            }, $items),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'from' => $from,
            'to' => $to,
            'last_page' => $lastPage,
            'links' => [],
            'meta' => [
                'total' => $total,
                'current_page' => $page,
                'from' => $from,
                'to' => $to,
                'per_page' => $perPage,
                'last_page' => $lastPage,
            ],
        ];
    }
}
