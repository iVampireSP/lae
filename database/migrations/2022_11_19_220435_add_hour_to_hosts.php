<?php

use App\Models\Host;
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
        Schema::table('hosts', function (Blueprint $table) {
            //

            $table->tinyInteger('hour')->index()->nullable()->after('status');
        });

        Host::chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $host->hour = $host->created_at->hour;
                $host->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hosts', function (Blueprint $table) {
            //

            $table->dropColumn('hour');
        });
    }
};
