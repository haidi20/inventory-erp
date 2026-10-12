<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel satusehat_settings.
 * Digunakan untuk konfigurasi integrasi resmi dengan portal SATUSEHAT Kemenkes RI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satusehat_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('org_uuid')->nullable()->comment('Organization ID dari portal SATUSEHAT (Disediakan Kemenkes)');
            $table->string('client_uuid')->nullable()->comment('Client ID untuk autentikasi OAuth2');
            $table->text('client_secret')->nullable()->comment('Client Secret untuk autentikasi OAuth2');
            $table->string('base_url')->default('https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1')->comment('URL API FHIR Base');
            $table->string('auth_url')->default('https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken')->comment('URL Auth Service Base');
            $table->boolean('is_active')->default(false)->comment('Flag status aktif integrasi');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
        });

        // Insert 1 row default (singleton)
        \DB::table('satusehat_settings')->insert([
            'uuid'          => (string) \Illuminate\Support\Str::uuid(),
            'org_uuid'      => null,
            'client_uuid'     => null,
            'client_secret' => null,
            'base_url'      => 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1',
            'auth_url'      => 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken',
            'is_active'     => false,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('satusehat_settings');
    }
};

