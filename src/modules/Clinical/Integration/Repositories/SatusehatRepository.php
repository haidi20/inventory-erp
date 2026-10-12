<?php

namespace App\Modules\Clinical\Integration\Repositories;

use App\Shared\Support\ExceptionLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class SatusehatRepository
 *
 * Implementasi repository akses API SATUSEHAT menggunakan Laravel HTTP Client.
 */
class SatusehatRepository implements SatusehatRepositoryInterface
{
    /**
     * Base URL platform SATUSEHAT (Sandbox/Staging/Production).
     */
    protected string $baseUrl;

    /**
     * URL untuk pertukaran token OAuth2.
     */
    protected string $authUrl;

    /**
     * Client ID dari portal developer SATUSEHAT.
     */
    protected string $clientId;

    /**
     * Client Secret dari portal developer SATUSEHAT.
     */
    protected string $clientSecret;

    /**
     * Inisialisasi konfigurasi dari environment atau database settings.
     */
    public function __construct()
    {
        $this->baseUrl = (string) config('services.satusehat.base_url', '');
        $this->authUrl = (string) config('services.satusehat.auth_url', '');
        $this->clientId = (string) config('services.satusehat.client_id', '');
        $this->clientSecret = (string) config('services.satusehat.client_secret', '');
    }

    /**
     * @inheritDoc
     */
    public function fetchToken(): ?array
    {
        if (empty($this->authUrl) || empty($this->clientId) || empty($this->clientSecret)) {
            Log::error('Satusehat Credentials Missing');
            return null;
        }

        try {
            $response = Http::asForm()->post($this->authUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Satusehat Auth Failed (' . $response->status() . '): ' . $response->body());
            return null;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            Log::error('Satusehat Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getPractitionerByNIK(string $token, string $nik): ?array
    {
        $url = "{$this->baseUrl}/Practitioner?identifier=https://terminologi.kemkes.go.id/fhir/id/nik|{$nik}";
        $bundle = $this->getFhirResource($token, $url);

        if ($bundle && isset($bundle['entry'][0]['resource'])) {
            return $bundle['entry'][0]['resource'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPatientByNIK(string $token, string $nik): ?array
    {
        $url = "{$this->baseUrl}/Patient?identifier=https://terminologi.kemkes.go.id/fhir/id/nik|{$nik}";
        $bundle = $this->getFhirResource($token, $url);

        if ($bundle && isset($bundle['entry'][0]['resource'])) {
            return $bundle['entry'][0]['resource'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getOrganizationByID(string $token, string $orgId): ?array
    {
        $url = "{$this->baseUrl}/Organization/{$orgId}";
        return $this->getFhirResource($token, $url);
    }

    /**
     * @inheritDoc
     */
    public function generateKYC(string $token): ?array
    {
        $url = "{$this->baseUrl}/kyc/v1/generate-code";
        try {
            $response = Http::withToken($token)->post($url, []);
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return null;
        }
    }

    /**
     * FHIR POST Helpers
     */
    public function postChargeItem(string $token, array $data): ?array { return $this->postFhirResource($token, 'ChargeItem', $data); }
    public function postInvoice(string $token, array $data): ?array { return $this->postFhirResource($token, 'Invoice', $data); }
    public function postClaim(string $token, array $data): ?array { return $this->postFhirResource($token, 'Claim', $data); }
    public function postPaymentNotice(string $token, array $data): ?array { return $this->postFhirResource($token, 'PaymentNotice', $data); }
    public function postCoverageEligibilityRequest(string $token, array $data): ?array { return $this->postFhirResource($token, 'CoverageEligibilityRequest', $data); }
    public function postEncounter(string $token, array $data): ?array { return $this->postFhirResource($token, 'Encounter', $data); }
    public function postCondition(string $token, array $data): ?array { return $this->postFhirResource($token, 'Condition', $data); }
    public function postLocation(string $token, array $data): ?array { return $this->postFhirResource($token, 'Location', $data); }
    public function postObservation(string $token, array $data): ?array { return $this->postFhirResource($token, 'Observation', $data); }
    public function postMedicationRequest(string $token, array $data): ?array { return $this->postFhirResource($token, 'MedicationRequest', $data); }
    public function postMedicationDispense(string $token, array $data): ?array { return $this->postFhirResource($token, 'MedicationDispense', $data); }
    public function postAllergyIntolerance(string $token, array $data): ?array { return $this->postFhirResource($token, 'AllergyIntolerance', $data); }
    public function postComposition(string $token, array $data): ?array { return $this->postFhirResource($token, 'Composition', $data); }
    public function postAccount(string $token, array $data): ?array { return $this->postFhirResource($token, 'Account', $data); }

    /**
     * Internal GET Helper
     */
    protected function getFhirResource(string $token, string $url): ?array
    {
        try {
            $response = Http::withToken($token)->get($url);
            if ($response->successful()) {
                return $response->json();
            }
            Log::warning('Satusehat API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return null;
        }
    }

    /**
     * Internal POST Helper
     */
    protected function postFhirResource(string $token, string $resource, array $data): ?array
    {
        $url = "{$this->baseUrl}/{$resource}";
        try {
            $response = Http::withToken($token)->post($url, $data);
            if ($response->successful() || $response->status() === 201) {
                return $response->json();
            }
            Log::error("Satusehat POST {$resource} Failed: " . $response->body());
            return null;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return null;
        }
    }
}
