<?php

namespace App\Modules\Clinical\Pharmacy\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FarmacyController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyDashboard');
    }

    public function recipe(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyRecipe');
    }

    public function transaction(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyTransaction');
    }

    public function accounting(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyAccounting');
    }

    public function inventory(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyInventory');
    }

    public function stockAdjustment(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockAdjustment');
    }

    public function stockAdjustmentCreate(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockAdjustmentForm', [
            'mode' => 'create'
        ]);
    }

    public function stockAdjustmentEdit(string $uuid): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockAdjustmentForm', [
            'uuid' => $uuid,
            'mode' => 'edit'
        ]);
    }

    public function stockOpname(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockOpname');
    }

    public function stockOpnameCreate(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockOpnameForm', [
            'mode' => 'create'
        ]);
    }

    public function stockOpnameEdit(string $uuid): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockOpnameForm', [
            'uuid' => $uuid,
            'mode' => 'edit'
        ]);
    }

    public function stockMutation(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockMutation');
    }

    public function stockMutationCreate(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockMutationForm', [
            'mode' => 'create'
        ]);
    }

    public function stockMutationEdit(string $uuid): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyStockMutationForm', [
            'uuid' => $uuid,
            'mode' => 'edit'
        ]);
    }

    public function openingBalance(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyOpeningBalance');
    }

    public function openingBalanceCreate(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyOpeningBalanceForm', [
            'mode' => 'create'
        ]);
    }

    public function openingBalanceEdit(string $uuid): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyOpeningBalanceForm', [
            'uuid' => $uuid,
            'mode' => 'edit'
        ]);
    }

    public function serviceDeposit(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyServiceDeposit');
    }

    public function residueReturn(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyResidueReturn');
    }

    public function management(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyManagement');
    }

    public function report(): Response
    {
        return Inertia::render('Clinical/Pharmacy/presentations/views/FarmacyReport');
    }

    // Standard index fallback
    public function index(): Response
    {
        return $this->dashboard();
    }
}
