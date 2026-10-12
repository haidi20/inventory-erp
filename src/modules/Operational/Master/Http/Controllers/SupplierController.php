<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = SupplierEntity::fromArray(array_merge($request->all(), [
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ]));

        $result = $this->service->getPageData($entity);

        return $this->apiResource(collect(array_map(fn ($item) => $item->toArray(), $result['items'])))
            ->additional([
                'meta' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                ],
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'term_days' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $user = auth()->user();
        $entity = SupplierEntity::fromArray(array_merge($data, [
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid' => $user->uuid,
        ]));

        $supplier = $this->service->save($entity);
        $this->audit('supplier.create', null, null, $supplier->toArray());

        return $this->apiResource($supplier->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $supplier = $this->service->findByUuid(new SupplierEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$supplier) {
            abort(404, 'Supplier not found');
        }

        return $this->apiResource($supplier->toArray());
    }

    public function update(Request $request, string $uuid)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'term_days' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $user = auth()->user();
        $existing = $this->service->findByUuid(new SupplierEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$existing) {
            abort(404, 'Supplier not found');
        }

        $updated = $this->service->save(SupplierEntity::fromArray(array_merge(
            $existing->toArray(),
            $data,
            ['uuid' => $uuid, 'actor_uuid' => $user->uuid]
        )));

        $this->audit('supplier.update', null, $existing->toArray(), $updated->toArray());

        return $this->apiResource($updated->toArray());
    }

    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $existing = $this->service->findByUuid(new SupplierEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if ($existing) {
            $this->service->delete($existing);
            $this->audit('supplier.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
