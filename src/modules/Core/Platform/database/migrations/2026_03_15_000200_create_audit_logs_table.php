<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel audit_logs.
 * Digunakan untuk mencatat jejak audit (siapa, melakukan apa, kapan, dan perubahan datanya).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('branch_uuid')->constrained('branches', 'uuid')->cascadeOnDelete()->comment('ID Cabang kejadian');
            $table->foreignUuid('user_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('ID Pengguna yang melakukan aksi');
            $table->string('event')->index()->comment('Jenis aksi: created, updated, deleted, login, logout');
            $table->string('entity_type')->nullable()->comment('Nama model yang diubah');
            $table->string('entity_uuid')->nullable()->comment('ID record yang diubah');
            $table->json('before')->nullable()->comment('Data sebelum perubahan');
            $table->json('after')->nullable()->comment('Data sesudah perubahan');
            $table->string('ip_address')->nullable()->comment('Alamat IP pelaku');
            $table->string('user_agent')->nullable()->comment('Browser/Perangkat pelaku');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('created_at');
            $table->index(['entity_type', 'entity_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

