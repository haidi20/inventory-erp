<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;

use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;

class AncillaryWorkflowRepository
{
    public function getRequestEntryData(string $companyUuid): array
    {
        return [
            'lab' => [
                'pendingCount' => LabOrder::query()->where('company_uuid', $companyUuid)->where('status', 'pending')->count(),
                'inProgressCount' => LabOrder::query()->where('company_uuid', $companyUuid)->where('status', 'proses')->count(),
                'completedCount' => LabOrder::query()->where('company_uuid', $companyUuid)->where('status', 'selesai')->count(),
                'latestOrder' => LabOrder::query()->where('company_uuid', $companyUuid)->with(['patient', 'doctor'])->latest('ordered_at')->latest('created_at')->first(),
            ],
            'radiology' => [
                'pendingCount' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->where('status', 'pending')->count(),
                'inProgressCount' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->where('status', 'proses')->count(),
                'completedCount' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->where('status', 'selesai')->count(),
                'latestOrder' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->with(['patient', 'doctor'])->latest('ordered_at')->latest('created_at')->first(),
            ],
        ];
    }

    public function getResultEntryData(string $companyUuid): array
    {
        return [
            'lab' => [
                'readyForResultCount' => LabOrder::query()->where('company_uuid', $companyUuid)->whereIn('status', ['pending', 'proses'])->count(),
                'processingCount' => LabOrder::query()->where('company_uuid', $companyUuid)->where('status', 'proses')->count(),
                'latestOrder' => LabOrder::query()->where('company_uuid', $companyUuid)->with(['patient', 'doctor'])->whereIn('status', ['pending', 'proses'])->latest('ordered_at')->latest('created_at')->first(),
            ],
            'radiology' => [
                'readyForResultCount' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->whereIn('status', ['pending', 'proses'])->count(),
                'processingCount' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->where('status', 'proses')->count(),
                'latestOrder' => RadiologyOrder::query()->where('company_uuid', $companyUuid)->with(['patient', 'doctor'])->whereIn('status', ['pending', 'proses'])->latest('ordered_at')->latest('created_at')->first(),
            ],
        ];
    }
}
