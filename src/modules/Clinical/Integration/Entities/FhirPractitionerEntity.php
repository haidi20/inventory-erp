<?php

namespace App\Modules\Clinical\Integration\Entities;

/**
 * Entity untuk merepresentasikan data Tenaga Kesehatan (Practitioner) dari FHIR Satusehat.
 */
class FhirPractitionerEntity
{
    /**
     * @var string|null ID unik praktisi di platform Satusehat.
     */
    public ?string $id;

    /**
     * @var string|null Nama lengkap praktisi.
     */
    public ?string $name;

    /**
     * @var string|null NIK praktisi sebagai identitas unik nasional.
     */
    public ?string $nik;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'][0]['text'] ?? null;
        $this->nik = $data['identifier'][0]['value'] ?? null;
    }
}

