<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('item_images')) {
            return;
        }

        Schema::create('item_images', function (Blueprint $table): void {
            $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
            $table->char('item_uuid', 36)->comment('Relasi ke tabel items');
            $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik data');
            $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik data');
            $table->string('path')->comment('Path file gambar item');
            $table->unsignedInteger('sort_order')->default(0)->comment('Urutan tampil gambar');
            $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
            $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
            $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
            $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
            $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
            $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
            $table->index('item_uuid', 'idx_item_images_item');
            $table->index('deleted_at', 'idx_item_images_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_images');
    }
};
