<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Drop foreign key
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_host_id_foreign');
            $table->dropForeign('tasks_user_id_foreign');
            $table->dropForeign('tasks_module_id_foreign');
        });

        // run RAW SQL
        DB::statement('ALTER TABLE tasks ENGINE=MEMORY;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //

        DB::statement('ALTER TABLE tasks ENGINE=InnoDB;');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('host_id')->references('id')->on('hosts')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }
};
