<?php

namespace App\Modules\Operational\Inventory\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;

class StockMovement extends Model {
    protected $dateFormat = 'U';
    
    use HasUuids, HasCompanyScope, HasBranchScope;

    protected $table = 'stock_movements';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
