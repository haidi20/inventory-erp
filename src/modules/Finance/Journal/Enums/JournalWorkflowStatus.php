<?php

namespace App\Modules\Finance\Journal\Enums;

enum JournalWorkflowStatus: string
{
    case DRAFT = 'draft';
    case PENDING_SUPERVISOR = 'pending_supervisor';
    case PENDING_MANAGER = 'pending_manager';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
