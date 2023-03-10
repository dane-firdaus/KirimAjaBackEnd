<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddCommodityToTrxBookingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_booking_detail', function (Blueprint $table) {
            $table->integer('package_commodity_id')->unsigned()->nullable();       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_booking_detail', function (Blueprint $table) {
            $table->integer('package_commodity_id')->unsigned()->nullable();
        });
    }
}
