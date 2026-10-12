<?php

namespace App\Modules\Operational\Inventory\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Unit;

class StockAdjustmentItem extends Model {
    protected $dateFormat = 'U';
    
    use HasUuids, HasSoftDeleteUnique;
    use HasCompanyScope, HasBranchScope;

    protected $table = 'stock_adjustment_items';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'adjustment_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_uuid', 'uuid');
    }
}
