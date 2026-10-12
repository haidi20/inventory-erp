<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\CustomerSupplierFilterEntity;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Support\Collection;

class CustomerSupplierRepository
{
    public function getCustomers(CustomerSupplierFilterEntity $filterEntity): Collection
    {
        $query = Patient::query()->orderBy('name');

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('medical_record_no', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('bpjs_number', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Patient $patient): array {
            return [
                'code' => $patient->medical_record_no ?: '-',
                'name' => $patient->name,
                'gender' => $patient->genderLabel(),
                'phone' => $patient->phone ?: '-',
                'bpjs_status' => $patient->bpjs_number ? 'Terdaftar' : 'Non BPJS',
                'address' => $patient->address ?: '-',
            ];
        });
    }

    public function getSuppliers(CustomerSupplierFilterEntity $filterEntity): Collection
    {
        $query = Supplier::query()->orderBy('name');

        if (($filterEntity->search ?? '') !== '') {
            $search = $filterEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (Supplier $supplier): array {
            return [
                'name' => $supplier->name,
                'phone' => $supplier->phone ?: '-',
                'email' => $supplier->email ?: '-',
                'term_days' => (int) ($supplier->term_days ?? 0),
                'status' => $supplier->is_active ? 'Aktif' : 'Nonaktif',
                'address' => $supplier->address ?: '-',
            ];
        });
    }
}
