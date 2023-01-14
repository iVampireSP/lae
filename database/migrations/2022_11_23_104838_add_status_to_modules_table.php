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
        Schema::table('modules', function (Blueprint $table) {
            //
            $table->enum('status', ['up', 'down', 'maintenance'])->index()->default('down')->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            //

            $table->dropColumn('status');
        });
    }
};
