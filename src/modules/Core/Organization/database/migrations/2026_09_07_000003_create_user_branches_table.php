<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot table: 1 User bisa akses MULTIPLE Branches (di bawah MULTIPLE Companies).
 * Menyimpan relasi many-to-many antara users dan branches.
 * Kolom is_default menandai branch yang aktif saat user login.
 * company_uuid ada di pivot karena 1 user bisa punya branch di company A dan company B.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_branches', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid')->index();
            $table->uuid('company_uuid')->index();
            $table->uuid('branch_uuid')->index();
            $table->boolean('is_default')->default(false)->comment('Branch aktif saat login');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            // Audit fields
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);

            // Foreign keys
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('company_uuid')->references('uuid')->on('companies')->cascadeOnDelete();
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();

            // Unique constraint: 1 user tidak bolehduplikat ke 1 company+branch
            $table->unique(['user_uuid', 'company_uuid', 'branch_uuid'], 'idx_user_branches_unique');
            // Index untuk cari branch default per user+company
            $table->index(['user_uuid', 'company_uuid', 'is_default'], 'idx_user_branches_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};
