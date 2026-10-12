<?php

namespace App\Modules\Clinical\MedicalRecord\Services;

use App\Modules\Clinical\MedicalRecord\Repositories\AncillaryWorkflowRepository;

class AncillaryWorkflowService
{
    public function __construct(private AncillaryWorkflowRepository $ancillaryWorkflowRepository)
    {
    }

    public function getRequestEntryPageData(string $companyUuid): array
    {
        return [
            'pageTitle' => 'Input Permintaan Lab / Radiologi',
            'pageSubtitle' => 'Buka permintaan penunjang dari alur pelayanan poli agar order laboratorium dan radiologi tercatat rapi di sistem.',
        ] + $this->ancillaryWorkflowRepository->getRequestEntryData($companyUuid);
    }

    public function getResultEntryPageData(string $companyUuid): array
    {
        return [
            'pageTitle' => 'Petugas Input Hasil Pemeriksaan ke RME',
            'pageSubtitle' => 'Gunakan halaman ini untuk masuk ke input hasil pemeriksaan penunjang sebelum dokter melakukan finalisasi pelayanan.',
        ] + $this->ancillaryWorkflowRepository->getResultEntryData($companyUuid);
    }
}
