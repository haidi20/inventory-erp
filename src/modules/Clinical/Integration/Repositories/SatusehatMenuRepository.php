<?php

namespace App\Modules\Clinical\Integration\Repositories;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Registration\Models\Patient;

class SatusehatMenuRepository
{
    public function getOverview(): array
    {
        $patients = Patient::query()
            ->orderBy('name')
            ->get();

        $encounters = Encounter::query()
            ->with(['patient', 'doctor'])
            ->latest('created_at')
            ->get();

        return [
            'totalPatients' => $patients->count(),
            'unsyncedPatients' => $patients->count(),
            'totalEncounters' => $encounters->count(),
            'readyEncounters' => $encounters
                ->filter(fn ($encounter) => $encounter->patient_uuid && $encounter->doctor_uuid && filled($encounter->diagnosis))
                ->count(),
            'patients' => $patients,
            'encounters' => $encounters,
            'orgId' => config('services.satusehat.org_id', '-'),
            'clientId' => config('services.satusehat.client_id'),
            'platformReady' => filled(config('services.satusehat.org_id')) && filled(config('services.satusehat.client_id')),
        ];
    }
}
