<?php

use App\Models\User;
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

        $transaction = new \App\Models\Transaction;

        // 寻找 updates_at 在 3 天内的用户
        User::chunk(100, function ($users) use ($transaction) {

            foreach ($users as $user) {
                // 重新计算余额


                $drops = Cache::get('user_drops_' . $user->id, 0);

                $drops = $drops['drops'] ?? 0;

                $rate = config('billing.drops_rate', 1000);
                if ($drops > 0) {

                    $amount = $drops / $rate;
                    $desc = "转换 {$drops} Drops 为 {$amount} 元。";
                    $transaction->addAmount($user->id, 'console', $amount, $desc, true);


                    echo "恢复 Drops User {$user->id} 的余额。";

                    Cache::forget('user_drops_' . $user->id);
                }


            }
        });

        User::where('updated_at', '>', now()->subDays(3))->chunk(100, function ($users) use ($transaction) {
            foreach ($users as $user) {
                if ($user->balance == -1) {
                    echo "补正余额: User {$user->id} balance {$user->balance}。";
                    $transaction->addAmount($user->id, 'console', 2, '补偿余额。', true);
                }
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
        Schema::table('balance', function (Blueprint $table) {
            //
        });
    }
};
