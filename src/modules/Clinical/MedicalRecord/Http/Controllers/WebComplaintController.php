<?php

namespace App\Modules\Clinical\MedicalRecord\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\MedicalRecord\Services\ComplaintService;
use App\Modules\Clinical\MedicalRecord\Entities\ComplaintEntity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebComplaintController extends Controller
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

    public function index(Request $request)
    {
        return Inertia::render('Clinical/MedicalRecord/presentations/views/Index', [
            'complaints' => $this->service->getList(array_merge($request->all(), [
                'company_uuid' => $this->companyUuid(),
            ])),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:pengaduan,saran',
            'is_anonymous' => 'boolean'
        ]);

        $entity = ComplaintEntity::fromArray(array_merge($validated, [
            'company_uuid' => $this->companyUuid(),
            'branch_uuid' => $this->branchUuid() ?? '',
            'created_by' => auth()->user()?->uuid,
        ]));
        $this->service->submitComplaint($entity);

        return redirect()->back()->with('success', 'Pengaduan/Saran berhasil dikirim.');
    }

    public function update(Request $request, string $uuid)
    {
        $request->validate(['status' => 'required|in:pending,proses,selesai']);
        $updated = $this->service->updateStatus(
            $uuid,
            $request->status,
            $this->companyUuid(),
            auth()->user()?->uuid
        );

        return redirect()->back()->with(
            $updated ? 'success' : 'error',
            $updated ? 'Status berhasil diperbarui.' : 'Data tidak ditemukan.'
        );
    }

    public function destroy(string $uuid)
    {
        $deleted = $this->service->deleteComplaint($uuid, $this->companyUuid());

        return redirect()->back()->with(
            $deleted ? 'success' : 'error',
            $deleted ? 'Data berhasil dihapus.' : 'Data tidak ditemukan.'
        );
    }
}
