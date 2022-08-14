<?php

use App\Models\User;
use App\Models\User\Host;
use App\Models\Module\ProviderModule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            // progress (max 100)
            $table->integer('progress')->default(0);

            // status
            $table->enum('status', ['pending', 'done', 'success', 'failed', 'error', 'cancelled', 'processing', 'need_operation'])->index();

            // user id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('module_id')->index();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // host id
            $table->unsignedBigInteger('host_id')->index();
            $table->foreign('host_id')->references('id')->on('hosts')->onDelete('cascade');

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
        Schema::dropIfExists('tasks');
    }
};
