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
            $table->foreignIdFor(User::class)->index();

            // provider module id
            $table->foreignIdFor(ProviderModule::class)->index();

            // host id
            $table->foreignIdFor(Host::class)->index();

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
