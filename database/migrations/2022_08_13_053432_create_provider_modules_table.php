<?php

use App\Models\Module\Module;
use App\Models\Module\Provider;
use App\Models\Module\ProviderModule;
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

            // api_token
            // $table->string('api_token')->index()->unique();

            // backend url
            $table->string('backend_url')->nullable();

            // enabled
            $table->boolean('is_enabled')->default(false)->index();


            $table->timestamps();
        });

        // if env is local
        if (env('APP_ENV') == 'local') {
            $provider =  [
                'provider_id' => 1,
                'module_id' => 1,
                'is_enabled' => 1,
                // 'api_token' => 123456,
            ];

            ProviderModule::create($provider);
        }
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
