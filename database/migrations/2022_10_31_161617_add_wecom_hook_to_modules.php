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
            $table->string('wecom_key')->nullable()->comment('企业微信机器人 key');
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

            // drop if exists
            if (Schema::hasColumn('modules', 'wecom_key')) {
                $table->dropColumn('wecom_key');
            }
        });
    }
};
