<?php

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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->unsignedTinyInteger('progress')->nullable();
            $table->enum('status', ['pending', 'done', 'success', 'failed', 'error', 'cancelled', 'processing', 'need_operation'])->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('host_id')->index();
            $table->string('module_id')->nullable()->index();
            $table->timestamps();
        });

        // 设置存储引擎为 MEMORY
        DB::statement('ALTER TABLE tasks ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
