<?php

namespace App\Modules\Core\Authorization\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Authorization\Services\RoleService;
use App\Modules\Core\Authorization\Entities\RoleEntity;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
    }

    public function index(Request $request)
    {
        $entity = RoleEntity::fromArray([
            'search' => $request->string('search')->trim(),
            'search_column' => $request->string('search_column')->trim(),
            'sort_by' => $request->string('sort_by', 'name'),
            'sort_direction' => $request->string('sort_direction', 'asc'),
            'per_page' => $request->integer('per_page', 10),
            'page' => $request->integer('page', 1),
        ]);

        return response()->json($this->roleService->roles($entity));
    }

    public function store(Request $request)
    {
        $entity = RoleEntity::fromArray($request->all());
        $result = $this->roleService->storeRole($entity);
        return response()->json($result);
    }

    public function update(Request $request, $uuid)
    {
        $data = $request->all();
        $data['uuid'] = $uuid;
        $entity = RoleEntity::fromArray($data);
        $result = $this->roleService->updateRole($entity);
        return response()->json($result);
    }

    public function destroy($uuid)
    {
        $entity = new RoleEntity(uuid: $uuid);
        $result = $this->roleService->destroyRole($entity);
        return response()->json($result);
    }

    public function export(Request $request)
    {
        $entity = RoleEntity::fromArray($request->all());
        return $this->roleService->export($entity);
    }

    public function import(Request $request)
    {
        $result = $this->roleService->import($request->file('file'), \Illuminate\Support\Facades\Auth::id());
        return response()->json($result);
    }

    public function downloadTemplate()
    {
        return $this->roleService->downloadImportTemplate();
    }
}
