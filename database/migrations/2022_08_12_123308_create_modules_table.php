<?php

use App\Models\Module\Module;
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
        Schema::create('modules', function (Blueprint $table) {
            $table->string('id')->index()->primary()->unique();

            // type
            $table->string('type')->index();

            // api token
            $table->string('api_token')->nullable()->unique()->index();
        });

        // if env is local
        if (env('APP_ENV') == 'local') {
            $module = [
                'id' => 'Example Model',
                'type' => 'test',
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
