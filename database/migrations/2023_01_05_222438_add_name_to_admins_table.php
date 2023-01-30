<?php

use App\Models\Admin;
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
        Schema::table('admins', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->name = $admin->id;
            $admin->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
