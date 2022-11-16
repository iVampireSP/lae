<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->string('id')->index()->primary()->unique();

            $table->string('name')->index();

            // api token
            $table->string('api_token')->nullable()->unique()->index();
        });

        // if env is local
        if (env('APP_ENV') == 'local') {
            $module = [
                'id' => 'test',
                'name' => 'Example Module',
                'api_token' => '123456'
            ];

            Module::create($module);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
