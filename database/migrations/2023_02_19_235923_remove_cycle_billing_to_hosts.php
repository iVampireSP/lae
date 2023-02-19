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
            $table->dropColumn('next_due_at');
            $table->dropColumn('billing_cycle');
            $table->dropColumn('last_paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        echo 'This migration cannot be reversed.'.PHP_EOL;
    }
};
