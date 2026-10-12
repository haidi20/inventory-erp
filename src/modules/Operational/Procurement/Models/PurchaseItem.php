<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Modules\Core\Organization\Models\Branch;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Master\Models\Product;

class PurchaseItem extends Model {
    protected $dateFormat = 'U';
    
    use HasUuids, HasSoftDeleteUnique;
    use HasCompanyScope, HasBranchScope;

    protected $table = 'purchase_items';

    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }
}

