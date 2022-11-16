<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drops', function (Blueprint $table) {
            $table->id();

            // payment
            $table->string('payment')->index();

            // amount
            $table->double('amount', 60, 8)->default(0);

            // 汇率
            $table->integer('rate')->default(1);

            // 实际收入
            $table->double('total', 60, 8)->default(0);

            $table->boolean('status')->default(0)->index();


            $table->foreignIdFor(User::class)->index();

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
        Schema::dropIfExists('drops');
    }
};
