<?php

namespace App\Modules\Operational\Inventory\Enums;

/**
 * Enum untuk menentukan tipe sumber stok (polymorphic).
 * Dipakai di tabel item_stocks kolom source_type.
 * 
 * Mapping ke model Laravel via Relation::morphMap() di AppServiceProvider.
 */
enum StockSourceType: string
{
    case Location = 'location';
    case Counter  = 'counter';
    case Vehicle  = 'vehicle';
    case Project  = 'project';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match($this) {
            self::Location => 'Gudang / Lokasi',
            self::Counter  => 'Counter / Kasir',
            self::Vehicle  => 'Kendaraan',
            self::Project  => 'Proyek',
        };
    }

    /**
     * Default type untuk backward compatibility.
     */
    public static function default(): self
    {
        return self::Location;
    }
}