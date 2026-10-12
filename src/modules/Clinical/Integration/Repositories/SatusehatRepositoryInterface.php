<?php

namespace App\Modules\Clinical\Integration\Repositories;

/**
 * Interface SatusehatRepositoryInterface
 *
 * Kontrak untuk akses data ke platform SATUSEHAT (FHIR API).
 */
interface SatusehatRepositoryInterface
{
    public function fetchToken(): ?array;
    public function getPractitionerByNIK(string $token, string $nik): ?array;
    public function getPatientByNIK(string $token, string $nik): ?array;
    public function getOrganizationByID(string $token, string $orgId): ?array;
    public function generateKYC(string $token): ?array;

    // Post FHIR Resources
    public function postChargeItem(string $token, array $data): ?array;
    public function postInvoice(string $token, array $data): ?array;
    public function postClaim(string $token, array $data): ?array;
    public function postPaymentNotice(string $token, array $data): ?array;
    public function postCoverageEligibilityRequest(string $token, array $data): ?array;
    public function postEncounter(string $token, array $data): ?array;
    public function postCondition(string $token, array $data): ?array;
    public function postLocation(string $token, array $data): ?array;
    public function postObservation(string $token, array $data): ?array;
    public function postMedicationRequest(string $token, array $data): ?array;
    public function postMedicationDispense(string $token, array $data): ?array;
    public function postAllergyIntolerance(string $token, array $data): ?array;
    public function postComposition(string $token, array $data): ?array;
    public function postAccount(string $token, array $data): ?array;
}
