<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstAWB extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_awb', function (Blueprint $table) {
            $table->id();
            $table->string('awb', 10);
            $table->boolean('used')->nullable()->default(false);
            $table->boolean('reserved')->nullable()->default(false);
            $table->string('app_uuid', 255)->nullable()->default('');
            $table->integer('user_id')->nullable()->default(0);
            $table->integer('booking_id')->nullable()->default(0);
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
        Schema::dropIfExists('mst_awb');
    }
}
