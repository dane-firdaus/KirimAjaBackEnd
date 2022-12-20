<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxBookingDetailCorporateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_booking_detail_corporate', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id');
            $table->string('package_description', 255);
            $table->float('package_length');
            $table->float('package_width');
            $table->float('package_height');
            $table->integer('package_weight');
            $table->integer('package_volume');
            $table->float('package_actual_weight');
            $table->float('package_actual_volume');
            $table->integer('package_quantity');
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
        Schema::dropIfExists('trx_booking_detail_corporate');
    }
}
