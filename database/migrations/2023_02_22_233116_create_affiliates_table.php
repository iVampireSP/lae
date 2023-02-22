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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();

            $table->ulid()->index();

            // 访问数量
            $table->unsignedBigInteger('visits')->default(0);

            // 累计收益
            $table->decimal('revenue', 10)->default(0);

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('affiliate_users', function (Blueprint $table) {
            $table->id();

            // 从中盈利
            $table->decimal('revenue', 10)->default(0);

            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('user_group_id')->constrained('affiliates')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropColumn('affiliate_id');
        });

        Schema::dropIfExists('affiliate_users');

        Schema::dropIfExists('affiliates');
    }
};
