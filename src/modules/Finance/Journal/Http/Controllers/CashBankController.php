<?php

namespace App\Modules\Finance\Journal\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashBankController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/CashBankIndex', [
            'filters' => [
                'period'   => (string) $request->string('period', now()->format('Y-m'))->trim(),
                'search'   => (string) $request->string('search')->trim(),
                'page'     => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 10),
            ],
        ]);
    }
}
