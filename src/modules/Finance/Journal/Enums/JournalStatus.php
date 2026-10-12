<?php

namespace App\Modules\Finance\Journal\Enums;

enum JournalStatus: string
{
    case UNPOSTED = 'unposted';
    case POSTED = 'posted';
    case DRAFT = 'draft';
    case VOIDED = 'voided';
}
