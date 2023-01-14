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
            // 将 price 和 managed_price 改成 decimal
            $table->decimal('price')->change();
            $table->decimal('managed_price')->change();
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
            // 回滚
            $table->unsignedDouble('price', 10)->change();
            $table->unsignedDouble('managed_price', 10)->change();
        });
    }
};
