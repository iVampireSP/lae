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
        Schema::table('hosts', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            // rollback
            $table->dropForeign(['module_id']);
        });
    }
};
