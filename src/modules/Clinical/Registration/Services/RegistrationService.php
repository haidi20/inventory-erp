<?php

namespace App\Modules\Clinical\Registration\Services;

use App\Modules\Clinical\Registration\Entities\RegistrationEntity;
use App\Modules\Clinical\Registration\Repositories\RegistrationRepositoryInterface;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Services\PatientService;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Operational\Master\Services\EmployeeService;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(
        protected RegistrationRepositoryInterface $repository,
        protected PatientService $patientService,
        protected EmployeeService $employeeService
    ) {}

    public function listRegistrations(RegistrationEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createRegistration(RegistrationEntity $entity): RegistrationEntity
    {
        $this->validateReferences($entity);
        return $this->repository->save($entity);
    }

    public function getRegistration(string $uuid, string $branchUuid, string $companyUuid): ?RegistrationEntity
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    public function updateRegistration(RegistrationEntity $entity): RegistrationEntity
    {
        $this->validateReferences($entity);
        return $this->repository->update($entity);
    }

    public function validateReferences(RegistrationEntity $entity): void
    {
        $errors = [];

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

    public function deleteRegistration(string $uuid, string $branchUuid, string $companyUuid): void
    {
        $this->repository->delete($uuid, $branchUuid, $companyUuid);
    }
}
