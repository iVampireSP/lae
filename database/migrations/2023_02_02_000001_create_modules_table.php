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
        Schema::create('modules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->index();
            $table->decimal('balance', 20, 4)->default(0)->index();
            $table->string('api_token')->nullable()->unique();
            $table->string('url')->nullable()->index();
            $table->string('wecom_key')->nullable()->comment('企业微信机器人 key');
            $table->enum('status', ['up', 'down', 'maintenance'])->default('down')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
