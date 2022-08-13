<?php

use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
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
        Schema::create('workorder_replies', function (Blueprint $table) {
            $table->id();

            // content
            $table->text('content');

            // workorder id (on delete cascade)
            $table->unsignedBigInteger('workorder_id')->index();
            $table->foreign('workorder_id')->references('id')->on('workorders')->onDelete('cascade');

            // user id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            
            $table->boolean('is_pending')->default(false)->index();


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
        Schema::dropIfExists('workorder_replies');
    }
};
