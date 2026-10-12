<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;

use App\Modules\Clinical\MedicalRecord\Entities\DoctorTaskFilterEntity;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Registration\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DoctorTaskRepository
{
    public function getActiveTasks(DoctorTaskFilterEntity $entity)
    {
        return Registration::with(['patient', 'doctor'])
            ->where('company_uuid', $entity->company_uuid)
            ->whereNotNull('doctor_uuid')
            ->whereIn('status', ['waiting', 'in_progress'])
            ->orderByRaw("CASE WHEN status = 'waiting' THEN 0 ELSE 1 END")
            ->orderBy('registered_at')
            ->get();
    }

    public function getDoctors(DoctorTaskFilterEntity $entity)
    {
        return Employee::query()
            ->where('company_uuid', $entity->company_uuid)
            ->where('role', 'dokter')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getCompletedTodayCounts(DoctorTaskFilterEntity $entity)
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::tomorrow()->subSecond()->timestamp;

        return Encounter::query()
            ->where('company_uuid', $entity->company_uuid)
            ->select('doctor_uuid', DB::raw('COUNT(*) as total'))
            ->whereNotNull('doctor_uuid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('doctor_uuid')
            ->pluck('total', 'doctor_uuid');
    }

    public function getRecentCompleted(DoctorTaskFilterEntity $entity)
    {
        return Encounter::with(['patient', 'doctor', 'registration'])
            ->where('company_uuid', $entity->company_uuid)
            ->whereNotNull('doctor_uuid')
            ->latest()
            ->limit(8)
            ->get();
    }
}

