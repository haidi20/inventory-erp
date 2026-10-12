<?php

namespace App\Modules\Core\Organization\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Services\BranchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebBranchController extends Controller
{
    public function __construct(protected BranchService $branchService) {}

    public function index(Request $request)
    {
        $entity = new BranchEntity(
            search:   (string) $request->string('search')->trim(),
            per_page: $request->integer('per_page', 15),
            page:     $request->integer('page', 1),
        );

        $items = $this->branchService->index($entity);

        return Inertia::render('core/organization/presentations/views/BranchIndex', [
            'branches' => ['data' => $items, 'total' => count($items)],
            'filters'  => [
                'search'   => (string) $request->string('search')->trim(),
                'per_page' => $request->integer('per_page', 15),
                'page'     => $request->integer('page', 1),
            ],
        ]);
    }
}