<?php

use App\Models\Host;
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
        Schema::table('hosts', function (Blueprint $table) {
            //

            $table->tinyInteger('minute_at')->index()->nullable()->after('hour_at');
        });

        echo PHP_EOL . '将开始刷新主机的分钟数...';
        Host::chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                echo '刷新: ' . $host->id . PHP_EOL;
                $host->minute_at = $host->created_at->minute;
                $host->save();
            }
        });
        echo ' 完成!' . PHP_EOL;
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
        });
    }
};
