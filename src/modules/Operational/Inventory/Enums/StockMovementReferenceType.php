<?php

namespace App\Modules\Operational\Inventory\Enums;

enum StockMovementReferenceType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case DISPENSING = 'dispensing';
    case PURCHASE_RETURN = 'purchase_return';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';
    case OPENING_BALANCE = 'opening_balance';

    public static function fromValue(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value) ? self::tryFrom(strtolower(trim($value))) : null;
    }
}
