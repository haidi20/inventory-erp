<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Identity\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WebUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private UserService $userService)
    {
    }

    /**
     * Display users list.
     */
    public function index(Request $request): Response
    {
        $this->authorize('settings.user.view');

        $companyUuid = (string) (current_company_uuid() ?? '');
        
        $entity = UserEntity::fromArray([
            'search' => $request->string('search')->trim(),
            'search_column' => $request->string('search_column', 'all'),
            'role_filter' => $request->string('role'),
            'sort_by' => $request->string('sort_by', 'name'),
            'sort_direction' => $request->string('sort_direction', 'asc'),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 10),
            'company_uuid' => $companyUuid,
        ]);

        return Inertia::render('Core/Identity/presentations/views/UserIndex', $this->userService->getPaginated($entity));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('settings.user.manage');
        
        $entity = UserEntity::fromArray($request->all() + [
            'actor_uuid' => $request->user()->uuid,
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => (string) (current_company_uuid() ?? ''),
        ]);
        
        $this->userService->save($entity);

        return redirect()->back()->with('success', 'User berhasil disimpan.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $uuid)
    {
        $this->authorize('settings.user.manage');
        
        $entity = UserEntity::fromArray($request->all() + [
            'uuid' => $uuid,
            'actor_uuid' => $request->user()->uuid,
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => (string) (current_company_uuid() ?? ''),
        ]);
        
        $this->userService->save($entity);

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, string $uuid)
    {
        $this->authorize('settings.user.manage');
        
        $this->userService->delete(UserEntity::fromArray([
            'uuid' => $uuid,
            'actor_uuid' => $request->user()->uuid,
            'company_uuid' => (string) (current_company_uuid() ?? ''),
        ]));

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Export data users.
     */
    public function export(Request $request)
    {
        $this->authorize('settings.user.view');
        
        $entity = UserEntity::fromArray($request->all() + [
            'company_uuid' => (string) (current_company_uuid() ?? ''),
        ]);
        
        return $this->userService->export($entity);
    }

    /**
     * Import data users.
     */
    public function import(Request $request)
    {
        $this->authorize('settings.user.manage');
        
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $this->userService->import(
            $request->file('file'),
            $request->user()->uuid,
            (string) (current_branch_uuid() ?? ''),
            (string) (current_company_uuid() ?? '')
        );

        return redirect()->back()->with('success', 'Import user berhasil diproses.');
    }

    /**
     * Download import template for users.
     */
    public function downloadImportTemplate()
    {
        $this->authorize('settings.user.manage');

        return $this->userService->downloadImportTemplate();
    }
}
