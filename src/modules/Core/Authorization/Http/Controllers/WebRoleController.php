<?php

namespace App\Modules\Core\Authorization\Http\Controllers;

use App\Modules\Core\Authorization\Entities\RoleEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Authorization\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private RoleService $roleService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->authorize('settings.role.view');

        $entity = RoleEntity::fromArray([
            'search' => $request->string('search')->trim(),
            'search_column' => $request->string('search_column', 'all'),
            'sort_by' => $request->string('sort_by', 'name'),
            'sort_direction' => $request->string('sort_direction', 'asc'),
            'per_page' => $request->integer('per_page', 10),
            'page' => $request->integer('page', 1),
        ]);

        $data = $this->roleService->GetRoles($entity);

        return Inertia::render('Core/Identity/presentations/views/RoleIndex', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('settings.role.manage');

        $entity = RoleEntity::fromArray($request->all() + ['actor_uuid' => $request->user()?->uuid]);
        $this->roleService->Store($entity);

        return redirect()->back()->with('success', 'Role berhasil disimpan.');
    }

    public function update(Request $request, string $uuid)
    {
        $this->authorize('settings.role.manage');

        $entity = RoleEntity::fromArray($request->all() + [
            'uuid' => $uuid,
            'actor_uuid' => $request->user()?->uuid,
        ]);
        $this->roleService->Update($entity);

        return redirect()->back()->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $this->authorize('settings.role.manage');

        $this->roleService->Destroy(new RoleEntity(uuid: $uuid, actor_uuid: auth()->user()?->uuid));

        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }
}
