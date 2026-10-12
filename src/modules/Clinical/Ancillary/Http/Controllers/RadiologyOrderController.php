<?php

namespace App\Modules\Clinical\Ancillary\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Ancillary\Entities\RadiologyOrderEntity;
use App\Modules\Clinical\Ancillary\Services\RadiologyOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RadiologyOrderController extends Controller
{
    public function __construct(
        private readonly RadiologyOrderService $service,
    ) {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $entity = new RadiologyOrderEntity(
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
            search:       $request->string('search')->trim()->toString(),
            status:       $request->string('status')->trim()->toString(),
            page:         (int) $request->input('page', 1),
            per_page:     (int) $request->input('per_page', 15),
        );

        $result = $this->service->getPageData($entity);

        return $this->apiResource(collect($result['items']))->additional([
            'meta' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $user   = auth()->user();
        $entity = $this->service->findByUuid($uuid, current_company_uuid());

        abort_if(!$entity, 404, 'Radiology order tidak ditemukan.');

        return $this->apiResource($entity);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_uuid'   => 'required|uuid',
            'doctor_uuid'    => 'nullable|uuid',
            'modality'       => 'required|string|max:50',
            'body_part'      => 'required|string|max:100',
            'clinical_notes' => 'nullable|string|max:2000',
            'ordered_at'     => 'nullable|integer',
        ]);

        $user   = auth()->user();
        $entity = new RadiologyOrderEntity(
            branch_uuid:    current_branch_uuid(),
            company_uuid:   current_company_uuid(),
            patient_uuid:   $data['patient_uuid'],
            doctor_uuid:    $data['doctor_uuid'] ?? null,
            modality:       $data['modality'],
            body_part:      $data['body_part'],
            clinical_notes: $data['clinical_notes'] ?? null,
            ordered_at:     $data['ordered_at'] ?? time(),
            actor_uuid:     $user->uuid,
        );

        $saved = $this->service->save($entity);
        $this->audit('radiology_order.create', null, null, (array) $saved);

        return $this->apiResource($saved);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'status'           => 'nullable|string|in:pending,proses,selesai',
            'findings'         => 'nullable|string|max:5000',
            'impression'       => 'nullable|string|max:2000',
            'radiologist_uuid' => 'nullable|uuid',
            'resulted_at'      => 'nullable|integer',
        ]);

        $user        = auth()->user();
        $companyUuid = current_company_uuid();
        $existing    = $this->service->findByUuid($uuid, $companyUuid);

        abort_if(!$existing, 404, 'Radiology order tidak ditemukan.');

        $entity = new RadiologyOrderEntity(
            uuid:             $uuid,
            branch_uuid:      $existing->branch_uuid,
            company_uuid:     $companyUuid,
            order_no:         $existing->order_no,
            patient_uuid:     $existing->patient_uuid,
            doctor_uuid:      $existing->doctor_uuid,
            modality:         $existing->modality,
            body_part:        $existing->body_part,
            clinical_notes:   $existing->clinical_notes,
            status:           $data['status'] ?? $existing->status,
            findings:         $data['findings'] ?? $existing->findings,
            impression:       $data['impression'] ?? $existing->impression,
            radiologist_uuid: $data['radiologist_uuid'] ?? $existing->radiologist_uuid,
            ordered_at:       $existing->ordered_at,
            resulted_at:      $data['resulted_at'] ?? $existing->resulted_at,
            actor_uuid:       $user->uuid,
        );

        $saved = $this->service->save($entity);
        $this->audit('radiology_order.update', (array) $existing, null, (array) $saved);

        return $this->apiResource($saved);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();
        $existing    = $this->service->findByUuid($uuid, $companyUuid);

        abort_if(!$existing, 404, 'Radiology order tidak ditemukan.');

        $this->service->delete($uuid, $user->uuid);
        $this->audit('radiology_order.delete', (array) $existing, null, null);

        return response()->json(null, 204);
    }
}
