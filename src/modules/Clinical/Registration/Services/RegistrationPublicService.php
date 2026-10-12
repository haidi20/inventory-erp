<?php

namespace App\Modules\Clinical\Registration\Services;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Repositories\PatientRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * RegistrationPublicService
 *
 * Service publik Clinical\Registration yang boleh di-import modul lain.
 * Semua return value berbasis array/Entity — tidak boleh return Model Eloquent ke modul lain.
 */
class RegistrationPublicService
{
    public function __construct(
        protected PatientRepositoryInterface $patientRepository
    ) {}

    /**
     * Ambil daftar pasien aktif untuk dropdown POS/transaksi.
     */
    public function getActivePatients(string $companyUuid): Collection
    {
        return collect($this->patientRepository->listActiveByCompany($companyUuid));
    }
}
