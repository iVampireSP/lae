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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 20, 4)->change();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->decimal('balance', 20, 4)->change();
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
            $table->decimal('balance', 10)->change();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->decimal('balance', 10)->change();
        });
    }
};
