<?php

namespace App\Modules\Operational\Inventory\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Inertia\Inertia;

class InventoryIndexController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('inventory.view')) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return Inertia::render('Operational/Inventory/presentations/views/InventoryIndex');
    }
}
