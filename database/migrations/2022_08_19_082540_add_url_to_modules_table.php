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
        Schema::table('modules', function (Blueprint $table) {
            //
            $table->string('url')->nullable()->after('api_token')->index();
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
            $table->dropColumn('url');
        });
    }
};
