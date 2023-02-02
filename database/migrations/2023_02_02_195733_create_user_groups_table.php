<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('名称');
            $table->string('color')->nullable()->comment('颜色');
            $table->integer('discount')->default(100)->comment('优惠百分比');
            $table->boolean('exempt')->default(false)->comment('暂停/终止豁免权');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['user_group_id'])->references(['id'])->on('user_groups')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_user_group_id_foreign');
        });

        Schema::dropIfExists('user_groups');
    }
};
