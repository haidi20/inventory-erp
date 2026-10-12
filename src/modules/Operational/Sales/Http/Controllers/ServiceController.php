<?php

namespace App\Modules\Operational\Sales\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Sales\Entities\ServiceEntity;
use App\Modules\Operational\Sales\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = ServiceEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]);

        return $this->apiResource($this->service->listServices($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'duration_minutes' => 'integer|min:0',
            'price' => 'numeric|min:0',
            'doctor_fee' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $entity = ServiceEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $service = $this->service->createService($entity);

        $this->audit('service.create', $service, null, $service->toArray());

        return $this->apiResource($service)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $service)
    {
        $user = $request->user();

        $result = $this->service->getService($service, current_branch_uuid(), current_company_uuid());

        abort_unless($result, 404);

        return $this->apiResource($result);
    }

    public function update(Request $request, string $service)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getService($service, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();
        $data = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'duration_minutes' => 'integer|min:0',
            'price' => 'numeric|min:0',
            'doctor_fee' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $entity = ServiceEntity::fromArray(array_merge($data, [
            'uuid' => $service,
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $updated = $this->service->updateService($entity);

        $this->audit('service.update', $updated, $before, $updated->toArray());

        return $this->apiResource($updated);
    }

    public function destroy(Request $request, string $service)
    {
        $user = $request->user();

        $existing = $this->service->getService($service, current_branch_uuid(), current_company_uuid());
        abort_unless($existing, 404);

        $before = $existing->toArray();

        $this->service->deleteService($service, current_branch_uuid(), current_company_uuid());

        $this->audit('service.delete', $existing, $before, null);

        return response()->noContent();
    }
}
