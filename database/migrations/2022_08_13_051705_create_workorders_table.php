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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            // title
            $table->string('title')->index();

            // content
            $table->text('content');

            // user id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // module id
            $table->string('module_id')->index();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // host id
            $table->unsignedBigInteger('host_id')->index()->nullable();
            $table->foreign('host_id')->references('id')->on('hosts')->onDelete('cascade');

            // status
            $table->enum('status', ['open', 'user_read', 'closed', 'user_replied', 'replied', 'read', 'on_hold', 'in_progress', 'error', 'pending'])->default('pending')->index();


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
        Schema::dropIfExists('workorders');
    }
};
