<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel pilar utama sistem: users, password_resets, dan sessions.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name')->index()->comment('Nama lengkap pengguna');
            $table->string('email')->comment('Alamat email unik untuk login');
            $table->bigInteger('email_verified_at')->nullable()->comment('Waktu verifikasi email (Unix)');
            $table->string('password')->comment('Hash password pengguna');
            $table->rememberToken()->comment('Token untuk fitur remember me');
            $table->string('username')->nullable()->unique()->comment('Username unik untuk login');
            $table->boolean('is_active')->default(true)->comment('Status aktif akun: true berarti dapat login');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['email', 'deleted_at'], 'idx_users_email_unique_soft');
            $table->index('deleted_at', 'idx_users_deleted_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Email yang meminta reset password');
            $table->string('token')->comment('Token validasi reset password');
            $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan token (Unix)');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID unik sesi browser');
            $table->uuid('user_id')->nullable()->index()->comment('UUID pengguna untuk kebutuhan internal session Laravel');
            $table->uuid('user_uuid')->nullable()->index()->comment('UUID pengguna yang memiliki sesi');
            $table->foreign('user_uuid')->references('uuid')->on('users')->nullOnDelete();
            $table->foreign('user_id')->references('uuid')->on('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable()->comment('Alamat IP pengguna');
            $table->text('user_agent')->nullable()->comment('Informasi browser/perangkat pengguna');
            $table->longText('payload')->comment('Data terenkripsi sesi');
            $table->integer('last_activity')->index()->comment('Timestamp aktivitas terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
