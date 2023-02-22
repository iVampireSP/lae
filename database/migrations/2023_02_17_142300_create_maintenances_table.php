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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            // 维护名称
            $table->string('name')->index();

            // 内容
            $table->text('content')->nullable();

            // 模块 ID
            $table->string('module_id')->index()->nullable();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('set null');

            // 开始于
            $table->dateTime('start_at')->nullable()->index();

            $table->dateTime('end_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
