<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerSupplierController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function customers(Request $request): Response
    {
        return Inertia::render('Operational/Master/presentations/views/SupplierIndex', [
            'filters' => [
                'search'         => (string) $request->string('search')->trim(),
                'search_column'  => (string) $request->string('search_column', 'all')->trim(),
                'sort_by'        => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page'           => $request->integer('page', 1),
                'per_page'       => $request->integer('per_page', 10),
            ],
        ]);
    }

    public function suppliers(Request $request): Response
    {
        return Inertia::render('Operational/Master/presentations/views/SupplierIndex', [
            'filters' => [
                'search'         => (string) $request->string('search')->trim(),
                'search_column'  => (string) $request->string('search_column', 'all')->trim(),
                'sort_by'        => (string) $request->string('sort_by', 'name')->trim(),
                'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
                'page'           => $request->integer('page', 1),
                'per_page'       => $request->integer('per_page', 10),
            ],
        ]);
    }
}
