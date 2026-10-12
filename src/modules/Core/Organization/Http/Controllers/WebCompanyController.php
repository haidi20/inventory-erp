<?php

namespace App\Modules\Core\Organization\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Services\CompanyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebCompanyController extends Controller
{
    public function __construct(protected CompanyService $companyService) {}

    public function index(Request $request)
    {
        $entity = new CompanyEntity(
            search:        (string) $request->string('search')->trim(),
            search_column: (string) $request->string('search_column', 'all')->trim(),
            per_page:      $request->integer('per_page', 15),
            page:          $request->integer('page', 1),
        );

        $items = $this->companyService->index($entity);

        return Inertia::render('core/organization/presentations/views/CompanyIndex', [
            'companies' => ['data' => $items, 'total' => count($items)],
            'filters'   => [
                'search'        => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'per_page'      => $request->integer('per_page', 15),
                'page'          => $request->integer('page', 1),
            ],
        ]);
    }
}