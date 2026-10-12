<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_cards', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('label')->nullable();
            $table->string('assignee')->nullable();
            $table->json('photos')->nullable();
            $table->string('column_name', 50)->default('todo');
            $table->unsignedInteger('position')->default(0);
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_cards');
    }
};
