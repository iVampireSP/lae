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
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();

            // 名称
            $table->string('name')->comment('名称')->index();

            // 颜色
            $table->string('color')->comment('颜色')->nullable();

            // 优惠百分比
            $table->integer('discount')->comment('优惠百分比')->default(100);

            // 暂停/终止豁免权
            $table->boolean('exempt')->comment('暂停/终止豁免权')->default(false);

            $table->timestamps();
        });

        // Schema::table('users', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_group_id')->nullable()->comment('用户组')->index()->after('banned_reason');
        //     $table->foreign('user_group_id')->references('id')->on('user_groups')->onDelete('set null');
        // });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_group_id')->nullable()->comment('用户组')->index()->after('banned_reason');
            $table->foreign('user_group_id')->references('id')->on('user_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     // drop column if exists
        //     if (Schema::hasColumn('users', 'user_group_id')) {
        //         $table->dropForeign('users_user_group_id_foreign');
        //
        //         $table->dropColumn('user_group_id');
        //     }
        // });
        //

        Schema::table('users', function (Blueprint $table) {
            // drop column if exists
            if (Schema::hasColumn('users', 'user_group_id')) {
                $table->dropForeign('users_user_group_id_foreign');

                $table->dropColumn('user_group_id');
            }
        });


        Schema::dropIfExists('user_groups');
    }
};
