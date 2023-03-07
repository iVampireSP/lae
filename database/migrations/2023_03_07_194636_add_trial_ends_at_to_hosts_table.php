<?php

use App\Models\Host;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            // 计费周期（小时，还是月）
            $table->enum('billing_cycle', ['hourly', 'monthly', 'once'])->default('hourly')->index()->after('status');
            $table->tinyInteger('day_at')->nullable()->index()->after('billing_cycle');
            $table->timestamp('trial_ends_at')->nullable()->after('day_at');

            // 不自动续费
            $table->boolean('cancel_at_period_end')->default(false)->after('trial_ends_at');

            // 上次扣费金额
            $table->decimal('last_paid', 10)->nullable()->after('cancel_at_period_end');
            $table->timestamp('last_paid_at')->nullable()->after('last_paid');

            // 到期时间（下次扣费时间）
            $table->timestamp('expired_at')->nullable()->after('last_paid_at');
        });

        $hosts = Host::all();
        $count = $hosts->count();
        // 为已有的主机设置默认值
        Host::all()->each(function (Host $host) use (&$count) {
            echo "Migrating {$host->id} ({$host->name})... {$count} left".PHP_EOL;

            $host->day_at = $host->created_at->day;

            $host->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('trial_ends_at');
            $table->dropColumn('day_at');
            $table->dropColumn('billing_cycle');
            $table->dropColumn('cancel_at_period_end');
            $table->dropColumn('last_paid');
            $table->dropColumn('last_paid_at');
            $table->dropColumn('expired_at');
        });
    }
};
