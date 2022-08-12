<?php

use App\Models\Admin\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();

            $table->string('password');
            $table->string('api_token')->unique();

            $table->softDeletes();

            $table->timestamps();
        });


        // if env is not production, create admin user
        if (env('APP_ENV') !== 'production') {
            $admin = new Admin();
            $admin->name = 'admin';
            $admin->email = 'admin@admin.test';
            $admin->password = bcrypt('admin');
            $admin->api_token = 'Qwerty123';
            $admin->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
