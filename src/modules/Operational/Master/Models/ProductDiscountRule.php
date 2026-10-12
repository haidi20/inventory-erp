<?php

namespace App\Modules\Operational\Master\Models;


use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductDiscountRule extends Model
{
    use HasBranchScope;
    use HasCompanyScope;
    use HasUuids;

    protected $table = 'item_discount_rules';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = [];
}
