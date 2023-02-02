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
        Schema::create('module_allows', function (Blueprint $table) {
            $table->id();

            $table->string('module_id')->index();
            $table->string('allowed_module_id')->index();
            $table->timestamps();

            $table->foreign(['allowed_module_id'])->references(['id'])->on('modules')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['module_id'])->references(['id'])->on('modules')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('module_allows');
    }
};
