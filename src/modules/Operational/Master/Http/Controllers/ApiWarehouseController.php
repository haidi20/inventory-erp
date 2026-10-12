<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Entities\ActionResponseEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Operational\Master\Services\LocationService;
use Illuminate\Http\Request;
use App\Shared\Traits\ResolvesKlinikContext;

class ApiWarehouseController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly LocationService $locationService) {}

    public function index(Request $request): JsonResponse
    {
        $entity = LocationEntity::fromArray([
            'search'       => (string) $request->string('search')->trim(),
            'search_column'=> (string) $request->string('search_column', 'all')->trim(),
            'sort_by'      => (string) $request->string('sort_by', 'code')->trim(),
            'sort_direction'=> (string) $request->string('sort_direction', 'asc')->trim(),
            'page'         => $request->integer('page', 1),
            'per_page'     => $request->integer('per_page', 15),
            'company_uuid' => $this->resolveCompanyUuid($request),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ]);

        $result = $this->locationService->index($entity);

        return response()->json([
            'data' => array_map(fn($l) => $l->toArray(), $result['items'] ?? []),
            'meta' => [
                'total'        => $result['total'] ?? 0,
                'current_page' => $result['current_page'] ?? 1,
                'per_page'     => $result['per_page'] ?? 15,
            ],
        ]);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $entity = new LocationEntity(
            uuid:         $uuid,
            branch_uuid:  (string) ($request->user()?->branch_uuid ?? ''),
            company_uuid: $this->resolveCompanyUuid($request),
        );

        $location = $this->locationService->getByUuid($entity);

        if (!$location) {
            return response()->json(['message' => 'Gudang tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $location->toArray()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
            'can_sell'    => ['nullable', 'boolean'],
            'is_primary'  => ['nullable', 'boolean'],
        ]);

        $entity = LocationEntity::fromArray([
            ...$data,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);

        $location = $this->locationService->save($entity);

        return response()->json(['data' => $location->toArray()]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
            'can_sell'    => ['nullable', 'boolean'],
            'is_primary'  => ['nullable', 'boolean'],
        ]);

        $entity = LocationEntity::fromArray([
            ...$data,
            'uuid'         => $uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);

        $location = $this->locationService->save($entity);

        return response()->json(['data' => $location->toArray()]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $entity = new LocationEntity(
            uuid:         $uuid,
            branch_uuid:  (string) ($request->user()?->branch_uuid ?? ''),
            company_uuid: $this->resolveCompanyUuid($request),
        );

        $result = $this->locationService->destroy($entity);

        if ($result instanceof ActionResponseEntity) {
            if (!$result->success) {
                return response()->json(['message' => $result->message], 422);
            }
            return response()->json(['message' => $result->message]);
        }
        if (is_array($result) && ($result['status'] ?? '') === 'error') {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message'] ?? 'Berhasil dihapus.']);
    }
}