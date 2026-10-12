<?php

namespace App\Modules\Clinical\Registration\Services;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Repositories\PatientRepository;
use Illuminate\Support\Facades\Validator;

class PatientService
{
    public function __construct(private PatientRepository $patientRepository)
    {
    }

    public function index(PatientEntity $patientEntity): array
    {
        $search = trim((string) ($patientEntity->search ?? ''));
        $gender = trim((string) ($patientEntity->gender ?? ''));

        return array_merge(
            $this->patientRepository->getPageData($patientEntity),
            compact('search', 'gender')
        );
    }

    public function store(PatientEntity $patientEntity): PatientEntity
    {
        Validator::make($patientEntity->toArray(), [
            'medical_record_no' => ['required', 'string', 'max:50', 'unique:patients,medical_record_no'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bpjs_number' => ['nullable', 'string', 'max:30'],
        ])->validate();

        return $this->patientRepository->create($patientEntity);
    }

    public function getPageData(PatientEntity $entity): array
    {
        return $this->patientRepository->getPageData($entity);
    }

    public function save(PatientEntity $entity): PatientEntity
    {
        return $this->patientRepository->save($entity);
    }

    public function findByUuid(PatientEntity $entity): ?PatientEntity
    {
        return $this->patientRepository->getByUuid($entity);
    }

    public function delete(PatientEntity $entity): void
    {
        $this->patientRepository->delete($entity);
    }
}
