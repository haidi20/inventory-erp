<?php

namespace App\Modules\Clinical\Registration\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Registration\Entities\RegistrationEntity;
use App\Modules\Clinical\Registration\Services\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = RegistrationEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]);

        return $this->apiResource($this->service->listRegistrations($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'queue_no' => 'nullable|string|max:20',
            'patient_uuid' => 'required|uuid',
            'doctor_uuid' => 'nullable|uuid',
            'status' => 'nullable|string|max:20',
            'registered_at' => 'nullable|date',
        ]);

        $entity = RegistrationEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $registration = $this->service->createRegistration($entity);

        $this->audit('registration.create', $registration, null, $registration->toArray());

        return $this->apiResource($registration)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $registration)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $result = $this->service->getRegistration($registration, $branchUuid, $companyUuid);

        abort_unless($result, 404);

        return $this->apiResource($result);
    }

    public function update(Request $request, string $registration)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getRegistration($registration, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();

        $data = $request->validate([
            'queue_no' => 'nullable|string|max:20',
            'patient_uuid' => 'sometimes|uuid',
            'doctor_uuid' => 'nullable|uuid',
            'status' => 'nullable|string|max:20',
            'registered_at' => 'nullable|date',
        ]);

        $entity = RegistrationEntity::fromArray(array_merge($data, [
            'uuid' => $registration,
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $updated = $this->service->updateRegistration($entity);

        $this->audit('registration.update', $updated, $before, $updated->toArray());

        return $this->apiResource($updated);
    }

    public function destroy(Request $request, string $registration)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getRegistration($registration, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();

        $this->service->deleteRegistration($registration, $branchUuid, $companyUuid);

        $this->audit('registration.delete', $existing, $before, null);

        return response()->noContent();
    }
}
