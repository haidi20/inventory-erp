<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('base_url')->default('https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest');
            $table->string('consumer_uuid')->nullable();
            $table->string('user_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('ppk_code')->nullable();
            $table->boolean('is_active')->default(false);
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_settings');
    }
};

