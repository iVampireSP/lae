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
            $table->timestamp('real_name_verified_at')->nullable()->comment('实名认证时间')->after('email_verified_at');
            $table->string('id_card')->nullable()->comment('身份证号')->after('email_md5');
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
            $table->dropColumn('real_name_verified_at');
            $table->dropColumn('id_card');
        });
    }
};
