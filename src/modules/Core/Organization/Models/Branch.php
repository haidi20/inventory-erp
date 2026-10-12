<?php

namespace App\Modules\Core\Organization\Models;

use App\Modules\Operational\Master\Models\Product;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Database\Factories\Master\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Procurement\Models\Purchase;


class Branch extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;

    protected $table = 'branches';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class, 'branch_uuid', 'uuid');
    }

    public function warehouses()
    {
        return $this->hasMany(Location::class, 'branch_uuid', 'uuid');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'branch_uuid', 'uuid');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return BranchFactory::new();
    }
}
