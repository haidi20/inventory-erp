<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Master\Models\Product;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model ProductPurchase
 *
 * Informasi histori dan preferensi pembelian produk dari berbagai supplier.
 */
class ProductPurchase extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'product_purchases';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_uuid', 'uuid');
    }
}



