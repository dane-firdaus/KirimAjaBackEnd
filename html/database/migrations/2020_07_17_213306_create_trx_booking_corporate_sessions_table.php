<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxBookingCorporateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_booking_corporate_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100);
            $table->integer('corporate_id')->unsigned();
            $table->bigInteger('total_cost');
            $table->integer('total_chargeable')->unsigned();
            $table->integer('booking_corporate_id')->unsigned()->nullable();
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
        Schema::dropIfExists('trx_booking_corporate_sessions');
    }
}
