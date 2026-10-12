<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait HasSoftDeleteUnique
 * 
 * Ekstensi dari SoftDeletes Laravel untuk mendukung indeks unik komposit.
 * Menggunakan nilai 0 (bukan NULL) untuk data aktif agar kompatibel dengan 
 * indeks unik di berbagai database (MySQL/SQLite).
 */
trait HasSoftDeleteUnique
{
    use SoftDeletes;

    /**
     * Menimpa bootSoftDeletes untuk mencegah penggunaan SoftDeletingScope bawaan Laravel
     * yang menggunakan whereNull('deleted_at').
     */
    public static function bootSoftDeletes()
    {
        // Kosongkan agar tidak menambah scope whereNull
    }

    /**
     * Scope a query to include soft-deleted records.
     */
    public function scopeWithTrashed(Builder $query)
    {
        return $query->withoutGlobalScope('HasSoftDeleteUnique');
    }

    /**
     * Boot trait HasSoftDeleteUnique.
     */
    public static function bootHasSoftDeleteUnique()
    {
        static::addGlobalScope('HasSoftDeleteUnique', function (Builder $builder) {
            $builder->where($builder->getModel()->getQualifiedDeletedAtColumn(), 0);
        });

        static::creating(function ($model) {
            if (is_null($model->{$model->getDeletedAtColumn()})) {
                $model->{$model->getDeletedAtColumn()} = 0;
            }
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->{$model->getDeletedAtColumn()} = time();
            }
        });
    }

    /**
     * Menimpa metode bawaan untuk memastikan nilai integer disimpan.
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = time();

        $columns = [$this->getDeletedAtColumn() => $time];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $time;
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }

    /**
     * Menimpa metode restore untuk mengembalikan deleted_at ke 0.
     */
    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = 0;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Inisialisasi trait.
     * Mengubah format tanggal ke Unix Timestamp jika belum disetel.
     */
    public function initializeHasSoftDeleteUnique()
    {
        $this->dateFormat = 'U';
    }

    /**
     * Mendapatkan query builder yang menyaring data aktif (deleted_at = 0).
     * Menimpa scope bawaan SoftDeletes.
     */
    public function scopeWithoutTrashed(Builder $query)
    {
        return $query->where($this->getQualifiedDeletedAtColumn(), 0);
    }

    /**
     * Mendapatkan query builder yang menyaring data terhapus (deleted_at > 0).
     * Menimpa scope bawaan SoftDeletes.
     */
    public function scopeOnlyTrashed(Builder $query)
    {
        return $query->where($this->getQualifiedDeletedAtColumn(), '>', 0);
    }

    /**
     * Support builder-level restore() for models using the custom deleted_at = 0 convention.
     */
    public function scopeRestore(Builder $query)
    {
        return $query->update([$query->getModel()->getDeletedAtColumn() => 0]);
    }
}
