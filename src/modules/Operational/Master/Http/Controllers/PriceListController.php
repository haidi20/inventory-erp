<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PriceListController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Operational/Master/presentations/views/PriceListIndex', [
            'filters' => [
                'search'    => (string) $request->string('search')->trim(),
                'page'      => $request->integer('page', 1),
                'per_page'  => $request->integer('per_page', 10),
            ],
        ]);
    }
}
