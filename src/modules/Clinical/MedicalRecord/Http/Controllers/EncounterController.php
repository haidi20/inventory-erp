<?php

namespace App\Modules\Clinical\MedicalRecord\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\MedicalRecord\Entities\EncounterEntity;
use App\Modules\Clinical\MedicalRecord\Services\EncounterService;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function __construct(
        private EncounterService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = EncounterEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]);

        return $this->apiResource($this->service->listEncounters($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'registration_uuid' => 'required|uuid',
            'patient_uuid' => 'required|uuid',
            'doctor_uuid' => 'nullable|uuid',
            'complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:20',
            'items' => 'array',
            'items.*.item_type' => 'required_with:items|in:service,product',
            'items.*.item_uuid' => 'required_with:items|uuid',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
        ]);

        $entity = EncounterEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $encounter = $this->service->createEncounter($entity);

        $this->audit('encounter.create', $encounter, null, $encounter->toArray());

        return $this->apiResource($encounter)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $encounter)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $result = $this->service->getEncounter($encounter, $branchUuid, $companyUuid);

        abort_unless($result, 404);

        return $this->apiResource($result);
    }

    public function update(Request $request, string $encounter)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $existing = $this->service->getEncounter($encounter, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();

        $data = $request->validate([
            'registration_uuid' => 'sometimes|uuid',
            'patient_uuid' => 'sometimes|uuid',
            'doctor_uuid' => 'nullable|uuid',
            'complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:20',
            'items' => 'array',
            'items.*.item_type' => 'required_with:items|in:service,product',
            'items.*.item_uuid' => 'required_with:items|uuid',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
        ]);

        $entity = EncounterEntity::fromArray(array_merge($data, [
            'uuid' => $encounter,
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $updated = $this->service->updateEncounter($entity);

        $this->audit('encounter.update', $updated, $before, $updated->toArray());

        return $this->apiResource($updated);
    }

    public function destroy(Request $request, string $encounter)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getEncounter($encounter, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();

        $this->service->deleteEncounter($encounter, $branchUuid, $companyUuid);

        $this->audit('encounter.delete', $existing, $before, null);

        return response()->noContent();
    }
}
