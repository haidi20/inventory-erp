<?php

namespace App\Modules\Core\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanCard extends Model
{
    protected $dateFormat = 'U';

    protected $fillable = [
        'title',
        'label',
        'assignee',
        'photos',
        'column_name',
        'position',
    ];

    protected $casts = [
        'photos' => 'array',
    ];
}
