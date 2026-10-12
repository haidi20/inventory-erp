<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Operational\Master\Repositories\MasterDataRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class MasterDataService
{
    public function __construct(private MasterDataRepository $masterDataRepository)
    {
    }

    public function patients(PatientEntity $patientEntity): array
    {
        $search = trim((string) ($patientEntity->search ?? ''));
        $gender = trim((string) ($patientEntity->gender ?? ''));

        return array_merge(
            $this->masterDataRepository->getPatientsPageData(PatientEntity::fromArray([
                'search' => $search,
                'gender' => $gender,
            ])),
            compact('search', 'gender')
        );
    }

    public function storePatient(PatientEntity $patientEntity): void
    {
        $data = Validator::make([
            'medical_record_no' => $patientEntity->medical_record_no,
            'name' => $patientEntity->name,
            'gender' => $patientEntity->gender,
            'birth_date' => $patientEntity->birth_date,
            'phone' => $patientEntity->phone,
            'bpjs_number' => $patientEntity->bpjs_number,
        ], [
            'medical_record_no' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bpjs_number' => ['nullable', 'string', 'max:30'],
        ])->validate();

        if ($this->masterDataRepository->medicalRecordNoExists($data['medical_record_no'])) {
            throw ValidationException::withMessages(['medical_record_no' => ['The medical record no has already been taken.']]);
        }

        $data['is_active'] = true;

        $this->masterDataRepository->createPatient(PatientEntity::fromArray($data));
    }

    public function suppliers(SupplierEntity $supplierEntity): array
    {
        $search = trim((string) ($supplierEntity->search ?? ''));

        return array_merge(
            $this->masterDataRepository->getSuppliersPageData(SupplierEntity::fromArray([
                'search' => $search,
            ])),
            compact('search')
        );
    }

    public function storeSupplier(SupplierEntity $supplierEntity): void
    {
        $data = Validator::make([
            'name' => $supplierEntity->name,
            'phone' => $supplierEntity->phone,
            'email' => $supplierEntity->email,
            'address' => $supplierEntity->address,
            'term_days' => $supplierEntity->term_days,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'term_days' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        if ($this->masterDataRepository->productCodeExists(ProductEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['is_active'] = true;

        $this->masterDataRepository->createSupplier(SupplierEntity::fromArray($data));
    }

    public function updateSupplier(SupplierEntity $supplierEntity): void
    {
        $data = Validator::make([
            'name' => $supplierEntity->name,
            'phone' => $supplierEntity->phone,
            'email' => $supplierEntity->email,
            'address' => $supplierEntity->address,
            'term_days' => $supplierEntity->term_days,
            'is_active' => $supplierEntity->is_active,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        $data['uuid'] = $supplierEntity->uuid;
        $data['is_active'] = $supplierEntity->is_active ?? true;

        $this->masterDataRepository->updateSupplier(SupplierEntity::fromArray($data));
    }

    public function destroySupplier(SupplierEntity $supplierEntity): array
    {
        if ($this->masterDataRepository->supplierHasPurchases($supplierEntity)) {
            return [
                'status' => 'error',
                'message' => 'Supplier sudah memiliki transaksi, tidak bisa dihapus.',
            ];
        }

        $this->masterDataRepository->deleteSupplier($supplierEntity);

        return [
            'status' => 'success',
            'message' => 'Supplier dihapus.',
        ];
    }

    public function products(ProductEntity $productEntity): array
    {
        $search = trim((string) ($productEntity->search ?? ''));
        $category = trim((string) ($productEntity->filter_category ?? ''));

        return array_merge(
            $this->masterDataRepository->getProductsPageData(ProductEntity::fromArray([
                'search' => $search,
                'filter_category' => $category,
            ])),
            compact('search', 'category')
        );
    }

    public function storeProduct(ProductEntity $productEntity): void
    {
        $data = Validator::make([
            'code' => $productEntity->code,
            'name' => $productEntity->name,
            'category' => $productEntity->category,
            'unit' => $productEntity->unit,
            'price_buy' => $productEntity->price_buy,
            'price_sell' => $productEntity->price_sell,
            'min_stock' => $productEntity->min_stock,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_buy' => ['nullable', 'numeric', 'min:0'],
            'price_sell' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        $data['is_active'] = true;

        $this->masterDataRepository->createProduct(ProductEntity::fromArray($data));
    }

    public function updateProduct(ProductEntity $productEntity): void
    {
        $data = Validator::make([
            'code' => $productEntity->code,
            'name' => $productEntity->name,
            'category' => $productEntity->category,
            'unit' => $productEntity->unit,
            'price_buy' => $productEntity->price_buy,
            'price_sell' => $productEntity->price_sell,
            'min_stock' => $productEntity->min_stock,
            'is_active' => $productEntity->is_active,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_buy' => ['nullable', 'numeric', 'min:0'],
            'price_sell' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        if ($this->masterDataRepository->productCodeExists(ProductEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['uuid'] = $productEntity->uuid;
        $data['is_active'] = $productEntity->is_active ?? true;

        $this->masterDataRepository->updateProduct(ProductEntity::fromArray($data));
    }

    public function destroyProduct(ProductEntity $productEntity): array
    {
        if ($this->masterDataRepository->productHasTransactions($productEntity)) {
            return [
                'status' => 'error',
                'message' => 'Produk sudah digunakan dalam transaksi, tidak bisa dihapus.',
            ];
        }

        $this->masterDataRepository->deleteProduct($productEntity);

        return [
            'status' => 'success',
            'message' => 'Produk dihapus.',
        ];
    }

    public function services(TreatmentEntity $treatmentEntity): array
    {
        $search = trim((string) ($treatmentEntity->search ?? ''));

        return array_merge(
            $this->masterDataRepository->getServicesPageData(TreatmentEntity::fromArray([
                'search' => $search,
            ])),
            compact('search')
        );
    }

    public function storeService(TreatmentEntity $treatmentEntity): void
    {
        $data = Validator::make([
            'code' => $treatmentEntity->code,
            'name' => $treatmentEntity->name,
            'duration_minutes' => $treatmentEntity->duration_minutes,
            'price' => $treatmentEntity->price,
            'doctor_fee' => $treatmentEntity->doctor_fee,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'doctor_fee' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        if ($this->masterDataRepository->serviceCodeExists(TreatmentEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['is_active'] = true;

        $this->masterDataRepository->createService(TreatmentEntity::fromArray($data));
    }

    public function updateService(TreatmentEntity $treatmentEntity): void
    {
        $data = Validator::make([
            'code' => $treatmentEntity->code,
            'name' => $treatmentEntity->name,
            'duration_minutes' => $treatmentEntity->duration_minutes,
            'price' => $treatmentEntity->price,
            'doctor_fee' => $treatmentEntity->doctor_fee,
            'is_active' => $treatmentEntity->is_active,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'doctor_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        if ($this->masterDataRepository->serviceCodeExists(TreatmentEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['uuid'] = $treatmentEntity->uuid;
        $data['is_active'] = $treatmentEntity->is_active ?? true;

        $this->masterDataRepository->updateService(TreatmentEntity::fromArray($data));
    }

    public function destroyService(TreatmentEntity $treatmentEntity): void
    {
        $this->masterDataRepository->deleteService($treatmentEntity);
    }

    public function packages(TreatmentPackageEntity $treatmentPackageEntity): array
    {
        $search = trim((string) ($treatmentPackageEntity->search ?? ''));

        return array_merge(
            $this->masterDataRepository->getPackagesPageData(TreatmentPackageEntity::fromArray([
                'search' => $search,
            ])),
            compact('search')
        );
    }

    public function storePackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        $data = Validator::make([
            'code' => $treatmentPackageEntity->code,
            'name' => $treatmentPackageEntity->name,
            'price' => $treatmentPackageEntity->price,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        if ($this->masterDataRepository->packageCodeExists(TreatmentPackageEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['is_active'] = true;

        $this->masterDataRepository->createPackage(TreatmentPackageEntity::fromArray($data));
    }

    public function updatePackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        $data = Validator::make([
            'code' => $treatmentPackageEntity->code,
            'name' => $treatmentPackageEntity->name,
            'price' => $treatmentPackageEntity->price,
            'is_active' => $treatmentPackageEntity->is_active,
        ], [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        if ($this->masterDataRepository->packageCodeExists(TreatmentPackageEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['uuid'] = $treatmentPackageEntity->uuid;
        $data['is_active'] = $treatmentPackageEntity->is_active ?? true;

        $this->masterDataRepository->updatePackage(TreatmentPackageEntity::fromArray($data));
    }

    public function destroyPackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        $this->masterDataRepository->deletePackage($treatmentPackageEntity);
    }

    public function employees(EmployeeEntity $employeeEntity): array
    {
        $search = trim((string) ($employeeEntity->search ?? ''));
        $role = trim((string) ($employeeEntity->filter_role ?? ''));

        return array_merge(
            $this->masterDataRepository->getEmployeesPageData(EmployeeEntity::fromArray([
                'search' => $search,
                'filter_role' => $role,
            ])),
            compact('search', 'role')
        );
    }

    public function storeEmployee(EmployeeEntity $employeeEntity): void
    {
        $data = Validator::make([
            'name' => $employeeEntity->name,
            'role' => $employeeEntity->role,
            'specialization' => $employeeEntity->specialization,
            'fee_default' => $employeeEntity->fee_default,
            'sip_number' => $employeeEntity->sip_number,
            'str_number' => $employeeEntity->str_number,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'fee_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sip_number' => ['nullable', 'string', 'max:100'],
            'str_number' => ['nullable', 'string', 'max:100'],
        ])->validate();

        if ($this->masterDataRepository->accountCodeExists(AccountEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['is_active'] = true;

        $this->masterDataRepository->createEmployee(EmployeeEntity::fromArray($data));
    }

    public function updateEmployee(EmployeeEntity $employeeEntity): void
    {
        $data = Validator::make([
            'name' => $employeeEntity->name,
            'role' => $employeeEntity->role,
            'specialization' => $employeeEntity->specialization,
            'fee_default' => $employeeEntity->fee_default,
            'sip_number' => $employeeEntity->sip_number,
            'str_number' => $employeeEntity->str_number,
            'is_active' => $employeeEntity->is_active,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'fee_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sip_number' => ['nullable', 'string', 'max:100'],
            'str_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ])->validate();

        if ($this->masterDataRepository->accountCodeExists(AccountEntity::fromArray($data))) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        $data['uuid'] = $employeeEntity->uuid;
        $data['is_active'] = $employeeEntity->is_active ?? true;

        $this->masterDataRepository->updateEmployee(EmployeeEntity::fromArray($data));
    }

    public function destroyEmployee(EmployeeEntity $employeeEntity): array
    {
        if ($this->masterDataRepository->employeeHasUser($employeeEntity)) {
            return [
                'status' => 'error',
                'message' => 'Pegawai masih terhubung ke akun user.',
            ];
        }

        $this->masterDataRepository->deleteEmployee($employeeEntity);

        return [
            'status' => 'success',
            'message' => 'Pegawai dihapus.',
        ];
    }

    public function storeAccount(AccountEntity $accountEntity): void
    {
        $data = Validator::make([
            'code' => $accountEntity->code,
            'name' => $accountEntity->name,
            'type' => $accountEntity->type,
            'type_saldo' => $accountEntity->type_saldo,
            'is_header' => $accountEntity->is_header,
        ], [
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'type_saldo' => ['required', 'in:debit,credit'],
            'is_header' => ['nullable'],
        ])->validate();

        $this->masterDataRepository->createAccount(AccountEntity::fromArray([
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'type_saldo' => $data['type_saldo'],
            'is_header' => $accountEntity->is_header ?? false,
            'is_active' => true,
        ]));
    }

    public function updateAccount(AccountEntity $accountEntity): void
    {
        $data = Validator::make([
            'code' => $accountEntity->code,
            'name' => $accountEntity->name,
            'type' => $accountEntity->type,
            'type_saldo' => $accountEntity->type_saldo,
            'is_header' => $accountEntity->is_header,
        ], [
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'type_saldo' => ['required', 'in:debit,credit'],
            'is_header' => ['nullable'],
        ])->validate();

        $this->masterDataRepository->updateAccount(AccountEntity::fromArray([
            'uuid' => $accountEntity->uuid,
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'type_saldo' => $data['type_saldo'],
            'is_header' => $accountEntity->is_header ?? false,
        ]));
    }
}
