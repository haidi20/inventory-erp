<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master gudang per cabang
            if (!Schema::hasTable('locations')) {
                Schema::create('locations', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');

                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('type', 50)->default('warehouse')->comment('Tipe lokasi: warehouse, store, counter, custom.');
                    $table->string('code', 50)->comment('Penjelasan kolom code.');
                    $table->string('name', 255)->comment('Penjelasan kolom name.');
                    $table->text('description')->nullable()->comment('Penjelasan kolom description.');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Penjelasan kolom is_active.');
                    $table->boolean('can_sell')->nullable()->default(true)->comment('Penjelasan kolom can_sell.');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penjelasan kolom is_primary.');
                    $table->char('address_uuid', 36)->nullable()->comment('UUID alamat gudang.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_branch_location');
                    $table->index('deleted_at', 'idx_locations_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('locations');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};




