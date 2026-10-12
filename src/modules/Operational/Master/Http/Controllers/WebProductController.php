<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebProductController extends Controller
{
    public function __construct(private ProductService $service)
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $isSuperadmin = $request->user()->isSuperAdmin();
        $companies = $isSuperadmin
            ? $this->service->getCompanyOptions()
            : [];

        return Inertia::render('Operational/Master/presentations/views/ProductIndex', [
            'filters' => collect([
                'search' => (string) $request->string('search')->trim(),
                'search_column' => (string) $request->string('search_column', 'all')->trim(),
                'sort_by' => (string) $request->string('sort_by', 'code')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
                'filter_company_uuid' => $request->string('filter_company_uuid')->trim(),
                'filter_barcode' => $request->string('filter_barcode')->trim(),
                'filter_code' => $request->string('filter_code')->trim(),
                'filter_name' => $request->string('filter_name')->trim(),
                'filter_unit' => $request->string('filter_unit')->trim(),
                'filter_group_uuid' => $request->string('filter_group_uuid')->trim(),
                'filter_keterangan' => $request->string('filter_keterangan')->trim(),
                'filter_kind' => $request->string('filter_kind')->trim(),
                'filter_type_uuid' => $request->string('filter_type_uuid')->trim(),
                'filter_brand_uuid' => $request->string('filter_brand_uuid')->trim(),
                'filter_origin_uuid' => $request->string('filter_origin_uuid')->trim(),
                'filter_grade_uuid' => $request->string('filter_grade_uuid')->trim(),
                'filter_created_by' => $request->string('filter_created_by')->trim(),
                'filter_updated_by' => $request->string('filter_updated_by')->trim(),
                'filter_created_from' => $request->string('filter_created_from')->trim(),
                'filter_created_to' => $request->string('filter_created_to')->trim(),
                'filter_updated_from' => $request->string('filter_updated_from')->trim(),
                'filter_updated_to' => $request->string('filter_updated_to')->trim(),
                'filter_periode' => $request->string('filter_periode')->trim(),
            ])->all(),
            'companies' => $companies,
            'default_company_uuid' => $isSuperadmin ? current_company_uuid() : null,
        ]);
    }

    public function create(Request $request): Response
    {
        $editUuid = (string) $request->string('edit')->trim();
        $product = null;

        if ($editUuid !== '') {
            $product = $this->service->getProductForEdit(
                $editUuid,
                current_branch_uuid(),
                current_company_uuid(),
                $request->user()->isSuperAdmin()
            );

            abort_if(! $product, 404);
        }

        return Inertia::render('Operational/Master/presentations/views/ProductForm', [
            'product' => $product?->toArray(),
            'options' => $this->service->getFormOptions(),
        ]);
    }

    public function edit(string $uuid): RedirectResponse
    {
        return redirect()->route('master.products.create', ['edit' => $uuid]);
    }
}
