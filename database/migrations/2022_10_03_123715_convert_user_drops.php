<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
        //

        $decimal = config('drops.decimal');

        User::chunk(100, function ($users) use ($decimal) {
            foreach ($users as $user) {

                $cache_key = 'user_drops_' . $user->id;

                $drops = Cache::get($cache_key);

                if (!is_array($drops)) {
                    $drops = [
                        'drops' => $drops,
                    ];
                }

                $drops = $drops['drops'] / $decimal;

                $drops = [
                    'drops' => $drops,
                ];

                Cache::forever($cache_key, $drops);
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
        //
    }
};
