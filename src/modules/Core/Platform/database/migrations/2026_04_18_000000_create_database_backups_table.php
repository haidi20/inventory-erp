<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->unsignedBigInteger('id')->nullable();
            $table->string('name');
            $table->string('path');
            $table->bigInteger('size');
            $table->string('status')->default('ready');
            $table->string('source')->default('system'); // system, user
            $table->string('history_type')->default('manual'); // manual, system
            $table->text('notes')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            // Indexing for datatable performance
            $table->index(['history_type', 'created_at']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
