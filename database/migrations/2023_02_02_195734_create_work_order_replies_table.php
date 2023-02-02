<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('work_order_replies', function (Blueprint $table) {
            $table->id();

            $table->text('content');
            $table->string('ip')->nullable();
            $table->unsignedBigInteger('work_order_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('module_id')->nullable()->index();
            $table->string('role')->default('user')->comment('回复角色');
            $table->boolean('is_pending')->default(false)->index();
            $table->timestamps();

            $table->foreign(['module_id'])->references(['id'])->on('modules')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['work_order_id'])->references(['id'])->on('work_orders')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_replies');
    }
};
