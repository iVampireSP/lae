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
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            //

            $table->tinyInteger('hour_at')->index()->nullable()->after('status');
        });

        echo PHP_EOL . '将开始刷新主机的小时数...';
        (new App\Models\Host)->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $host->hour_at = $host->created_at->hour;
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
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            //

            $table->dropColumn('hour_at');
        });
    }
};
