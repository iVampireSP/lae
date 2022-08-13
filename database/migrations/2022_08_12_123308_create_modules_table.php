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
            $table->id();

            // name
            $table->string('name')->index();

            // type
            $table->string('type')->index();

            $table->timestamps();
        });

        // if env is local
        if (env('APP_ENV') == 'local') {
            $module = [
                'name' => 'Example Model',
                'type' => 'test',
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
