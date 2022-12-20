<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxBookingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_booking_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id');
            $table->string('package_description', 255);
            $table->float('package_length');
            $table->float('package_width');
            $table->float('package_height');
            $table->float('package_weight');
            $table->float('package_volume');
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
        Schema::dropIfExists('trx_booking_detail');
    }
}
