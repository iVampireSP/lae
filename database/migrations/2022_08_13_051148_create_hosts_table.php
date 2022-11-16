<?php

use App\Models\Module\ProviderModule;
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
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();

            // name
            $table->string('name')->index();

            // provider id
            $table->string('module_id')->index();
            // $table->foreign('module_id')->references('id')->on('modules')->onDelete('set null');

            // user_id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            // price
            $table->double('price', 60, 8)->index();

            // config
            $table->json('configuration')->nullable();

            // status
            $table->enum('status', ['running', 'stopped', 'error', 'suspended', 'pending'])->default('pending')->index();

            // soft delete
            $table->softDeletes();


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
        Schema::dropIfExists('hosts');
    }
};
