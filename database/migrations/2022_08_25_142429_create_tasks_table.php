<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::dropIfExists('tasks');

        Schema::create(
            'tasks',
            function (Blueprint $table) {
                $table->uuid('id')->primary()->unique();

                $table->string('title');

                // progress (max 100)
                $table->integer('progress')->default(0);

                // status
                $table->enum('status', ['pending', 'done', 'success', 'failed', 'error', 'cancelled', 'processing', 'need_operation'])->index();

                // user id
                $table->unsignedBigInteger('user_id')->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                // host id
                $table->unsignedBigInteger('host_id')->index();
                $table->foreign('host_id')->references('id')->on('hosts')->onDelete('cascade');

                $table->timestamps();

            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('tasks');
    }
};
