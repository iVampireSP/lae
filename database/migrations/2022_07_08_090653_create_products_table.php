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
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();
            // 介绍
            $table->string('description')->nullable();
            // 是否隐藏
            $table->boolean('is_hidden')->default(false);
            
            // 产品组
            $table->unsignedBigInteger('product_group_id')->nullable()->index();
            $table->foreign('product_group_id')->references('id')->on('product_groups')->onDelete('set null');

            // 库存控制
            $table->boolean('is_stock_control')->default(false);

            // 库存
            $table->integer('stock')->default(0);

            // 服务器模块
            $table->string('module')->nullable()->index();

            // 排序
            $table->integer('order')->default(0);

            // 价格
            $table->decimal('price', 10, 2)->default(0);

            // 是否下架
            $table->boolean('is_retired')->default(false);

            // 是否推荐
            $table->boolean('is_recommended')->default(false);

            /* 价格部分 */
            // 设置费
            $table->decimal('setup_fee', 10, 2)->default(0);

            // 每月付费
            $table->decimal('monthly_fee', 10, 2)->default(0);

            // 季度
            $table->decimal('quarterly_fee', 10, 2)->default(0);

            // 半年付费
            $table->decimal('half_yearly_fee', 10, 2)->default(0);

            // 年付费
            $table->decimal('yearly_fee', 10, 2)->default(0);

            // 小时付费
            $table->decimal('hourly_fee', 10, 2)->default(0);

            // 可配置选项组 ID
            $table->unsignedBigInteger('product_configurable_option_group_id')->nullable()->index();
            $table->foreign('product_configurable_option_group_id')->references('id')->on('product_configurable_options');

            // 由哪个管理员创建
            // $table->unsignedBigInteger('admin_id')->nullable()->index();
            // $table->foreign('admin_id')->references('id')->on('admins');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
