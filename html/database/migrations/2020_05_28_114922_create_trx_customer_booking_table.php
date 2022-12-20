<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrxCustomerBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_customer_booking', function (Blueprint $table) {
            $table->id();
            $table->string('device_token', 255)->nullable();
            $table->string('booking_code', 10)->nullable();
            $table->string('booking_origin_name', 255);
            $table->string('booking_origin_addr_1', 255);
            $table->string('booking_origin_addr_2', 255)->nullable();
            $table->string('booking_origin_addr_3', 255)->nullable();
            $table->string('booking_origin_city', 255);
            $table->string('booking_origin_zip', 8);
            $table->string('booking_origin_contact', 100);
            $table->string('booking_origin_phone', 20);
            $table->string('booking_destination_name', 255);
            $table->string('booking_destination_addr_1', 255);
            $table->string('booking_destination_addr_2', 255)->nullable();
            $table->string('booking_destination_addr_3', 255)->nullable();
            $table->string('booking_destination_city', 255);
            $table->string('booking_destination_zip', 8);
            $table->string('booking_destination_contact', 100);
            $table->string('booking_destination_phone', 20);
            $table->integer('booking_delivery_point_id')->nullable();
            $table->float('booking_fee')->nullable();
            $table->float('booking_estimate_cost')->nullable();
            $table->boolean('valid')->nullable()->default(false);
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
        Schema::dropIfExists('trx_customer_booking');
    }
}
