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
        Schema::create('product_configurable_options', function (Blueprint $table) {
            $table->id();

            // 显示名称
            $table->string('display_name')->nullable()->index();
            // 名称
            $table->string('name')->nullable()->index();

            // 是否隐藏
            $table->boolean('is_hidden')->default(false);

            // 类型(Dropdown 下拉菜单, Radio 单选按钮, Boolean 是否，Quantity 数量))，默认为Dropdown
            $table->string('type')->default('Dropdown');

            // 最小数量
            $table->integer('min_qty')->default(0);
            // 最大数量
            $table->integer('max_qty')->default(0);

            // 单位
            $table->string('unit')->nullable();

            // 数量阶梯
            $table->string('qty_step')->nullable();

            // 是否支持降级
            $table->boolean('is_allow_degrade')->default(false);

            // 排序
            $table->integer('order')->default(0);

            // 备注
            $table->string('notes')->nullable();

            // 可配置选项组
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->foreign('group_id')->references('id')->on('product_configurable_options');

            // 由哪个管理员创建
            // $table->unsignedBigInteger('admin_id')->nullable()->index();
            // $table->foreign('admin_id')->references('id')->on('admins');

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
        Schema::dropIfExists('config_options');
    }
};
