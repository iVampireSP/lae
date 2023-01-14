<?php

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

        // if transaction collection exists
        if (Schema::connection('mongodb')->hasTable('transactions')) {
            return;
        }

        Schema::connection('mongodb')->create('transactions', function (Blueprint $collection) {
            $collection->unsignedBigInteger('user_id')->index();
            $collection->unsignedBigInteger(
                'type'
            )->index();
            $collection->unsignedBigInteger('payment')->index();
            $collection->unsignedBigInteger(
                'module_id'
            )->index()->nullable();
            $collection->unsignedBigInteger('host_id')->index()->nullable();

            // a year
            $year = 365 * 24 * 60 * 60;
            $collection->expire('created_at', $year);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('transactions');
    }
};
