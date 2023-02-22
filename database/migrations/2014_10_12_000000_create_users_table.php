<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->string('name')->index();
            $table->string('real_name')->nullable();
            $table->string('email')->unique();
            $table->string('email_md5')->nullable()->comment('邮箱 MD5');
            $table->string('id_card')->nullable()->comment('身份证号');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('real_name_verified_at')->nullable()->index()->comment('实名认证时间');
            $table->date('birthday_at')->nullable()->index();
            $table->string('password')->nullable();
            $table->decimal('balance', 20, 4)->default(0);
            $table->dateTime('banned_at')->nullable()->index()->comment('封禁时间');
            $table->string('banned_reason')->nullable();
            $table->unsignedBigInteger('user_group_id')->nullable()->index()->comment('用户组');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
