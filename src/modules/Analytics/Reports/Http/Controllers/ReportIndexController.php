<?php

namespace App\Modules\Analytics\Reports\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportIndexController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('laporan.view')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return Inertia::render('Analytics/Reports/presentations/views/ReportIndex');
    }
}
