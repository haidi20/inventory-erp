<?php

namespace App\Modules\Operational\Master\Models;


use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Database\Factories\Operational\ItemConversionFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Unit;

/**
 * Model ItemConversion
 *
 * Definisi konversi antar satuan barang (misal: 1 Box ke 10 Strip) untuk akurasi inventori.
 */
class ItemConversion extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'item_conversions';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_uuid', 'uuid');
    }

    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, 'from_unit_uuid', 'uuid');
    }

    public function toUnit()
    {
        return $this->belongsTo(Unit::class, 'to_unit_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
    protected static function newFactory(): Factory
    {
        return ItemConversionFactory::new();
    }
}
