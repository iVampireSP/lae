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
        Schema::create('product_configurable_option_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable()->index();
            $table->string('description')->nullable()->index();

            // soft delete
            $table->softDeletes();

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
        Schema::dropIfExists('config_option_groups');
    }
};
