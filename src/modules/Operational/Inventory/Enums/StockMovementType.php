<?php

namespace App\Modules\Operational\Inventory\Enums;

enum StockMovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUST = 'adjust';
}
