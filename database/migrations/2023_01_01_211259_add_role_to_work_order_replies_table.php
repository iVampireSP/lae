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
        Schema::table('work_order_replies', function (Blueprint $table) {
            $table->string('role')->default('user')->comment('回复角色')->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('work_order_replies', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
