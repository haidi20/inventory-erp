<?php

namespace App\Modules\Operational\Sales\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Sales\Entities\ServicePackageEntity;
use App\Modules\Operational\Sales\Services\ServicePackageService;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    public function __construct(
        private ServicePackageService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = ServicePackageEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]);

        return $this->apiResource($this->service->listPackages($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'array',
            'items.*.item_type' => 'required_with:items|in:service,product',
            'items.*.item_uuid' => 'required_with:items|uuid',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ]);

        $entity = ServicePackageEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $package = $this->service->createPackage($entity);

        $this->audit('service_package.create', $package, null, $package->toArray());

        return $this->apiResource($package)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $servicePackage)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $result = $this->service->getPackage($servicePackage, $branchUuid, $companyUuid);

        abort_unless($result, 404);

        return $this->apiResource($result->load('items'));
    }

    public function update(Request $request, string $servicePackage)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getPackage($servicePackage, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->load('items')->toArray();
        $data = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'array',
            'items.*.item_type' => 'required_with:items|in:service,product',
            'items.*.item_uuid' => 'required_with:items|uuid',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ]);

        $entity = ServicePackageEntity::fromArray(array_merge($data, [
            'uuid' => $servicePackage,
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $updated = $this->service->updatePackage($entity);

        $this->audit('service_package.update', $updated, $before, $updated->toArray());

        return $this->apiResource($updated);
    }

    public function destroy(Request $request, string $servicePackage)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getPackage($servicePackage, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->load('items')->toArray();

        $this->service->deletePackage($servicePackage, $branchUuid, $companyUuid);

        $this->audit('service_package.delete', $existing, $before, null);

        return response()->noContent();
    }
}
