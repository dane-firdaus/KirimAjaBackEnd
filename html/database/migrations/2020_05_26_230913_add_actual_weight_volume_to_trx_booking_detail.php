<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualWeightVolumeToTrxBookingDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_booking_detail', function (Blueprint $table) {
            $table->float('package_actual_weight')->nullable();
            $table->float('package_actual_volume')->nullable();
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
            //
        });
    }
}
