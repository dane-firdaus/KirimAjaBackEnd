<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstAppCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_app_check', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 100)->nullable();
            $table->string('version', 10)->nullable();
            $table->string('message', 255)->nullable();
            $table->boolean('force_update')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_app_check');
    }
}
