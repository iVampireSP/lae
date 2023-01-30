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
        // 坏，为什么得这么写
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('progress');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('progress');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('progress')->after('title');
        });
    }
};
