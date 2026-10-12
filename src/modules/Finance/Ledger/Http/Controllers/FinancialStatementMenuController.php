<?php

namespace App\Modules\Finance\Ledger\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialStatementMenuController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): Response
    {
        return Inertia::render('Finance/Ledger/presentations/views/FinancialStatementMenuIndex');
    }
}
