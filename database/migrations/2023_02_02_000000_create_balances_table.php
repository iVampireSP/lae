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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable()->index();
            $table->string('trade_id')->nullable()->index();
            $table->string('payment')->nullable()->index();
            $table->decimal('amount', 10)->default(0);
            $table->decimal('remaining_amount', 10)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
