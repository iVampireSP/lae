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
        Schema::create('product_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();

            // 标题
            $table->string('title')->nullable();

            // 描述
            $table->string('description')->nullable();

            // 是否隐藏
            $table->boolean('is_hidden')->default(false);

            // 排序
            $table->integer('order')->default(0);

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
        Schema::dropIfExists('product_groups');
    }
};
