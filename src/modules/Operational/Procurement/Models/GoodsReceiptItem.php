<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Master\Models\Product;

class GoodsReceiptItem extends Model {
    protected $dateFormat = 'U';

    use HasUuids, HasSoftDeleteUnique;
    use HasCompanyScope, HasBranchScope;

    protected $table = 'goods_receipt_items';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_uuid', 'uuid');
    }
}
