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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();

            // order id
            $table->string('order_id')->nullable()->index();

            // trade id
            $table->string('trade_id')->nullable()->index();

            // payment
            $table->string('payment')->nullable()->index();

            // amount
            $table->decimal('amount', 10)->default(0);

            // paid_at
            $table->timestamp('paid_at')->nullable();

            // user id
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
