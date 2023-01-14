<?php

use App\Models\WorkOrder\Reply;
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
        Schema::table('work_order_replies', function (Blueprint $table) {
            // name
            $table->string('name')->nullable()->after('user_id');

            // module_id
            $table->string('module_id')->nullable()->after('name')->index();
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });

        // 为每个工单回复生成一个 module_id 安静更改
        (new App\Models\WorkOrder\Reply)->whereNull('module_id')->with('workOrder')->chunk(100, function ($replies) {
            foreach ($replies as $reply) {
                $reply->module_id = $reply->workOrder->module_id;
                $reply->saveQuietly();
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
        Schema::table('work_order_replies', function (Blueprint $table) {
            Schema::hasColumn('work_order_replies', 'name') && $table->dropColumn('name');

            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
};
