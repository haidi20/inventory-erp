<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\Supplier;

class MasterDataRepository
{
    public function medicalRecordNoExists(string $medicalRecordNo, ?string $ignoreUuid = null): bool
    {
        return Patient::query()
            ->where('medical_record_no', $medicalRecordNo)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    public function productCodeExists(ProductEntity $entity): bool
    {
        return Product::query()
            ->where('code', $entity->code)
            ->when($entity->uuid, fn ($query) => $query->where('uuid', '!=', $entity->uuid))
            ->exists();
    }

    public function serviceCodeExists(TreatmentEntity $entity): bool
    {
        return Service::query()
            ->where('code', $entity->code)
            ->where('deleted_at', 0)
            ->when($entity->uuid, fn ($query) => $query->where('uuid', '!=', $entity->uuid))
            ->exists();
    }

    public function packageCodeExists(TreatmentPackageEntity $entity): bool
    {
        return ServicePackage::query()
            ->where('code', $entity->code)
            ->when($entity->uuid, fn ($query) => $query->where('uuid', '!=', $entity->uuid))
            ->exists();
    }

    public function accountCodeExists(AccountEntity $entity): bool
    {
        return Account::query()
            ->where('code', $entity->code)
            ->when($entity->uuid, fn ($query) => $query->where('uuid', '!=', $entity->uuid))
            ->exists();
    }

    public function getPatientsPageData(PatientEntity $patientEntity): array
    {
        $query = Patient::query()->orderBy('name');

        if (($patientEntity->search ?? '') !== '') {
            $search = $patientEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('medical_record_no', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('bpjs_number', 'LIKE', "%{$search}%");
            });
        }

        if (($patientEntity->gender ?? '') !== '') {
            $query->where('gender', $patientEntity->gender);
        }

        return [
            'items' => $query->paginate(20)->withQueryString(),
            'totalPatients' => Patient::query()->count(),
            'activePatients' => Patient::query()->where('is_active', true)->count(),
            'insuredPatients' => Patient::query()->whereNotNull('bpjs_number')->where('bpjs_number', '!=', '')->count(),
        ];
    }

    public function createPatient(PatientEntity $patientEntity): void
    {
        DB::beginTransaction();
        try {
            Patient::query()->create([
                'medical_record_no' => $patientEntity->medical_record_no,
                'name' => $patientEntity->name,
                'gender' => $patientEntity->gender,
                'birth_date' => $patientEntity->birth_date,
                'phone' => $patientEntity->phone,
                'bpjs_number' => $patientEntity->bpjs_number,
                'is_active' => $patientEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSuppliersPageData(SupplierEntity $supplierEntity): array
    {
        $query = Supplier::query()->orderBy('name');

        if (($supplierEntity->search ?? '') !== '') {
            $search = $supplierEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        return [
            'items' => $query->paginate(20)->withQueryString(),
            'activeSuppliers' => Supplier::query()->where('is_active', true)->count(),
            'inactiveSuppliers' => Supplier::query()->where('is_active', false)->count(),
        ];
    }

    public function createSupplier(SupplierEntity $supplierEntity): void
    {
        DB::beginTransaction();
        try {
            Supplier::query()->create([
                'name' => $supplierEntity->name,
                'phone' => $supplierEntity->phone,
                'email' => $supplierEntity->email,
                'address' => $supplierEntity->address,
                'term_days' => $supplierEntity->term_days,
                'is_active' => $supplierEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSupplier(SupplierEntity $supplierEntity): void
    {
        DB::beginTransaction();
        try {
            Supplier::query()->findOrFail($supplierEntity->uuid)->update([
                'name' => $supplierEntity->name,
                'phone' => $supplierEntity->phone,
                'email' => $supplierEntity->email,
                'address' => $supplierEntity->address,
                'term_days' => $supplierEntity->term_days,
                'is_active' => $supplierEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function supplierHasPurchases(SupplierEntity $supplierEntity): bool
    {
        return Supplier::query()->findOrFail($supplierEntity->uuid)->purchases()->exists();
    }

    public function deleteSupplier(SupplierEntity $supplierEntity): void
    {
        DB::beginTransaction();
        try {
            Supplier::query()->findOrFail($supplierEntity->uuid)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getProductsPageData(ProductEntity $productEntity): array
    {
        $query = Product::query()->orderBy('name');

        if (($productEntity->search ?? '') !== '') {
            $search = $productEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        if (($productEntity->filter_category ?? '') !== '') {
            $query->where('category', $productEntity->filter_category);
        }

        return [
            'items' => $query->paginate(25)->withQueryString(),
            'categories' => Product::query()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category'),
            'activeProducts' => Product::query()->where('is_active', true)->count(),
            'lowStockCount' => 0,
            'stockValueTotal' => 0,
        ];
    }

    public function createProduct(ProductEntity $productEntity): void
    {
        DB::beginTransaction();
        try {
            Product::query()->create([
                'code' => $productEntity->code,
                'name' => $productEntity->name,
                'category' => $productEntity->category,
                'unit' => $productEntity->unit,
                'price_buy' => $productEntity->price_buy,
                'price_sell' => $productEntity->price_sell,
                'min_stock' => $productEntity->min_stock,
                'is_active' => $productEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProduct(ProductEntity $productEntity): void
    {
        DB::beginTransaction();
        try {
            Product::query()->findOrFail($productEntity->uuid)->update([
                'code' => $productEntity->code,
                'name' => $productEntity->name,
                'category' => $productEntity->category,
                'unit' => $productEntity->unit,
                'price_buy' => $productEntity->price_buy,
                'price_sell' => $productEntity->price_sell,
                'min_stock' => $productEntity->min_stock,
                'is_active' => $productEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function productHasTransactions(ProductEntity $productEntity): bool
    {
        $product = Product::query()->findOrFail($productEntity->uuid);

        return $product->purchaseItems()->exists() || $product->dispensingItems()->exists();
    }

    public function deleteProduct(ProductEntity $productEntity): void
    {
        DB::beginTransaction();
        try {
            Product::query()->findOrFail($productEntity->uuid)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getServicesPageData(TreatmentEntity $treatmentEntity): array
    {
        $query = Service::query()->orderBy('name');

        if (($treatmentEntity->search ?? '') !== '') {
            $search = $treatmentEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        return [
            'items' => $query->paginate(20)->withQueryString(),
            'activeServices' => Service::query()->where('is_active', true)->count(),
            'totalAll' => Service::query()->count(),
        ];
    }

    public function createService(TreatmentEntity $treatmentEntity): void
    {
        DB::beginTransaction();
        try {
            Service::query()->create([
                'code' => $treatmentEntity->code,
                'name' => $treatmentEntity->name,
                'duration_minutes' => $treatmentEntity->duration_minutes,
                'price' => $treatmentEntity->price,
                'doctor_fee' => $treatmentEntity->doctor_fee,
                'is_active' => $treatmentEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateService(TreatmentEntity $treatmentEntity): void
    {
        DB::beginTransaction();
        try {
            Service::query()->findOrFail($treatmentEntity->uuid)->update([
                'code' => $treatmentEntity->code,
                'name' => $treatmentEntity->name,
                'duration_minutes' => $treatmentEntity->duration_minutes,
                'price' => $treatmentEntity->price,
                'doctor_fee' => $treatmentEntity->doctor_fee,
                'is_active' => $treatmentEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteService(TreatmentEntity $treatmentEntity): void
    {
        DB::beginTransaction();
        try {
            Service::query()->findOrFail($treatmentEntity->uuid)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPackagesPageData(TreatmentPackageEntity $treatmentPackageEntity): array
    {
        $query = ServicePackage::query()->orderBy('name');

        if (($treatmentPackageEntity->search ?? '') !== '') {
            $search = $treatmentPackageEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        return [
            'items' => $query->paginate(20)->withQueryString(),
            'activePackages' => ServicePackage::query()->where('is_active', true)->count(),
            'totalAll' => ServicePackage::query()->count(),
        ];
    }

    public function createPackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        DB::beginTransaction();
        try {
            ServicePackage::query()->create([
                'code' => $treatmentPackageEntity->code,
                'name' => $treatmentPackageEntity->name,
                'price' => $treatmentPackageEntity->price,
                'is_active' => $treatmentPackageEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        DB::beginTransaction();
        try {
            ServicePackage::query()->findOrFail($treatmentPackageEntity->uuid)->update([
                'code' => $treatmentPackageEntity->code,
                'name' => $treatmentPackageEntity->name,
                'price' => $treatmentPackageEntity->price,
                'is_active' => $treatmentPackageEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePackage(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        DB::beginTransaction();
        try {
            ServicePackage::query()->findOrFail($treatmentPackageEntity->uuid)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getEmployeesPageData(EmployeeEntity $employeeEntity): array
    {
        $query = Employee::query()
            ->with('user')
            ->orderBy('name');

        if (($employeeEntity->search ?? '') !== '') {
            $search = $employeeEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('specialization', 'LIKE', "%{$search}%")
                    ->orWhere('sip_number', 'LIKE', "%{$search}%");
            });
        }

        if (($employeeEntity->filter_role ?? '') !== '') {
            $query->where('role', $employeeEntity->filter_role);
        }

        return [
            'items' => $query->paginate(25)->withQueryString(),
            'totalAll' => Employee::query()->count(),
            'totalDoctors' => Employee::query()->where('role', 'dokter')->count(),
            'activeEmployees' => Employee::query()->where('is_active', true)->count(),
        ];
    }

    public function createEmployee(EmployeeEntity $employeeEntity): void
    {
        DB::beginTransaction();
        try {
            Employee::query()->create([
                'name' => $employeeEntity->name,
                'role' => $employeeEntity->role,
                'specialization' => $employeeEntity->specialization,
                'fee_default' => $employeeEntity->fee_default,
                'sip_number' => $employeeEntity->sip_number,
                'str_number' => $employeeEntity->str_number,
                'is_active' => $employeeEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEmployee(EmployeeEntity $employeeEntity): void
    {
        DB::beginTransaction();
        try {
            Employee::query()->findOrFail($employeeEntity->uuid)->update([
                'name' => $employeeEntity->name,
                'role' => $employeeEntity->role,
                'specialization' => $employeeEntity->specialization,
                'fee_default' => $employeeEntity->fee_default,
                'sip_number' => $employeeEntity->sip_number,
                'str_number' => $employeeEntity->str_number,
                'is_active' => $employeeEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function employeeHasUser(EmployeeEntity $employeeEntity): bool
    {
        return (bool) Employee::query()->findOrFail($employeeEntity->uuid)->user_uuid;
    }

    public function deleteEmployee(EmployeeEntity $employeeEntity): void
    {
        DB::beginTransaction();
        try {
            Employee::query()->findOrFail($employeeEntity->uuid)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createAccount(AccountEntity $accountEntity): void
    {
        DB::beginTransaction();
        try {
            Account::query()->create([
                'code' => $accountEntity->code,
                'name' => $accountEntity->name,
                'type' => $accountEntity->type,
                'type_saldo' => $accountEntity->type_saldo,
                'is_header' => $accountEntity->is_header,
                'is_active' => $accountEntity->is_active,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAccount(AccountEntity $accountEntity): void
    {
        DB::beginTransaction();
        try {
            Account::query()->findOrFail($accountEntity->uuid)->update([
                'code' => $accountEntity->code,
                'name' => $accountEntity->name,
                'type' => $accountEntity->type,
                'type_saldo' => $accountEntity->type_saldo,
                'is_header' => $accountEntity->is_header,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
