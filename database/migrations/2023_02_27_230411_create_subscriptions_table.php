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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // 类型
            $table->enum('status', [
                'draft',
                'active',
                'expired',
                'canceled',
            ])->index();

            // 名称
            $table->string('name')->nullable();

            // 计划 ID
            $table->string('plan_id')->index();

            // 配置项目
            $table->json('configuration')->nullable();

            // 价格
            $table->decimal('price', 10)->default(0);

            // 结束时间
            $table->timestamp('expired_at')->nullable();

            // 试用结束时间
            $table->timestamp('trial_ends_at')->nullable();

            // 下个月取消
            $table->boolean('cancel_at_period_end')->default(false)->index();

            // 续费时价格
            $table->decimal('renew_price', 10)->default(0);

            // 模块 ID
            $table->string('module_id')->index()->nullable();
            $table->foreign('module_id')->references('id')->on('modules')->nullOnDelete();

            // 用户 ID
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
