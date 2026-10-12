<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Operational\Master\Exports\UnitExport;
use App\Modules\Operational\Master\Exports\UnitImportTemplateExport;
use App\Shared\Traits\ResolvesKlinikContext;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ApiUnitController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly UnitService $unitService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $entity = UnitEntity::fromArray([
            ...$this->buildFilters($request),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);
        $result = $this->unitService->index($entity);
        $payload = $this->buildPayload($result, $request);

        return response()->json($payload);
    }

    public function show(string $uuid): JsonResponse
    {
        $unit = $this->unitService->getByUuid(new UnitEntity(uuid: $uuid));
        abort_if($unit === null, 404, 'Satuan tidak ditemukan.');

        return response()->json($this->transformItem($unit));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);
        $entity = UnitEntity::fromArray([
            ...$data,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);
        $unit = $this->unitService->save($entity);

        return response()->json($this->transformItem($unit));
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $data = $this->validateRequest($request);
        $entity = UnitEntity::fromArray([
            ...$data,
            'uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);
        $unit = $this->unitService->save($entity);

        return response()->json($this->transformItem($unit));
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $this->unitService->destroy(UnitEntity::fromArray([
            'uuid' => $uuid,
            'branch_uuid' => (string) ($request->user()?->branch_uuid ?? ''),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $filters = $this->buildFilters($request);
        $entity = UnitEntity::fromArray([
            ...$filters,
            'page' => 1,
            'per_page' => 5000,
        ]);
        $result = $this->unitService->index($entity);

        return Excel::download(
            new UnitExport(collect($result['items'] ?? [])),
            'units-export.xlsx'
        );
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $summary = $this->unitService->importRows(
            $rows,
            (string) ($request->user()?->branch_uuid ?? ''),
            $this->resolveCompanyUuid($request),
        );

        if (($summary['failed'] ?? 0) > 0) {
            return response()->json([
                'message' => 'Sebagian data satuan gagal diimpor.',
                'errors' => ['file' => $summary['errors'] ?? []],
            ], 422);
        }

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new UnitImportTemplateExport($this->unitService->getImportTemplate()),
            'unit-import-template.xlsx'
        );
    }

    private function buildPayload(array $result, Request $request): array
    {
        $filters = $this->buildFilters($request);
        $page = (int) ($result['page'] ?? 1);
        $perPage = (int) ($result['per_page'] ?? 10);
        $total = (int) ($result['total'] ?? 0);
        $lastPage = max(1, (int) ceil(max($total, 1) / max($perPage, 1)));
        $from = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to = $total === 0 ? null : min($page * $perPage, $total);

        return [
            'data' => array_map(fn ($item) => $this->transformItem($item), $result['items'] ?? []),
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
            'summary' => ['total' => $total],
            'filters' => $filters,
        ];
    }

    private function buildFilters(Request $request): array
    {
        return [
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'code')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 10),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ];
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function transformItem(UnitEntity $unit): array
    {
        return [
            'uuid' => $unit->uuid,
            'code' => $unit->code,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'description' => $unit->description,
            'is_active' => (bool) $unit->is_active,
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
