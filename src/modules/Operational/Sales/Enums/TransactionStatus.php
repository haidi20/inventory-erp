<?php

namespace App\Modules\Operational\Sales\Enums;

enum TransactionStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case POSTED = 'posted';
    case CANCELLED = 'cancelled';
    case CLOSED = 'closed';
}
