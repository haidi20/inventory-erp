<?php

namespace App\Modules\Clinical\Integration\Services;

use Illuminate\Support\Carbon;
use App\Modules\Operational\Master\Services\MasterPublicService;
use App\Modules\Clinical\Integration\Entities\SatusehatPayloadEntity;
use App\Modules\Clinical\Registration\Repositories\PatientRepositoryInterface;
use App\Modules\Clinical\Integration\Repositories\SatusehatRepositoryInterface;
use InvalidArgumentException;

class SatusehatService
{
    /**
     * Interface repositori untuk akses data Satusehat.
     */
    private SatusehatRepositoryInterface $satusehatRepo;
    private PatientRepositoryInterface $patientRepo;

    /**
     * Dependency injection untuk repositori Satusehat.
     */
    public function __construct(SatusehatRepositoryInterface $satusehatRepo, PatientRepositoryInterface $patientRepo)
    {
        $this->satusehatRepo = $satusehatRepo;
        $this->patientRepo = $patientRepo;
    }

    /**
     * Konversi Unix Timestamp (bigInteger) ke format ISO8601 (FHIR SATUSEHAT).
     */
    public function convertToIso8601(?int $timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)
            ->setTimezone(config('app.timezone', 'Asia/Makassar'))
            ->toIso8601String();
    }

    /**
     * Helper untuk mengambil token internal dengan mekanisme Caching (60 menit).
     */
    private function getToken(): string
    {
        return \Cache::remember('satusehat_access_token', 3600, function () {
            $auth = $this->satusehatRepo->fetchToken();
            return $auth['access_token'] ?? '';
        });
    }

    public function CheckPractitioner(string $nik): ?array
    {
        if (strlen(trim($nik)) !== 16) {
            throw new InvalidArgumentException("nik praktisi wajib 16 digit");
        }

        $practitioner = $this->satusehatRepo->getPractitionerByNIK($this->getToken(), $nik);
        
        if ($practitioner && isset($practitioner['id'])) {
            app(MasterPublicService::class)->updateEmployeePractitionerId($nik, $practitioner['id']);
        }

        return $practitioner;
    }

    public function CheckPatient(string $nik): ?array
    {
        if (strlen(trim($nik)) !== 16) {
            throw new InvalidArgumentException("nik pasien wajib 16 digit");
        }

        $patient = $this->satusehatRepo->getPatientByNIK($this->getToken(), $nik);

        if ($patient && isset($patient['id'])) {
            $this->patientRepo->updateIhsNumberByNik($nik, $patient['id']);
        }

        return $patient;
    }

    public function GetOrganization(string $orgId): ?array
    {
        if (empty(trim($orgId))) {
            throw new InvalidArgumentException("organizaton id wajib diisi");
        }

        return $this->satusehatRepo->getOrganizationByID($this->getToken(), $orgId);
    }

    public function GenerateKYCCode(): ?array
    {
        return $this->satusehatRepo->generateKYC($this->getToken());
    }

    public function CheckCoverageEligibility(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postCoverageEligibilityRequest($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendChargeItem(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postChargeItem($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendInvoice(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postInvoice($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendClaim(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postClaim($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendPaymentNotice(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postPaymentNotice($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendEncounter(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postEncounter($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendCondition(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postCondition($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendLocation(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postLocation($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendObservation(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postObservation($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendMedicationRequest(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postMedicationRequest($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendMedicationDispense(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postMedicationDispense($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendAllergyIntolerance(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postAllergyIntolerance($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendComposition(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postComposition($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    public function SendAccount(SatusehatPayloadEntity $entity): ?array
    {
        return $this->satusehatRepo->postAccount($this->getToken(), $this->convertTimestampsInPayload($entity->data));
    }

    /**
     * Mengonversi semua nilai numerik (Unix Timestamp) dalam payload 
     * menjadi string ISO8601 secara rekursif.
     */
    private function convertTimestampsInPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->convertTimestampsInPayload($value);
                continue;
            }

            // Jika key mengandung indikasi waktu dan nilainya integer (Unix)
            $timeKeys = ['start', 'end', 'time', 'occurrenceDateTime', 'recorded', 'authoredOn', 'effectiveDateTime'];
            if (in_array($key, $timeKeys) && is_int($value)) {
                $payload[$key] = $this->convertToIso8601($value);
            }
        }
        return $payload;
    }
}
