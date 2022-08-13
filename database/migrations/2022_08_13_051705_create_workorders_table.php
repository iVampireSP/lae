<?php

use App\Models\Module\Module;
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
        Schema::create('workorders', function (Blueprint $table) {
            $table->id();

            // title
            $table->string('title')->index();

            // content
            $table->text('content')->nullable();

            // host id (optional) and null on delete
            $table->foreignIdFor(Module::class)->nullable()->onDelete('set null');

            // user id
            $table->foreignIdFor(User::class)->index();

            // provider id
            $table->foreignIdFor(ProviderModule::class)->index()->onDelete('set null');

            // status
            $table->enum('status', ['open', 'closed', 'user_replied', 'replied', 'on_hold', 'in_progress', 'error', 'pending'])->default('pending')->index();



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
