<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Shared\Traits\ResolvesKlinikContext;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class LocationController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly LocationService $locationService)
    {
    }

    public function index(Request $request)
    {
        $entity = LocationEntity::fromArray([
            'search'         => (string) $request->string('search')->trim(),
            'search_column'  => (string) $request->string('search_column', 'all')->trim(),
            'sort_by'        => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page'           => $request->integer('page', 1),
            'per_page'       => $request->integer('per_page', 15),
            'branch_uuid'    => $request->user()?->branch_uuid ?? '',
            'company_uuid'   => $this->resolveCompanyUuid($request),
        ]);

        $result = $this->locationService->index($entity);

        return Inertia::render('Operational/Master/presentations/views/WarehouseIndex', [
            'filters' => [
                'search'        => $entity->search,
                'search_column' => $entity->search_column,
                'branch_uuid'   => $entity->branch_uuid,
                'per_page'      => $entity->per_page,
                'page'          => $entity->page,
            ],
            'pagination' => [
                'total'        => $result['total'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
            ],
            'branches'               => $result['branches'],
            'active_locations_count' => $result['active_locations'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'can_sell'    => ['nullable', 'boolean'],
            'is_primary'  => ['nullable', 'boolean'],
        ]);

        $entity = LocationEntity::fromArray([
            ...$data,
            'branch_uuid'  => (string) ($request->user()?->branch_uuid ?? ''),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);

        $this->locationService->save($entity);

        return redirect()->back()->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function update(Request $request, string $uuid)
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'can_sell'    => ['nullable', 'boolean'],
            'is_primary'  => ['nullable', 'boolean'],
        ]);

        $entity = LocationEntity::fromArray([
            ...$data,
            'uuid'         => $uuid,
            'branch_uuid'  => (string) ($request->user()?->branch_uuid ?? ''),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);

        $this->locationService->save($entity);

        return redirect()->back()->with('success', 'Gudang berhasil diperbarui.');
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

    public function destroy(Request $request, string $uuid)
    {
        $entity = new LocationEntity(
            uuid:         $uuid,
            branch_uuid:  (string) ($request->user()?->branch_uuid ?? ''),
            company_uuid: $this->resolveCompanyUuid($request),
        );

        $result = $this->locationService->destroy($entity);

        if (is_array($result) && ($result['status'] ?? '') === 'error') {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', is_array($result) ? ($result['message'] ?? 'Gudang berhasil dihapus.') : 'Gudang berhasil dihapus.');
    }
}
