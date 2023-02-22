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
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('module_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('price', 10)->index();
            $table->decimal('managed_price', 10)->nullable()->index();
            $table->json('configuration')->nullable();
            $table->enum('status', ['running', 'stopped', 'error', 'suspended', 'pending', 'unavailable', 'locked'])->default('pending')->index();
            $table->tinyInteger('hour_at')->nullable()->index();
            $table->tinyInteger('minute_at')->nullable()->index();
            $table->timestamp('suspended_at')->nullable()->index();
            $table->timestamp('unavailable_at')->nullable()->comment('不可用时间');
            $table->timestamp('locked_at')->nullable()->comment('锁定时间');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign(['module_id'])->references(['id'])->on('modules')->onUpdate('CASCADE')->onDelete('NO ACTION');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};
