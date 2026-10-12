<?php

namespace App\Modules\Clinical\Integration\Http\Controllers;

use App\Modules\Clinical\Integration\Entities\SatusehatPayloadEntity;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Integration\Services\SatusehatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SatusehatController extends Controller
{
    /**
     * Service untuk mengelola logika integrasi Satusehat.
     */
    private SatusehatService $satusehatService;

    /**
     * Dependency injection untuk SatusehatService.
     */
    public function __construct(SatusehatService $satusehatService)
    {
        $this->satusehatService = $satusehatService;
    }

    /**
     * Pengecekan Tenaga Kesehatan berdasarkan NIK.
     */
    public function checkPractitioner(string $nik): JsonResponse
    {
        try {
            $result = $this->satusehatService->CheckPractitioner($nik);
            return response()->json([
                'success' => true,
                'message' => 'data praktisi berhasil ditemukan',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal memverifikasi praktisi');
        }
    }

    /**
     * Pengecekan Pasien MPI berdasarkan NIK.
     */
    public function checkPatient(string $nik): JsonResponse
    {
        try {
            $result = $this->satusehatService->CheckPatient($nik);
            return response()->json([
                'success' => true,
                'message' => 'data pasien berhasil ditemukan',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal memverifikasi pasien');
        }
    }

    /**
     * Verifikasi Organisasi berdasarkan ID.
     */
    public function checkOrganization(string $id): JsonResponse
    {
        try {
            $result = $this->satusehatService->GetOrganization($id);
            return response()->json([
                'success' => true,
                'message' => 'data organisasi berhasil ditemukan',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal memverifikasi organisasi');
        }
    }

    /**
     * Mengajukan kode KYC IT.
     */
    public function generateKYC(): JsonResponse
    {
        try {
            $result = $this->satusehatService->GenerateKYCCode();
            return response()->json([
                'success' => true,
                'message' => 'kode KYC berhasil dibuat',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal generate kode KYC');
        }
    }

    /**
     * Pengecekan Kelayakan BPJS.
     */
    public function checkCoverageEligibility(Request $request): JsonResponse
    {
        try {
            $result = $this->satusehatService->CheckCoverageEligibility(
                SatusehatPayloadEntity::fromArray($request->all())
            );
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal mengecek kelayakan BPJS');
        }
    }

    /**
     * Kirim Encounter (Kunjungan).
     */
    public function sendEncounter(Request $request): JsonResponse
    {
        try {
            $result = $this->satusehatService->SendEncounter(
                SatusehatPayloadEntity::fromArray($request->all())
            );
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal mengirim encounter');
        }
    }

    /**
     * Kirim Condition (Diagnosis).
     */
    public function sendCondition(Request $request): JsonResponse
    {
        try {
            $result = $this->satusehatService->SendCondition(
                SatusehatPayloadEntity::fromArray($request->all())
            );
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal mengirim condition');
        }
    }

    /**
     * Kirim Observation (Vital Signs/Lab).
     */
    public function sendObservation(Request $request): JsonResponse
    {
        try {
            $result = $this->satusehatService->SendObservation(
                SatusehatPayloadEntity::fromArray($request->all())
            );
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal mengirim observasi');
        }
    }

    /**
     * Kirim MedicationRequest (Resep).
     */
    public function sendMedicationRequest(Request $request): JsonResponse
    {
        try {
            $result = $this->satusehatService->SendMedicationRequest(
                SatusehatPayloadEntity::fromArray($request->all())
            );
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            return $this->respondError($e, 'gagal mengirim resep');
        }
    }

    /**
     * Helper untuk respon error seragam.
     */
    private function respondError(\Exception $e, string $fallbackMessage): JsonResponse
    {
        $status = ($e instanceof InvalidArgumentException) ? 400 : 500;
        
        ExceptionLogger::log($e, self::class);
        Log::error("Satusehat Controller Error: {$fallbackMessage} - " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => ($status === 400) ? $e->getMessage() : $fallbackMessage,
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], $status);
    }
}
