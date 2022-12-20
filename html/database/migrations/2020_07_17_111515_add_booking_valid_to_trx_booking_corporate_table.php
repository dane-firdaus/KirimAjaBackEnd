<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingValidToTrxBookingCorporateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_booking_corporate', function (Blueprint $table) {
            $table->boolean('booking_valid')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_booking_corporate', function (Blueprint $table) {
            $table->boolean('booking_valid')->nullable()->default(false);
        });
    }
}
