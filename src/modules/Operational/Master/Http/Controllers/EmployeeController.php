<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Operational\Master\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * EmployeeController
 * 
 * Pengontrol API untuk manajemen data Karyawan/Tenaga Medis.
 * Menggunakan EmployeeRepository untuk persistensi data dan EmployeeEntity untuk integritas tipe.
 */
class EmployeeController extends Controller
{
    private EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Menampilkan daftar karyawan dengan paginasi.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = auth()->user();
        $entity = new EmployeeEntity(
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
            search: $request->query('search'),
            filter_role: $request->query('role'),
            page: (int) ($request->query('page', 1)),
            per_page: (int) ($request->query('per_page', 25))
        );

        $result = $this->service->getPageData($entity);
        
        return $this->apiResource(collect(array_map(fn($item) => $item->toArray(), $result['items'])))
            ->additional([
                'meta' => [
                    'total' => $result['total'],
                    'page' => $result['current_page'],
                    'per_page' => $result['per_page'] ?? 25,
                    'stats' => [
                        'total' => $result['totalAll'],
                        'doctors' => $result['totalDoctors'],
                        'active' => $result['activeEmployees'],
                    ]
                ]
            ]);
    }

    /**
     * Menyimpan data karyawan baru.
     * 
     * @param Request $request
     * @return JsonResource
     */
    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'user_uuid' => 'nullable|uuid',
            'nik' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:100',
            'fee_default' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $user = auth()->user();
        $entity = EmployeeEntity::fromArray(array_merge($data, [
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid' => $user->uuid
        ]));

        $saved = $this->service->save($entity);
        
        $this->audit('employee.create', null, null, $saved->toArray());
        
        return $this->apiResource($saved->toArray());
    }

    /**
     * Menampilkan detail karyawan.
     * 
     * @param string $uuid
     * @return JsonResource
     */
    public function show(string $uuid): JsonResource
    {
        $user = auth()->user();
        $entity = new EmployeeEntity(
            uuid: $uuid, 
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $employee = $this->service->findByUuid($entity);
        
        if (!$employee) {
            abort(404, 'Employee not found');
        }

        return $this->apiResource($employee->toArray());
    }

    /**
     * Memperbarui data karyawan.
     * 
     * @param Request $request
     * @param string $uuid
     * @return JsonResource
     */
    public function update(Request $request, string $uuid): JsonResource
    {
        $user = auth()->user();
        $entitySearch = new EmployeeEntity(
            uuid: $uuid, 
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);
        if (!$existing) {
            abort(404, 'Employee not found');
        }

        $data = $request->validate([
            'nik' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'role' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:100',
            'fee_default' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $entity = EmployeeEntity::fromArray(array_merge($existing->toArray(), $data, [
            'actor_uuid' => $user->uuid,
            'company_uuid' => current_company_uuid()
        ]));

        $updated = $this->service->save($entity);
        
        $this->audit('employee.update', null, $existing->toArray(), $updated->toArray());
        
        return $this->apiResource($updated->toArray());
    }

    /**
     * Menghapus karyawan (Soft Delete).
     * 
     * @param string $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $entitySearch = new EmployeeEntity(
            uuid: $uuid, 
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);
        
        if ($existing) {
            $this->service->delete($existing);
            $this->audit('employee.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
