<?php

namespace App\Modules\Clinical\Registration\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * PatientController
 * 
 * Pengontrol API untuk manajemen data Pasien.
 * Menggunakan PatientRepository untuk persistensi data dan PatientEntity untuk integritas tipe.
 */
class PatientController extends Controller
{
    private PatientService $service;

    public function __construct(PatientService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Menampilkan daftar pasien dengan paginasi.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = auth()->user();
        $entity = new PatientEntity(
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
            search: $request->query('search'),
            gender: $request->query('gender'),
            page: (int) ($request->query('page', 1)),
            per_page: (int) ($request->query('per_page', 20))
        );

        $result = $this->service->getPageData($entity);
        
        return $this->apiResource(collect(array_map(fn($item) => $item->toArray(), $result['items'])))
            ->additional([
                'meta' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'] ?? 20,
                    'stats' => [
                        'total' => $result['totalPatients'],
                        'active' => $result['activePatients'],
                        'insured' => $result['insuredPatients'],
                    ]
                ]
            ]);
    }

    /**
     * Menyimpan data pasien baru.
     * 
     * @param Request $request
     * @return JsonResource
     */
    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'medical_record_no' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bpjs_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $user = auth()->user();
        $entity = PatientEntity::fromArray(array_merge($data, [
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid' => $user->uuid
        ]));

        $saved = $this->service->save($entity);
        
        $this->audit('patient.create', null, null, $saved->toArray());
        
        return $this->apiResource($saved->toArray());
    }

    /**
     * Menampilkan detail pasien.
     * 
     * @param string $uuid
     * @return JsonResource
     */
    public function show(string $uuid): JsonResource
    {
        $user = auth()->user();
        $entity = new PatientEntity(
            uuid: $uuid, 
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $patient = $this->service->findByUuid($entity);
        
        if (!$patient) {
            abort(404, 'Patient not found');
        }

        return $this->apiResource($patient->toArray());
    }

    /**
     * Memperbarui data pasien.
     * 
     * @param Request $request
     * @param string $uuid
     * @return JsonResource
     */
    public function update(Request $request, string $uuid): JsonResource
    {
        $user = auth()->user();
        $entitySearch = new PatientEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);
        if (!$existing) {
            abort(404, 'Patient not found');
        }

        $data = $request->validate([
            'medical_record_no' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bpjs_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $entity = PatientEntity::fromArray(array_merge($existing->toArray(), $data, [
            'actor_uuid' => $user->uuid
        ]));

        $updated = $this->service->save($entity);
        
        $this->audit('patient.update', null, $existing->toArray(), $updated->toArray());
        
        return $this->apiResource($updated->toArray());
    }

    /**
     * Menghapus pasien (Soft Delete).
     * 
     * @param string $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $entitySearch = new PatientEntity(
            uuid: $uuid, 
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);
        
        if ($existing) {
            $this->service->delete($existing);
            $this->audit('patient.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
