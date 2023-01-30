<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `hosts` CHANGE `status` `status` ENUM('running','stopped','error','suspended','pending','unavailable', 'locked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");

        \Illuminate\Support\Facades\Schema::table('hosts', function (Blueprint $table) {
            $table->timestamp('unavailable_at')->nullable()->comment('不可用时间')->after('suspended_at');
            $table->timestamp('locked_at')->nullable()->comment('锁定时间')->after('unavailable_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `hosts` CHANGE `status` `status` ENUM('running','stopped','error','suspended','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");

        \Illuminate\Support\Facades\Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('unavailable_at');
            $table->dropColumn('locked_at');
        });
    }
};
