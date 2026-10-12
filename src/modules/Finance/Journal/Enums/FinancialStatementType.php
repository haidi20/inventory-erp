<?php

namespace App\Modules\Finance\Journal\Enums;

enum FinancialStatementType: string
{
    case BALANCE_SHEET = 'balance_sheet';
    case INCOME_STATEMENT = 'income_statement';
}
