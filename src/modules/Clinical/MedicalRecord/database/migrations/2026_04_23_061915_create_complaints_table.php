<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $blueprint) {
            $blueprint->uuid('uuid')->primary();
            $blueprint->string('branch_uuid')->index();
            $blueprint->string('company_uuid')->nullable();

            $blueprint->enum('type', ['pengaduan', 'saran'])->default('pengaduan');
            $blueprint->string('subject');
            $blueprint->text('content');
            $blueprint->enum('status', ['pending', 'proses', 'selesai'])->default('pending');
            $blueprint->boolean('is_anonymous')->default(false);

            // Audit Columns
            $blueprint->bigInteger('created_at')->nullable();
            $blueprint->bigInteger('updated_at')->nullable();
            $blueprint->bigInteger('deleted_at')->unsigned()->default(0);
            $blueprint->string('created_by')->nullable();
            $blueprint->string('updated_by')->nullable();
            $blueprint->string('deleted_by')->nullable();

            $blueprint->index('company_uuid');
            $blueprint->index(['company_uuid', 'deleted_at']);
            $blueprint->index(['type', 'status']);        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
