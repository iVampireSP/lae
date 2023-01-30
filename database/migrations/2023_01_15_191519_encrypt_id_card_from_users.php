<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if (! $user->id_card) {
                    continue;
                }

                echo "Encrypting user {$user->id}...".PHP_EOL;
                // 设置值（不走模型的 mutator）
                $user->setAttribute('id_card', Crypt::encryptString($user->id_card));

                $user->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        echo PHP_EOL.'无法解密用户数据，因为此操作是不可逆的。'.PHP_EOL;
    }
};
