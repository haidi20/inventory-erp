<?php

namespace App\Modules\Clinical\MedicalRecord\Services;

use App\Modules\Clinical\MedicalRecord\Entities\EncounterEntity;
use App\Modules\Clinical\MedicalRecord\Repositories\EncounterRepositoryInterface;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Services\PatientService;
use App\Modules\Clinical\Registration\Services\RegistrationService;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Operational\Master\Services\EmployeeService;
use App\Modules\Operational\Master\Services\MasterPublicService;
use Illuminate\Validation\ValidationException;

class EncounterService
{
    public function __construct(
        protected EncounterRepositoryInterface $repository,
        protected MasterPublicService $masterPublicService,
        protected RegistrationService $registrationService,
        protected PatientService $patientService,
        protected EmployeeService $employeeService
    ) {}

    public function listEncounters(EncounterEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createEncounter(EncounterEntity $entity): EncounterEntity
    {
        $this->validateReferences($entity);
        $this->ensureProductItemsValid($entity);

        return $this->repository->save($entity);
    }

    public function getEncounter(string $uuid, string $branchUuid, string $companyUuid): ?EncounterEntity
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    public function updateEncounter(EncounterEntity $entity): EncounterEntity
    {
        $this->validateReferences($entity);
        $this->ensureProductItemsValid($entity);

        return $this->repository->update($entity);
    }

    public function validateReferences(EncounterEntity $entity): void
    {
        $errors = [];

        if ($entity->registration_uuid && !$this->registrationService->getRegistration(
            $entity->registration_uuid,
            $entity->branch_uuid,
            $entity->company_uuid
        )) {
            $errors['registration_uuid'] = ['The selected registration uuid is invalid.'];
        }

        if ($entity->patient_uuid && !$this->patientService->findByUuid(new PatientEntity(
            uuid: $entity->patient_uuid,
            branch_uuid: $entity->branch_uuid,
            company_uuid: $entity->company_uuid
        ))) {
            $errors['patient_uuid'] = ['The selected patient uuid is invalid.'];
        }

        if ($entity->doctor_uuid && !$this->employeeService->findByUuid(new EmployeeEntity(
            uuid: $entity->doctor_uuid,
            branch_uuid: $entity->branch_uuid,
            company_uuid: $entity->company_uuid
        ))) {
            $errors['doctor_uuid'] = ['The selected doctor uuid is invalid.'];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function deleteEncounter(string $uuid, string $branchUuid, string $companyUuid): void
    {
        $this->repository->delete($uuid, $branchUuid, $companyUuid);
    }

    protected function ensureProductItemsValid(EncounterEntity $entity): void
    {
        foreach ($entity->items as $item) {
            if (($item['item_type'] ?? null) !== 'product') {
                continue;
            }

            $this->masterPublicService->validateProductForClinicalTransaction($item['item_uuid']);
        }
    }
}
