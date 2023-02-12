<?php

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
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->enum('billing_cycle', [
                'monthly',
                'quarterly',
                'semi-annually',
                'annually',
                'biennially',
                'triennially',
            ])->nullable()->index()->after('status');

            $table->dateTime('next_due_at')->nullable()->after('billing_cycle')->index();
        });

        $raw = \Illuminate\Support\Facades\DB::raw("ALTER TABLE `hosts` CHANGE `status` `status` ENUM('draft', 'running','stopped','error','suspended','pending','unavailable','locked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");
        \Illuminate\Support\Facades\DB::statement($raw);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
            $table->dropColumn('next_due_at');
        });

        $raw = \Illuminate\Support\Facades\DB::raw("ALTER TABLE `hosts` CHANGE `status` `status` ENUM('running','stopped','error','suspended','pending','unavailable','locked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");
        \Illuminate\Support\Facades\DB::statement($raw);
    }
};
