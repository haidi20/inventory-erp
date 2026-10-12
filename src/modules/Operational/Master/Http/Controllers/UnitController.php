<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\UnitService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $unitService)
    {
    }

    public function index(Request $request): Response
    {
        $payload = $this->buildPayload($request);

        return Inertia::render('Operational/Master/presentations/views/UnitIndex', [
            'units' => $payload,
            'summary' => ['total' => $payload['total']],
            'filters' => [
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'code')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
            ],
        ]);
    }

    private function buildPayload(Request $request): array
    {
        $result = $this->unitService->index(UnitEntity::fromArray([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'code')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 10),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ]));

        $page = (int) ($result['page'] ?? 1);
        $perPage = (int) ($result['per_page'] ?? 10);
        $total = (int) ($result['total'] ?? 0);
        $lastPage = max(1, (int) ceil(max($total, 1) / max($perPage, 1)));
        $from = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to = $total === 0 ? null : min($page * $perPage, $total);

        return [
            'data' => array_map(fn ($item) => [
                'uuid' => $item->uuid,
                'code' => $item->code,
                'name' => $item->name,
                'symbol' => $item->symbol,
                'description' => $item->description,
                'is_active' => (bool) $item->is_active,
            ], $result['items'] ?? []),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'from' => $from,
            'to' => $to,
            'links' => $this->buildPaginationLinks($page, $lastPage, $request),
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

    private function buildPaginationLinks(int $page, int $lastPage, Request $request): array
    {
        $query = array_filter([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'code')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'per_page' => $request->integer('per_page', 10),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ], fn ($value) => $value !== null && $value !== '');

        $buildUrl = fn (int $targetPage): string => '/masters/units?' . http_build_query([...$query, 'page' => $targetPage]);

        $links = [[
            'url' => $page > 1 ? $buildUrl($page - 1) : null,
            'label' => '&laquo; Previous',
            'active' => false,
        ]];

        for ($cursor = 1; $cursor <= $lastPage; $cursor++) {
            $links[] = [
                'url' => $buildUrl($cursor),
                'label' => (string) $cursor,
                'active' => $cursor === $page,
            ];
        }

        $links[] = [
            'url' => $page < $lastPage ? $buildUrl($page + 1) : null,
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return $links;
    }
}
