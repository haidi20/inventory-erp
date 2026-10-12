<?php

namespace App\Modules\Clinical\Integration\Entities;

/**
 * Entity untuk merepresentasikan data Kunjungan (Encounter) di platform Satusehat.
 * Digunakan sebagai induk untuk data klinis lainnya (Condition, Observation, dll).
 */
class FhirEncounterEntity
{
    /**
     * @var string|null ID unik Encounter yang digenerate oleh Satusehat.
     */
    public ?string $id;

    /**
     * @var string|null Status kunjungan (misal: arrived, in-progress, finished).
     */
    public ?string $status;

    /**
     * @var string|null Tanggal dan waktu mulai kunjungan dalam format ISO8601.
     */
    public ?string $start;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->start = $data['period']['start'] ?? null;
    }
}

