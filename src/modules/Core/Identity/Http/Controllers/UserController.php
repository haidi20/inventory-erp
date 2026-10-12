<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request)
    {
        $entity = new UserEntity(
            search: $request->string('search')->trim(),
            search_column: $request->string('search_column', 'all'),
            role_filter: $request->string('role')->trim(),
            sort_by: $request->string('sort_by', 'name'),
            sort_direction: $request->string('sort_direction', 'asc'),
            per_page: $request->integer('per_page', 10),
            page: $request->integer('page', 1)
        );

        $result = $this->userService->getPaginated($entity);

        return response()->json([
            'data' => $result['users']['data'] ?? [],
            'roles' => $result['roles'] ?? [],
            'summary' => $result['summary'] ?? [],
            'filters' => $result['filters'] ?? [],
            'meta' => $result['users']['meta'] ?? [],
            'links' => $result['users']['links'] ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $entity = UserEntity::fromArray(array_merge($request->all(), [
            'branch_uuid' => $request->input('branch_uuid') ?: Auth::user()->active_branch_uuid,
            'company_uuid' => Auth::user()->active_company_uuid,
            'actor_uuid' => Auth::id(),
        ]));

        $user = $this->userService->save($entity);

        return response()->json($user);
    }

    public function update(Request $request, $uuid)
    {
        $entity = UserEntity::fromArray(array_merge($request->all(), [
            'uuid' => $uuid,
            'branch_uuid' => $request->input('branch_uuid') ?: Auth::user()->active_branch_uuid,
            'company_uuid' => Auth::user()->active_company_uuid,
            'actor_uuid' => Auth::id(),
        ]));

        $user = $this->userService->save($entity);

        return response()->json($user);
    }

    public function destroy($uuid)
    {
        $entity = new UserEntity(
            uuid: $uuid,
            actor_uuid: Auth::id()
        );
        $this->userService->delete($entity);

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $entity = new UserEntity(
            search: $request->string('search')->trim(),
            search_column: $request->string('search_column', 'all'),
            role_filter: $request->string('role')->trim(),
            sort_by: $request->string('sort_by', 'name'),
            sort_direction: $request->string('sort_direction', 'asc')
        );

        return $this->userService->export($entity);
    }

    public function import(Request $request)
    {
        $this->userService->import($request->file('file'), Auth::id());

        return response()->json(['success' => true]);
    }

    public function downloadTemplate()
    {
        return $this->userService->downloadImportTemplate();
    }
}
