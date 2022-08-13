<?php

use App\Models\Module\Module;
use App\Models\Module\ProviderModule;
use App\Models\User;
use App\Models\User\Host;
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
        Schema::create('workorders', function (Blueprint $table) {
            $table->id();

            // title
            $table->string('title')->index();

            // content
            $table->text('content');

            // user id
            $table->foreignIdFor(User::class)->index();

            // host id
            $table->foreignIdFor(Host::class)->index()->nullable();

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
