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
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->unique()->after('id')->nullable();
        });

        $count = User::count();
        $i = 0;

        User::chunk(100, function ($users) use (&$i, $count) {
            foreach ($users as $user) {
                echo sprintf('Updating %d/%d', ++$i, $count) . PHP_EOL;

                $user->email_md5 = md5($user->email);
                $user->uuid = Str::uuid();
                $user->saveQuietly();
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};