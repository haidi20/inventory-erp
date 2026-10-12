<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Master\Models\Product;

class PurchaseOrderItem extends Model {
    protected $dateFormat = 'U';
    
    use HasUuids, HasSoftDeleteUnique;
    use HasCompanyScope, HasBranchScope;

    protected $table = 'purchase_order_items';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $guarded = [];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}

