<?php

namespace App\Modules\Finance\Journal\Enums;

/**
 * Enum untuk model referensi jurnal
 */
enum JournalReferenceModel: string
{
    case SALE = 'App\Modules\Operational\Sales\Models\Sale';
    case PURCHASE = 'App\Modules\Operational\Procurement\Models\Purchase';
    case ENCOUNTER = 'App\Modules\Clinical\MedicalRecord\Models\Encounter';
    case REGISTRATION = 'App\Modules\Clinical\Registration\Models\Registration';
    case STOCK_MOVEMENT = 'App\Modules\Operational\Inventory\Models\StockMovement';
    case PATIENT = 'App\Modules\Clinical\Registration\Models\Patient';
    case SUPPLIER = 'App\Modules\Operational\Master\Models\Supplier';
}
