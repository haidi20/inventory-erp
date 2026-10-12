<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('user_type')->nullable();
            $table->string('user_id', 36)->nullable()->comment('UUID user yang melakukan aksi');
            $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan untuk audit trail per faskes');
            $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang untuk audit trail per faskes');
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id', 36)->nullable()->comment('UUID record yang diaudit');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();

            $table->index(['user_id', 'user_type']);
            $table->index('company_uuid', 'idx_audits_company_uuid');
            $table->index('branch_uuid', 'idx_audits_branch_uuid');
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
