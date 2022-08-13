<?php

use App\Models\Module\ProviderModule;
use App\Models\User;
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
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();

            // name
            $table->string('name')->index();

            // provider id
            $table->foreignIdFor(ProviderModule::class)->index();

            // user_id
            $table->foreignIdFor(User::class)->index();

            // price
            $table->double('price', 8, 6)->index();

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
