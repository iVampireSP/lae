<?php

use App\Models\WorkOrder\WorkOrder;
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
        Schema::table('work_orders', function (Blueprint $table) {
            // uuid
            $table->uuid('uuid')->nullable()->after('id')->index()->unique();
        });

        // 为每个工单生成一个 uuid 安静更改
        WorkOrder::query()->chunk(100, function ($workOrders) {
            foreach ($workOrders as $workOrder) {
                $workOrder->uuid = Str::uuid();
                $workOrder->saveQuietly();
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
        return;
    }
};
