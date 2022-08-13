<?php

use App\Models\Module\Module;
use App\Models\Module\Provider;
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
        Schema::create('provider_modules', function (Blueprint $table) {
            $table->id();

            // provider id (on delete cascade)
            $table->foreignIdFor(Provider::class)->index()->onDelete('cascade');

            // module id
            $table->foreignIdFor(Module::class)->index();

            // backend url
            $table->string('backend_url')->nullable();

            // enabled
            $table->boolean('is_enabled')->default(false)->index();


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
        Schema::dropIfExists('provider_modules');
    }
};
