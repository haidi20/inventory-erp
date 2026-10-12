<?php

namespace App\Modules\Clinical\Integration\Entities;

/**
 * Entity untuk merepresentasikan data Pasien dari FHIR Satusehat (Master Patient Index).
 */
class FhirPatientEntity
{
    /**
     * @var string|null ID Patient (IHS Number) di platform Satusehat.
     */
    public ?string $id;

    /**
     * @var string|null Nama lengkap pasien sesuai identitas.
     */
    public ?string $name;

    /**
     * @var string|null NIK pasien untuk verifikasi kependudukan.
     */
    public ?string $nik;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'][0]['text'] ?? null;
        $this->nik = $data['identifier'][0]['value'] ?? null;
    }
}

