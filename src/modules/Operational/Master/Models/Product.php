<?php

namespace App\Modules\Operational\Master\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\LookupValue;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Master\ProductFactory;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Models\ProductUnit;
use App\Modules\Operational\Master\Models\ProductOpeningBalance;
use App\Modules\Operational\Master\Models\ProductAccount;
use App\Modules\Operational\Master\Models\ProductDiscountRule;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\Unit;


class Product extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasCompanyScope, HasBranchScope, HasFactory, HasUuids, HasSoftDeleteUnique;

    protected $table = 'items';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'product_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    /**
     * Lookups
     */
    public function categoryLookup()
    {
        return $this->belongsTo(LookupValue::class, 'item_category_lookup_uuid', 'uuid');
    }

    public function groupLookup()
    {
        return $this->belongsTo(LookupValue::class, 'item_group_lookup_uuid', 'uuid');
    }

    public function typeLookup()
    {
        return $this->belongsTo(LookupValue::class, 'item_type_lookup_uuid', 'uuid');
    }

    public function brandLookup()
    {
        return $this->belongsTo(LookupValue::class, 'brand_lookup_uuid', 'uuid');
    }

    public function originLookup()
    {
        return $this->belongsTo(LookupValue::class, 'origin_lookup_uuid', 'uuid');
    }

    public function gradeLookup()
    {
        return $this->belongsTo(LookupValue::class, 'grade_lookup_uuid', 'uuid');
    }

    /**
     * Details & Stocks
     */
    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'item_uuid', 'uuid');
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'item_uuid', 'uuid');
    }

    public function accounts()
    {
        return $this->hasMany(ProductAccount::class, 'item_uuid', 'uuid')->with('account');
    }

    public function openingBalances()
    {
        return $this->hasMany(ProductOpeningBalance::class, 'item_uuid', 'uuid');
    }

    public function discountRules()
    {
        return $this->hasMany(ProductDiscountRule::class, 'item_uuid', 'uuid');
    }

    public function stocks()
    {
        return $this->hasMany(ProductLocation::class, 'item_uuid', 'uuid');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'item_uuid', 'uuid')->orderBy('sort_order');
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_uuid', 'uuid');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by', 'uuid');
    }

    public function getStockAttribute()
    {
        return $this->stocks()->sum('on_hand_qty');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return ProductFactory::new();
    }
}
