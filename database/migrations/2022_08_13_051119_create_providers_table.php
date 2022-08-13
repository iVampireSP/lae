<?php

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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();

            // name
            $table->string('name')->index();

            // api_token
            $table->string('api_token')->index()->unique();

            $table->timestamps();
        });

        // if env is local
        if (env('APP_ENV') == 'local') {
            $provider = [
                'name' => 'Example Provider',
                'api_token' => 123456,
            ];

            Provider::create($provider);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
};
