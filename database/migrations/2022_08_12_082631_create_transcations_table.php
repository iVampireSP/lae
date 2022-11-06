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
    public function up()
    {
        Schema::create('transcations', function (Blueprint $table) {
            $table->id();

            // remote transaction id
            $table->string('remote_id')->index();

            // drops id
            $table->unsignedBigInteger('drops_id')->index();
            $table->foreign('drops_id')->references('id')->on('drops');

            // payment
            $table->string('payment')->index();

            // amount
            $table->double('amount', 60, 8)->default(0);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcations');
    }
};
