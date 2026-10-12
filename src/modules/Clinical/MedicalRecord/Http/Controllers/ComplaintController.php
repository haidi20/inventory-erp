<?php

namespace App\Modules\Clinical\MedicalRecord\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\MedicalRecord\Services\ComplaintService;
use App\Modules\Clinical\MedicalRecord\Entities\ComplaintEntity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    public function __construct(
        protected ComplaintService $service
    ) {}

    protected function companyUuid(): ?string
    {
        $user = auth()->user();

        return $user?->company_uuid ?: $user?->branch?->company_uuid;
    }

    protected function branchUuid(): ?string
    {
        $user = auth()->user();

        return $user?->branch_uuid ?: $user?->branch?->uuid;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = array_merge($request->all(), [
            'company_uuid' => $this->companyUuid(),
        ]);

        $complaints = $this->service->getList($filters);
        return response()->json($complaints);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:pengaduan,saran',
            'is_anonymous' => 'boolean'
        ]);

        $entity = ComplaintEntity::fromArray(array_merge($validated, [
            'company_uuid' => $this->companyUuid(),
            'branch_uuid'  => $this->branchUuid() ?? '',
            'created_by'   => auth()->user()?->uuid,
        ]));
        $complaint = $this->service->submitComplaint($entity);

        return response()->json([
            'message' => 'Pengaduan/Saran berhasil dikirim.',
            'data' => $complaint
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $complaint = $this->service->find($uuid, $this->companyUuid());
        
        if (!$complaint) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json($complaint);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,proses,selesai'
        ]);

        $complaint = $this->service->updateStatus(
            $uuid,
            $request->status,
            $this->companyUuid(),
            auth()->user()?->uuid
        );

        if (!$complaint) {
            return response()->json(['message' => 'Gagal memperbarui status atau data tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            'data' => $complaint
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $success = $this->service->deleteComplaint($uuid, $this->companyUuid());

        if (!$success) {
            return response()->json(['message' => 'Gagal menghapus atau data tidak ditemukan.'], 404);
        }

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }
}
