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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            $table->uuid()->nullable()->unique();
            $table->string('title')->index();
            $table->text('content');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('module_id')->nullable()->index();
            $table->unsignedBigInteger('host_id')->nullable()->index();
            $table->enum('status', ['open', 'user_read', 'closed', 'user_replied', 'replied', 'read', 'on_hold', 'in_progress', 'error', 'pending'])->default('pending')->index();
            $table->string('ip')->nullable();
            $table->boolean('notify')->default(true)->comment('是否通知');
            $table->timestamps();

            $table->foreign(['host_id'])->references(['id'])->on('hosts')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['module_id'])->references(['id'])->on('modules')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
